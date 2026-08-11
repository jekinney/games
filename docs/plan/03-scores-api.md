# 03 — Scores & API

## Storage

One JSON file per game: `data/scores/<game-id>.json`.

```json
{
  "game": "snake",
  "scoring": "high",
  "updated": "2026-08-11T14:02:11Z",
  "scores": [
    { "initials": "JEK", "score": 9900, "date": "2026-08-11T14:02:11Z" },
    { "initials": "AAA", "score": 8720, "date": "2026-08-10T09:31:00Z" }
  ]
}
```

- **Max 10 entries.** The 11th is dropped on write, not on read.
- **Ties keep the older entry higher** — first to get there holds the spot, which
  is how the cabinets did it.
- Missing file = empty board. No setup step, no seeding required.

### Writing safely

Two people finishing a game at the same moment must not clobber each other:

```php
$fh = fopen($path, 'c+');
flock($fh, LOCK_EX);      // block
// read → insert → sort → truncate to 10 → write
ftruncate($fh, 0); rewind($fh); fwrite($fh, $json); fflush($fh);
flock($fh, LOCK_UN); fclose($fh);
```

Read-modify-write happens entirely inside the lock. That's the whole concurrency
story and it's sufficient for this traffic level.

## HTTP API

### `GET /api/games.php`

```json
{
  "games": [
    { "id": "snake", "title": "Snake", "year": 1976,
      "description": "Eat, grow, don't bite yourself.",
      "controls": "Arrow keys or WASD",
      "entry": "/games/snake/index.html",
      "thumb": "/games/snake/thumb.png", "scoring": "high" }
  ],
  "problems": []
}
```

### `GET /api/scores.php?game=snake`

```json
{
  "game": "snake",
  "scores": [{ "rank": 1, "initials": "JEK", "score": 9900, "date": "2026-08-11" }]
}
```

Unknown game → `404 {"error":"unknown game"}`.

### `POST /api/scores.php`

```json
{ "game": "snake", "score": 1240, "initials": "JEK" }
```

Response:

```json
{
  "accepted": true,
  "rank": 3,
  "scores": [ ...the new top 10... ]
}
```

`accepted: false` with `rank: null` when the score didn't make the board — a
normal outcome, not an error, and still returns the current top 10 so the client
can display it.

### Validation (server-side, non-negotiable)

| Field | Rule | On failure |
| --- | --- | --- |
| `game` | must match `^[a-z0-9][a-z0-9-]{0,31}$` **and** exist in the registry | `400` |
| `score` | integer, `0 <= score <= 999999999` | `400` |
| `initials` | uppercased, non-alphanumerics stripped, truncated to 3; empty → `"AAA"` | never fails, always coerced |

Content type must be `application/json`. Same-origin only — reject requests
whose `Origin` header is present and doesn't match the host.

### Rate limiting

Cheap and good enough: `data/ratelimit/<ip-hash>.json` holding a timestamp list.
Max **10 submissions per minute per IP**, `429` past that. Blunt, and it stops
a script from filling all ten slots in a second.

## Being honest about cheating

Anyone who opens devtools can call `Arcade.submitScore(999999999)`. There is no
fix for this in a client-side game without a server-side simulation, and building
that is wildly out of proportion for an arcade site with no accounts.

What we do instead:

1. **Per-game sanity cap.** Optional `"maxScore"` in `game.json`. Submissions
   above it are rejected outright. Cuts off the lazy 9999999999 case.
2. **Minimum play duration.** The shell records when the iframe loaded; a score
   arriving under ~3 seconds later is rejected. Kills console one-liners.
3. **Rate limiting**, above.
4. **An admin delete.** `scripts/clear-score.php <game> <rank>` from the CLI, plus
   `scripts/clear-board.php <game>`. When someone ruins a board, you wipe it in
   five seconds. This is the real answer.

Documented, deliberate, and appropriate for the stakes. Don't spend more here.

## Swapping the backend later

Everything above sits behind `src/ScoreStore.php` with four methods:

```php
top(string $game, int $limit = 10): array
submit(string $game, string $initials, int $score): array  // returns [rank, scores]
clear(string $game): void
remove(string $game, int $rank): void
```

Moving to SQLite means rewriting that one class. No API change, no client change.
Same for the reverse: if you drop the server and go `localStorage`-only, the
client only needs a different implementation behind `Arcade.getHighScores()` /
the shell's submit call.
