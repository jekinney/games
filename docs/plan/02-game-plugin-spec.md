# 02 — Game Plugin Spec

This is the contract. If a folder satisfies it, the site picks it up. Nothing
else in the codebase needs to change, ever.

## Minimum viable game

```
public/games/snake/
├─ game.json        <- required: the manifest
├─ index.html       <- required: the entry point
├─ snake.js         <- whatever else you want
├─ style.css
└─ thumb.png        <- optional, 320x200, shown in the game list
```

Two required files. Everything else is up to the game.

## `game.json`

```json
{
  "title": "Snake",
  "entry": "index.html",
  "description": "Eat, grow, don't bite yourself.",
  "author": "jek",
  "year": 1976,
  "controls": "Arrow keys or WASD",
  "scoring": "high",
  "thumb": "thumb.png",
  "tags": ["classic", "single-player"]
}
```

| Field | Required | Notes |
| --- | --- | --- |
| `title` | **yes** | Shown in the sidebar. Max 40 chars. |
| `entry` | **yes** | Relative path inside the folder. Must exist. Must not contain `..`. |
| `description` | no | One line, shown under the game. Max 200 chars. |
| `author` | no | Free text. |
| `year` | no | Displayed as flavor, e.g. "Snake (1976)". |
| `controls` | no | Printed next to the game so players don't have to guess. |
| `scoring` | no | `"high"` (default) or `"low"` — low is for time-attack games where 12.4s beats 30s. Drives the sort order of the top 10. |
| `thumb` | no | Relative image path. Falls back to a generated letter tile. |
| `tags` | no | Array of strings. Used for grouping later; ignored in v1. |

**The game's `id` is its folder name**, never a manifest field. `snake/` → `snake`.
Rename the folder and you get a new game with a fresh score table — that's
intended and documented behavior, not a bug.

## The JS API (`arcade-sdk.js`)

A game includes one script and gets four functions. That's the whole surface.

```html
<script src="/assets/arcade-sdk.js"></script>
```

```js
// Tell the shell you're alive and ready. Optional but polite —
// it lets the shell hide its loading state.
Arcade.ready();

// Game over. This is the important one.
// The SHELL prompts for initials if the score makes the top 10.
Arcade.submitScore(1240);

// Optional: live score in the shell's header while playing.
Arcade.setScore(340);

// Optional: read the current top 10 (e.g. to show a target to beat
// on your own title screen). Resolves to an array.
const top = await Arcade.getHighScores();
// [{ rank: 1, initials: "JEK", score: 9900, date: "2026-08-11" }, ...]
```

### Rules for game authors

1. **Call `submitScore` exactly once per play.** The shell rate-limits and the
   server rejects bursts.
2. **Don't draw your own initials prompt.** The shell does it, identically for
   every game.
3. **Score must be a non-negative integer.** For time-attack games, submit
   milliseconds or centiseconds and set `"scoring": "low"`.
4. **Use relative paths** for your own assets. `/games/snake/` is your home.
5. **Fill the frame.** Your page gets a variable-size iframe. Use
   `width: 100%; height: 100%` on the body and size the canvas from JS, or pick
   a fixed canvas size and center it. Don't assume 800x600.
6. **Handle your own keyboard, but only when focused.** The shell forwards focus
   to the iframe on click. Call `preventDefault()` on arrow keys so the page
   doesn't scroll.
7. **Pause when hidden.** Listen for `visibilitychange` — nobody wants a game
   burning CPU in a background tab.

### How the SDK works (implementation note)

`Arcade.submitScore(n)` → `parent.postMessage({source:'arcade-game', type:'score', value:n}, location.origin)`.
The shell validates the sender, prompts for initials, POSTs to the API, and
posts back `{type:'score-ack', rank}` so the game can show "You placed 3rd!" if
it wants to. `getHighScores()` is the same round trip with a promise keyed by a
request id.

A game that never loads the SDK still works — it just can't record scores. Good
for toys and demos.

## Adding a game — the actual checklist

1. `mkdir public/games/my-game`
2. Write `game.json` and `index.html`.
3. Add `<script src="/assets/arcade-sdk.js"></script>` and call
   `Arcade.submitScore()` on game over.
4. Refresh `games.test`. Done.

There is no step 5. No registration file, no build, no restart.

## Validation and failure behavior

A malformed game must never break the site. On any of these, the game is skipped
and a line is added to the `problems` array in the `/api/games.php` response:

- `game.json` missing or not valid JSON
- `title` or `entry` missing/empty
- `entry` file doesn't exist, or the path contains `..`
- folder name doesn't match `^[a-z0-9][a-z0-9-]{0,31}$`

A `?debug=1` on the shell URL renders `problems` visibly in the sidebar so you
can see why your new game didn't show up.
