# Retro Arcade — Project Plan

A plain, no-frills website that hosts small HTML/JS games and keeps an old-school
**top 10 high score table with 3-letter initials** for each game.

New games are added by **dropping a folder into `/games/`**. No code changes, no
rebuild, no registry to edit. The site scans the folder and the game shows up in
the left-hand menu.

## Plan documents

| Doc | What's in it |
| --- | --- |
| [01-architecture.md](01-architecture.md) | Stack, folder layout, how a page request flows |
| [02-game-plugin-spec.md](02-game-plugin-spec.md) | The contract a game folder must satisfy (`game.json`, the JS API) |
| [03-scores-api.md](03-scores-api.md) | Score storage, the HTTP API, anti-garbage rules |
| [04-ui-design.md](04-ui-design.md) | Layout, look, page list |
| [05-roadmap.md](05-roadmap.md) | Build order, milestones, definition of done |
| [06-first-games.md](06-first-games.md) | The three launch games and why those three |

## Goals

1. **Drop-in games.** A game is a self-contained folder. Deleting the folder
   removes the game. Nothing else knows it existed.
2. **Real high scores.** Scores are shared across everyone who visits, not stuck
   in one browser. Enter 3 initials, arcade style, when you make the board.
3. **No accounts.** No login, no email, no cookies-for-tracking. Initials only.
4. **Boring on purpose.** Craigslist/early-web plain: system fonts, blue links,
   a table, a sidebar. Fast, readable, no framework.

## Key decisions (and the assumptions behind them)

| Decision | Why |
| --- | --- |
| **PHP 8.3 backend, no framework** | The project already lives under Laravel Herd (`c:\Users\jekin\Herd\games` → `games.test`), and PHP 8.3 is on PATH. That gives a real shared leaderboard for ~150 lines of code and zero install. |
| **Scores in JSON files, one per game** | Top-10 lists are tiny. A JSON file per game with an atomic write is simpler than SQLite and trivially inspectable/editable. Swappable later — the API contract does not change. |
| **Vanilla JS, no build step** | Edit a file, refresh the browser. A build step would fight the drop-in-a-folder goal. |
| **Games run in an iframe** | A broken or greedy game can't take down the shell, and every game gets a clean global scope. The shell talks to it over `postMessage`. |

> **Assumption worth confirming:** scores are *shared server-side*. If you'd
> rather each visitor only sees their own scores, the PHP side disappears and
> `localStorage` replaces it — the client-side API in
> [03-scores-api.md](03-scores-api.md) is designed so that swap touches one file.

## Non-goals (for v1)

- Multiplayer, real-time, or anything networked inside a game
- Mobile-first design (it should *work* on a phone; it isn't the target)
- Cheat-proof scores — see the "honest about cheating" section in
  [03-scores-api.md](03-scores-api.md)
- Comments, profiles, likes, or any social feature
