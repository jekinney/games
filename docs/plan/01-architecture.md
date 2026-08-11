# 01 — Architecture

## Stack

- **Server:** PHP 8.3 (already on PATH via Herd), no framework, no Composer
  dependencies. Served at `http://games.test`.
- **Client:** hand-written HTML/CSS/JS. No bundler, no npm, no TypeScript.
- **Storage:** JSON files under `data/`. Never served directly to the browser.

Nothing here requires Herd specifically — `php -S localhost:8000 -t public` runs
the same site.

## Folder layout

```
games/
├─ public/                  <- document root (point Herd here)
│  ├─ index.php             <- the shell: sidebar + iframe
│  ├─ api/
│  │  ├─ games.php          <- GET  list of installed games
│  │  └─ scores.php         <- GET/POST high scores
│  ├─ assets/
│  │  ├─ site.css           <- the whole site's styling
│  │  ├─ shell.js           <- sidebar, routing, iframe host
│  │  └─ arcade-sdk.js      <- what games import (see 02)
│  └─ games/                <- ***drop game folders in here***
│     ├─ snake/
│     ├─ breakout/
│     └─ ...
├─ src/
│  ├─ GameRegistry.php      <- scans public/games, validates manifests
│  ├─ ScoreStore.php        <- read/write top-10 JSON, atomic
│  └─ helpers.php           <- json_response(), sanitize helpers
├─ data/
│  ├─ scores/<game-id>.json <- one file per game
│  └─ .htaccess             <- deny from all (belt and braces; it's outside webroot anyway)
└─ docs/plan/               <- these documents
```

**Why `public/` as the document root:** it keeps `data/` and `src/` physically
unreachable over HTTP. Set the Herd site's document root to `public`. If that's
inconvenient, the fallback is to keep everything at the root and rely on the
`data/.htaccess` deny rule — less safe, but workable.

## Request flow

**Loading the site**

1. Browser hits `/`. `index.php` renders the shell — header, empty sidebar,
   empty main panel.
2. `shell.js` calls `GET /api/games.php`.
3. `GameRegistry` scans `public/games/*/game.json`, validates each, sorts by
   title, returns JSON.
4. `shell.js` renders the sidebar list. Anything invalid is skipped and reported
   in a `problems` array the shell prints to the console (not to the page).

**Playing a game**

1. Click a game → URL becomes `/#snake` (hash routing; no server route needed).
2. Shell puts `<iframe src="/games/snake/index.html">` in the main panel and
   renders that game's high-score table beside it.
3. The game loads `/assets/arcade-sdk.js`, plays, and on game over calls
   `Arcade.submitScore(1234)`.
4. The SDK `postMessage`s the score to the shell. **The shell owns the initials
   prompt** — the game never draws it. Consistent everywhere, and games can't
   skip it.
5. Shell POSTs `{game, score, initials}` to `/api/scores.php`, gets the updated
   top 10 back, re-renders the table.

## Why an iframe

- One game's `window.onkeydown`, global variables, or infinite loop can't break
  the shell or another game.
- Games are written as if they own the page — simpler for whoever writes them.
- Games can't read each other's or the shell's DOM.
- The cost is one `postMessage` hop, which the SDK hides completely.

The shell validates every incoming message: it checks `event.source` matches the
current iframe's `contentWindow` and ignores anything else.

## What "drop in a folder" actually means

`GameRegistry::all()` does exactly this:

1. `glob('public/games/*/game.json')`
2. For each: decode, validate required keys, confirm the `entry` file exists.
3. Derive `id` from the **folder name** (not the manifest) so ids can't collide
   or be spoofed. Folder name must match `^[a-z0-9][a-z0-9-]{0,31}$`.
4. Skip and log anything that fails.

Result cached in-process only. Add a folder → refresh → it's in the menu.

## Security posture

This is a hobby site with no accounts, but there are still cheap wins worth taking:

- **Path traversal:** the game id from any request is matched against
  `^[a-z0-9][a-z0-9-]{0,31}$` *and* checked against the registry list before it
  ever touches a filesystem path. No user string is concatenated into a path.
- **Stored XSS:** initials are the only user input that's persisted. They're
  forced to `[A-Z0-9]{1,3}` server-side, and the client renders them with
  `textContent`. Both, not either.
- **Data files unreachable:** `data/` sits outside the document root.
- **No secrets anywhere:** nothing to leak.
