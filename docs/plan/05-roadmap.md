# 05 — Build Order

Ordered so the plugin contract is proven early. The riskiest assumption is
"a game folder drops in and just works" — that gets tested in milestone 2, not
at the end.

## M1 — Skeleton that renders

- `public/index.php` — header, sidebar, main panel, no data
- `assets/site.css` — the full look from [04](04-ui-design.md)
- `src/helpers.php` — `json_response()`, input reading
- Herd site pointed at `public/`

**Done when:** `games.test` loads and looks right with hard-coded placeholder
content.

## M2 — The plugin system

- `src/GameRegistry.php` — scan, validate, sort, report problems
- `api/games.php`
- `assets/shell.js` — fetch the list, render the sidebar, hash routing, mount the
  iframe
- One throwaway test game: a page that says "hello" and nothing else

**Done when:** creating `public/games/hello/` with two files makes it appear in
the menu after a refresh, and deleting the folder removes it. Also verify a
deliberately broken `game.json` is skipped without breaking the page.

## M3 — Scores end to end

- `src/ScoreStore.php` — locked read-modify-write, top-10 truncation, tie rules
- `api/scores.php` — GET and POST with the full validation table from [03](03-scores-api.md)
- Score table rendering in the shell, always 10 rows

**Done when:** POSTing a score with curl updates the file and the table shows it.
Concurrency spot-check: fire 20 parallel POSTs, confirm the file is still valid
JSON with exactly 10 entries.

## M4 — The SDK and the initials modal

- `assets/arcade-sdk.js` — `ready`, `setScore`, `submitScore`, `getHighScores`
- `postMessage` handling in the shell, with `event.source` validation
- The initials modal: 3 slots, keyboard capture, 10s auto-submit countdown
- Minimum-play-duration and rate-limit checks

**Done when:** the hello game can end, prompt for initials, and land on the board
without touching any shell code.

## M5 — Real games

Build the three in [06-first-games.md](06-first-games.md). Each one is a test of
the plugin system — if any of them needs a change outside its own folder, the
contract in [02](02-game-plugin-spec.md) has a gap and the contract gets fixed,
not the game.

## M6 — Trim

- `public/scores.php` — all boards on one page ✓
- `public/about.php` ✓
- `scripts/arcade.php` — `remove` and `clear` commands (replaces the separate clear-score / clear-board scripts) ✓
- Thumbnails: `game.json` `thumb` field + CSS fallback letter tile ✓
- `?debug=1` shows skipped-folder problems in the sidebar ✓
- `?theme=crt` — scanline overlay, green-on-black ✓
- `docs/adding-a-game.md` ✓

## Definition of done for v1

- [x] Three playable games, each in its own folder
- [x] Dropping a fourth folder in adds it with zero code changes
- [x] Scores persist across browsers and machines
- [x] Initials entry works by keyboard alone, with the countdown
- [x] A malformed game folder doesn't break the site
- [x] No login, no accounts, no cookies beyond a session-less site
- [ ] Loads in under a second on a cold cache

## Rough effort

| Milestone | Size |
| --- | --- |
| M1 | small |
| M2 | medium — this is the interesting one |
| M3 | medium |
| M4 | medium |
| M5 | large, but it's fun and parallelizable |
| M6 | small |
