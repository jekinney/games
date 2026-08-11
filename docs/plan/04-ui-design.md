# 04 — UI & Look

Deliberately plain. Craigslist, early Yahoo, a BBS door menu. System fonts, blue
underlined links, one accent color, a table with borders. No framework, no
animation, no shadows. It should load instantly and look like it was made in 2003
on purpose.

## Layout

```
┌──────────────────────────────────────────────────────────────┐
│  RETRO ARCADE                              [ scores ] [ about ] │
├────────────────┬─────────────────────────────────────────────┤
│ GAMES          │  Snake (1976)                               │
│                │  Eat, grow, don't bite yourself.            │
│ » Snake        │  Controls: Arrow keys or WASD               │
│   Breakout     │  ┌───────────────────────────────────────┐  │
│   Pong         │  │                                       │  │
│   Tetris       │  │           [ game iframe ]             │  │
│   Asteroids    │  │                                       │  │
│                │  └───────────────────────────────────────┘  │
│ ──────────     │                                             │
│ 5 games        │  TOP 10 — SNAKE                             │
│ add one? see   │  ┌────┬──────┬────────┬────────────┐        │
│ docs/plan      │  │  1 │ JEK  │  9,900 │ 2026-08-11 │        │
│                │  │  2 │ AAA  │  8,720 │ 2026-08-10 │        │
│                │  │  3 │ ---  │      0 │     --     │        │
│                │  └────┴──────┴────────┴────────────┘        │
└────────────────┴─────────────────────────────────────────────┘
```

- Sidebar: fixed ~200px, plain `<ul>` of links, current game bolded with a `»`.
- Main: title, description, controls line, the iframe, then the score table.
- Empty score slots render as `---` / `0`, exactly like a cabinet that nobody has
  played yet. Always ten rows.
- Under 700px wide the sidebar collapses to a `<details>` block above the game.
  That's the entire responsive story.

## Pages

| Route | What it is |
| --- | --- |
| `/` | Game grid — thumbnail tiles, or a plain list if no thumbs |
| `/#<game-id>` | Play a game + its top 10 |
| `/scores.php` | All boards on one page, one table per game |
| `/about.php` | What this is, how to add a game, link to `docs/plan` |

Hash routing for games means zero server routing and a shareable link per game.

## The initials entry

The one place we spend design effort — it's the whole point of the site.

```
        ┌─────────────────────────────┐
        │   NEW HIGH SCORE!  RANK 3   │
        │                             │
        │         J  E  _             │
        │         ▔  ▔  ▔             │
        │                             │
        │  type 3 letters · ENTER     │
        └─────────────────────────────┘
```

- Modal in the **shell**, over the iframe. Never drawn by the game.
- Three character slots. Typing a letter advances; Backspace goes back.
- Accepts `A-Z` and `0-9` only. Auto-uppercase.
- ENTER submits. Submitting with fewer than 3 typed pads with `A`.
- A **10-second countdown** that auto-submits, like the real thing. This is
  flavor, and it's the flavor people remember.
- Only shown when the score actually places top 10 — the shell asks the API
  first with a dry-run check, or optimistically prompts and accepts the
  `accepted:false` answer. **Decision: prompt optimistically**, because a
  round-trip before the prompt adds a visible stall at the most dramatic moment.
  If the score didn't place, show "not quite — rank 14" after.

## Styling rules

- One stylesheet: `assets/site.css`. Under 200 lines.
- System font stack. `Courier New` for scores and initials only.
- Palette: white background, `#00e` links, `#333` text, `#c00` accent for "NEW
  HIGH SCORE", `#eee` table stripes. That's it.
- No web fonts, no icon library, no images in the chrome.
- Optional `?theme=crt` adds a scanline overlay and green-on-black for people who
  want it. Off by default — plain is the default.
