# Adding a Game

Everything the arcade needs to know about your game lives in one folder under
`public/games/`. Drop it in, reload, done.

## The minimum

```
public/games/my-game/
  game.json
  index.html
```

`my-game` becomes the game's permanent ID — lowercase letters, numbers, and
hyphens only, 32 characters max. It appears in the URL as `/#my-game` and in
the score store filename as `my-game.json`.

## game.json — required fields

```json
{
  "title":   "My Game",
  "entry":   "index.html"
}
```

| Field   | Type   | What it does |
| ------- | ------ | ------------ |
| `title` | string | Shown in the sidebar and page heading. Max 40 chars. |
| `entry` | string | Path to the HTML file relative to the game folder. Must exist. |

## game.json — optional fields

```json
{
  "title":       "My Game",
  "entry":       "index.html",
  "description": "One sentence that fits the sidebar.",
  "controls":    "Arrow keys to move · SPACE to fire",
  "scoring":     "high",
  "author":      "you",
  "year":        2026,
  "thumb":       "thumb.png",
  "minScore":    0,
  "maxScore":    999999,
  "tags":        ["arcade", "single-player"]
}
```

| Field         | Default  | What it does |
| ------------- | -------- | ------------ |
| `description` | —        | Shown under the game title. Max 200 chars. |
| `controls`    | —        | Control hint shown below the description. Max 120 chars. |
| `scoring`     | `"high"` | `"high"` = higher is better (Snake, Breakout). `"low"` = lower is better (Maze — time in centiseconds). Affects sort order and tie-breaking. |
| `author`      | —        | Displayed on the home grid if no year is set. Max 60 chars. |
| `year`        | —        | Displayed on the home grid tile. |
| `thumb`       | —        | Path to a thumbnail image relative to the game folder. If present and the file exists, shown as the home grid tile image instead of the letter fallback. Recommended size: 320×200. |
| `minScore`    | —        | Any POST below this value is rejected with HTTP 422. Useful for `"low"` scoring games where a suspiciously fast time should be refused. |
| `maxScore`    | —        | Any POST above this value is rejected with HTTP 422. |
| `tags`        | `[]`     | Arbitrary strings; currently stored but not displayed. |

A malformed `game.json` (invalid JSON, missing required fields, nonexistent
entry file) causes the folder to be **silently skipped** — the rest of the
site is unaffected. Errors appear in the browser console; add `?debug=1` to
the URL to also show them in the sidebar.

## The game page (index.html)

Your game lives in an `<iframe>` sized to match the `#stage` container. The
shell owns everything outside that frame: the header, sidebar, score board,
and initials entry.

A typical game page has no navigation, no site chrome, just a canvas or game
div, and loads the SDK:

```html
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Game</title>
  <!-- your own styles here -->
</head>
<body>
  <!-- game canvas or DOM here -->
  <script src="/assets/arcade-sdk.js"></script>
  <script src="my-game.js"></script>
</body>
</html>
```

## The Arcade SDK

```js
Arcade.ready()               // Tell the shell the game is playable.
                             // Optional but recommended — hides the "loading…"
                             // overlay in the stage.

Arcade.setScore(n)           // Update the live score display in the shell header.
                             // Call this every time the score changes.
                             // n must be a non-negative integer.

Arcade.submitScore(n)        // Game over. The shell prompts for initials and posts
                             // the score to the API.
                             // Returns a Promise that resolves to:
                             //   { accepted: bool, rank: number|null, scores: [...] }
                             // accepted is false if the score didn't make the board.
                             // You don't have to await it; the shell handles everything.

await Arcade.getHighScores() // Fetch the current top 10 for this game.
                             // Returns the same scores array the POST does.
                             // Useful for showing "record to beat" at game start.
```

All calls are no-ops if the game is opened directly (not inside the shell), so
you can develop with a plain browser tab.

## Score submission flow

1. Game calls `Arcade.submitScore(1240)`.
2. The shell checks the minimum play time (2 s). If it hasn't been met, the
   score is silently dropped to prevent console one-liners.
3. The shell shows the initials modal. The player types up to 3 characters and
   hits Enter (or waits 10 s for the auto-submit countdown).
4. The shell POSTs to `/api/scores.php` with `{ game, initials, score }`.
5. The API validates, appends to the board, trims to top 10, and returns
   `{ accepted, rank, scores }`.
6. The shell re-renders the score table and resolves the `submitScore` promise.

## Layout tips

The `#stage` container has `aspect-ratio: 4 / 3` and fills the available width
up to `70vh` tall. Games should be responsive or use a fixed-size canvas with
CSS scaling:

```css
canvas {
  display: block;
  width: 100%;
  height: 100%;
  image-rendering: pixelated; /* keeps pixel art sharp */
}
```

On screens narrower than 700 px the stage switches to `aspect-ratio: 1 / 1`.
Design for 4:3, test at 1:1.

## Scoring conventions

| What you're measuring | `scoring` | Score value |
| --------------------- | --------- | ----------- |
| Points (higher = better) | `"high"` | Points directly (e.g. `1240`) |
| Elapsed time (lower = better) | `"low"` | Centiseconds (e.g. `1240` = 12.40 s) |

For time-attack games, set a `minScore` that rejects physically impossible
times (e.g. `"minScore": 200` rejects anything under 2 seconds).

## What the arcade contract guarantees

- Your folder's `game.json` is the only file the shell reads.
- Your game's ID is its folder name — never something in the manifest.
- The shell renders the initials modal and posts the score. Your game never
  talks to the scores API directly.
- A broken `game.json` skips your game; it can't break the site.
- You can update your game files freely — nothing is cached by the shell.

## What to avoid

- **Navigating away** from `index.html` inside the frame. The shell will lose
  the reference to the frame and won't be able to post messages.
- **Calling `submitScore` more than once** per play. The shell will show the
  modal for the first call and ignore subsequent ones.
- **Reading or writing cookies / localStorage** for score storage. The shell
  owns score persistence — use `Arcade.getHighScores()` if you need to display
  the board inside the frame.
- **Listening to `message` events on `window`** and processing messages from
  `event.origin !== location.origin`. The SDK already filters these.

## Testing your game without the full shell

Open `public/games/my-game/index.html` directly in a browser. SDK calls will
print a `[arcade-sdk] not running inside the arcade shell` warning to the
console and return harmlessly — everything else (rendering, input, game logic)
runs normally.
