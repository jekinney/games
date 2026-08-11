<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Discovers games by scanning public/games/<id>/game.json.
 *
 * A game's id is its FOLDER NAME, never a manifest field — ids can't collide
 * and can't be spoofed by a manifest. Anything malformed is skipped and
 * recorded in problems() so a bad folder can never break the site.
 */
final class GameRegistry
{
    private const MAX_TITLE = 40;
    private const MAX_DESCRIPTION = 200;
    private const MAX_CONTROLS = 120;

    /** @var array<string,array>|null */
    private ?array $games = null;

    /** @var list<string> */
    private array $problems = [];

    public function __construct(private string $gamesDir)
    {
    }

    public static function default(): self
    {
        return new self(base_path('public/games'));
    }

    /** @return list<array> games sorted by title */
    public function all(): array
    {
        return array_values($this->load());
    }

    public function find(string $id): ?array
    {
        if (!is_valid_game_id($id)) {
            return null;
        }
        return $this->load()[$id] ?? null;
    }

    public function exists(string $id): bool
    {
        return $this->find($id) !== null;
    }

    /** @return list<string> human-readable reasons folders were skipped */
    public function problems(): array
    {
        $this->load();
        return $this->problems;
    }

    /** @return array<string,array> */
    private function load(): array
    {
        if ($this->games !== null) {
            return $this->games;
        }

        $this->games = [];
        $this->problems = [];

        if (!is_dir($this->gamesDir)) {
            $this->problems[] = 'games directory is missing: ' . $this->gamesDir;
            return $this->games;
        }

        foreach (glob($this->gamesDir . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $id = basename($dir);

            if (!is_valid_game_id($id)) {
                $this->problems[] = "$id: folder name must be lowercase letters, numbers and dashes (max 32)";
                continue;
            }

            $game = $this->readManifest($dir, $id);
            if ($game !== null) {
                $this->games[$id] = $game;
            }
        }

        uasort($this->games, static fn(array $a, array $b) => strcasecmp($a['title'], $b['title']));

        return $this->games;
    }

    private function readManifest(string $dir, string $id): ?array
    {
        $manifestPath = $dir . '/game.json';
        if (!is_file($manifestPath)) {
            $this->problems[] = "$id: no game.json";
            return null;
        }

        $raw = file_get_contents($manifestPath);
        if ($raw !== false) {
            // Windows editors (Notepad, VS Code, PowerShell's Out-File) happily
            // write a UTF-8 BOM, which json_decode rejects. Strip it.
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        }
        $manifest = $raw === false ? null : json_decode($raw, true);
        if (!is_array($manifest)) {
            $this->problems[] = "$id: game.json is not valid JSON";
            return null;
        }

        $title = $this->str($manifest['title'] ?? '', self::MAX_TITLE);
        if ($title === '') {
            $this->problems[] = "$id: game.json needs a \"title\"";
            return null;
        }

        $entry = $this->str($manifest['entry'] ?? '', 200);
        if ($entry === '') {
            $this->problems[] = "$id: game.json needs an \"entry\"";
            return null;
        }
        if (!$this->isSafeRelativePath($entry) || !is_file($dir . '/' . $entry)) {
            $this->problems[] = "$id: entry \"$entry\" is missing or outside the game folder";
            return null;
        }

        $thumb = $this->str($manifest['thumb'] ?? '', 200);
        if ($thumb !== '' && (!$this->isSafeRelativePath($thumb) || !is_file($dir . '/' . $thumb))) {
            $thumb = '';
        }

        $scoring = ($manifest['scoring'] ?? 'high') === 'low' ? 'low' : 'high';

        $maxScore = null;
        if (isset($manifest['maxScore']) && is_numeric($manifest['maxScore'])) {
            $maxScore = max(0, (int) $manifest['maxScore']);
        }
        $minScore = null;
        if (isset($manifest['minScore']) && is_numeric($manifest['minScore'])) {
            $minScore = max(0, (int) $manifest['minScore']);
        }

        $tags = [];
        if (isset($manifest['tags']) && is_array($manifest['tags'])) {
            foreach ($manifest['tags'] as $tag) {
                if (is_string($tag) && $tag !== '') {
                    $tags[] = $this->str($tag, 24);
                }
            }
        }

        return [
            'id'          => $id,
            'title'       => $title,
            'description' => $this->str($manifest['description'] ?? '', self::MAX_DESCRIPTION),
            'author'      => $this->str($manifest['author'] ?? '', 60),
            'year'        => isset($manifest['year']) && is_numeric($manifest['year'])
                                ? (int) $manifest['year'] : null,
            'controls'    => $this->str($manifest['controls'] ?? '', self::MAX_CONTROLS),
            'scoring'     => $scoring,
            'maxScore'    => $maxScore,
            'minScore'    => $minScore,
            'tags'        => $tags,
            'entry'       => "/games/$id/" . ltrim($entry, '/'),
            'thumb'       => $thumb === '' ? null : "/games/$id/" . ltrim($thumb, '/'),
        ];
    }

    /** Trim, collapse whitespace, cap length. Never returns null. */
    private function str(mixed $value, int $max): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }
        $clean = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return mb_substr($clean, 0, $max);
    }

    /** Relative, no traversal, no absolute paths, no drive letters. */
    private function isSafeRelativePath(string $path): bool
    {
        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return false;
        }
        if (str_starts_with($path, '/') || str_starts_with($path, '\\') || preg_match('/^[a-zA-Z]:/', $path)) {
            return false;
        }
        return true;
    }
}
