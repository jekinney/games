<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Top-10 boards, one JSON file per game, under data/scores/.
 *
 * Every read-modify-write happens inside an exclusive flock so two players
 * finishing at the same moment can't clobber each other. Swap this class for
 * SQLite later and nothing else has to change.
 */
final class ScoreStore
{
    public const BOARD_SIZE = 10;
    public const MAX_SCORE = 999999999;

    public function __construct(private string $dir)
    {
    }

    public static function default(): self
    {
        return new self(base_path('data/scores'));
    }

    /** @return list<array> ranked entries, at most $limit */
    public function top(string $game, int $limit = self::BOARD_SIZE): array
    {
        $board = $this->readBoard($this->path($game), $game);
        return $this->rank(array_slice($board['scores'], 0, $limit));
    }

    /**
     * Insert a score. Returns [rank|null, list<array> scores].
     * rank is null when the score didn't make the board.
     *
     * @param 'high'|'low' $scoring
     */
    public function submit(string $game, string $initials, int $score, string $scoring = 'high'): array
    {
        $path = $this->path($game);
        $this->ensureDir();

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new RuntimeException("cannot open score file for $game");
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException("cannot lock score file for $game");
            }

            $board = $this->readHandle($handle, $game);
            $now = gmdate('c');

            $entry = [
                'initials' => normalize_initials($initials),
                'score'    => $score,
                'date'     => $now,
                '_new'     => true,
            ];

            $scores = $board['scores'];
            $scores[] = $entry;
            $scores = $this->sort($scores, $scoring);

            // Find where the new entry landed BEFORE truncating.
            $index = null;
            foreach ($scores as $i => $row) {
                if (!empty($row['_new'])) {
                    $index = $i;
                    break;
                }
            }

            $accepted = $index !== null && $index < self::BOARD_SIZE;
            $scores = array_slice($scores, 0, self::BOARD_SIZE);
            $scores = array_map(static function (array $row): array {
                unset($row['_new']);
                return $row;
            }, $scores);

            $board = [
                'game'    => $game,
                'scoring' => $scoring,
                'updated' => $now,
                'scores'  => $scores,
            ];

            $this->writeHandle($handle, $board);

            return [$accepted ? $index + 1 : null, $this->rank($scores)];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** Would this score place, without writing anything? */
    public function wouldPlace(string $game, int $score, string $scoring = 'high'): bool
    {
        $scores = $this->readBoard($this->path($game), $game)['scores'];
        if (count($scores) < self::BOARD_SIZE) {
            return true;
        }
        $last = $scores[self::BOARD_SIZE - 1]['score'];
        return $scoring === 'low' ? $score < $last : $score > $last;
    }

    public function clear(string $game): void
    {
        $path = $this->path($game);
        if (is_file($path)) {
            unlink($path);
        }
    }

    /** Remove one entry by its 1-based rank. */
    public function remove(string $game, int $rank): bool
    {
        $path = $this->path($game);
        if (!is_file($path)) {
            return false;
        }

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return false;
        }

        try {
            flock($handle, LOCK_EX);
            $board = $this->readHandle($handle, $game);

            $index = $rank - 1;
            if ($index < 0 || !isset($board['scores'][$index])) {
                return false;
            }

            array_splice($board['scores'], $index, 1);
            $board['updated'] = gmdate('c');
            $this->writeHandle($handle, $board);

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /* ---------------- internals ---------------- */

    private function path(string $game): string
    {
        // Callers must validate the id first; this is the second line of defence.
        if (!is_valid_game_id($game)) {
            throw new InvalidArgumentException("bad game id: $game");
        }
        return $this->dir . '/' . $game . '.json';
    }

    private function ensureDir(): void
    {
        if (!is_dir($this->dir) && !mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            throw new RuntimeException("cannot create {$this->dir}");
        }
    }

    private function readBoard(string $path, string $game): array
    {
        if (!is_file($path)) {
            return $this->emptyBoard($game);
        }
        $raw = file_get_contents($path);
        return $this->decode($raw === false ? '' : $raw, $game);
    }

    private function readHandle($handle, string $game): array
    {
        rewind($handle);
        $raw = stream_get_contents($handle);
        return $this->decode($raw === false ? '' : $raw, $game);
    }

    /** A corrupt or empty file is treated as an empty board, never a fatal error. */
    private function decode(string $raw, string $game): array
    {
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', trim($raw)) ?? '';
        if ($raw === '') {
            return $this->emptyBoard($game);
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['scores']) || !is_array($data['scores'])) {
            return $this->emptyBoard($game);
        }

        $clean = [];
        foreach ($data['scores'] as $row) {
            if (!is_array($row) || !isset($row['score'])) {
                continue;
            }
            $clean[] = [
                'initials' => normalize_initials((string) ($row['initials'] ?? '')),
                'score'    => max(0, min(self::MAX_SCORE, (int) $row['score'])),
                'date'     => is_string($row['date'] ?? null) ? $row['date'] : gmdate('c'),
            ];
        }

        return [
            'game'    => $game,
            'scoring' => ($data['scoring'] ?? 'high') === 'low' ? 'low' : 'high',
            'updated' => is_string($data['updated'] ?? null) ? $data['updated'] : gmdate('c'),
            'scores'  => $clean,
        ];
    }

    private function emptyBoard(string $game): array
    {
        return ['game' => $game, 'scoring' => 'high', 'updated' => gmdate('c'), 'scores' => []];
    }

    /** Better score first; on a tie the older entry keeps the higher spot. */
    private function sort(array $scores, string $scoring): array
    {
        usort($scores, static function (array $a, array $b) use ($scoring): int {
            $cmp = $scoring === 'low'
                ? $a['score'] <=> $b['score']
                : $b['score'] <=> $a['score'];
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcmp((string) $a['date'], (string) $b['date']);
        });

        return $scores;
    }

    private function rank(array $scores): array
    {
        $out = [];
        foreach ($scores as $i => $row) {
            $out[] = [
                'rank'     => $i + 1,
                'initials' => $row['initials'],
                'score'    => $row['score'],
                'date'     => $row['date'],
            ];
        }
        return $out;
    }

    private function writeHandle($handle, array $board): void
    {
        $json = json_encode($board, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $json);
        fflush($handle);
    }
}
