<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Blunt per-IP rate limit backed by one small file per caller.
 * Enough to stop a script filling all ten slots in a second.
 */
final class RateLimiter
{
    public function __construct(
        private string $dir,
        private int $maxHits = 10,
        private int $windowSeconds = 60,
    ) {
    }

    public static function default(): self
    {
        // ARCADE_RATE_LIMIT tunes submissions-per-minute-per-IP; tests set it high.
        $max = (int) (getenv('ARCADE_RATE_LIMIT') ?: 10);
        return new self(base_path('data/ratelimit'), $max > 0 ? $max : 10);
    }

    /** True if the caller is allowed to proceed (and the hit is recorded). */
    public function allow(string $key): bool
    {
        if (!is_dir($this->dir) && !mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            return true; // never lock players out because of a disk problem
        }

        $path = $this->dir . '/' . hash('sha256', $key) . '.json';
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return true;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return true;
            }

            rewind($handle);
            $raw = stream_get_contents($handle);
            $hits = json_decode($raw === false ? '' : $raw, true);
            if (!is_array($hits)) {
                $hits = [];
            }

            $now = time();
            $cutoff = $now - $this->windowSeconds;
            $hits = array_values(array_filter(
                $hits,
                static fn($t) => is_numeric($t) && (int) $t > $cutoff
            ));

            if (count($hits) >= $this->maxHits) {
                return false;
            }

            $hits[] = $now;
            $json = json_encode($hits);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json);
            fflush($handle);

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** Best-effort caller identity. No proxy headers trusted by default. */
    public static function callerKey(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /** Delete rate-limit files older than an hour. Cheap housekeeping. */
    public function sweep(): void
    {
        foreach (glob($this->dir . '/*.json') ?: [] as $file) {
            if (filemtime($file) < time() - 3600) {
                @unlink($file);
            }
        }
    }
}
