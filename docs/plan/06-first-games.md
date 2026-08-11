# 06 — The First Three Games

Chosen to stress different parts of the plugin contract, not just to be fun.

## 1. Snake — `public/games/snake/`

The simplest possible real game. Grid-based, fixed timestep, no physics, no
assets.

- Canvas, 20x20 grid of 20px cells
- Move on a `setInterval` tick that speeds up as you grow
- Score = food eaten × 10
- `"scoring": "high"`

**Tests:** the basic contract — manifest, iframe mount, one `submitScore` call.
If Snake needs anything the spec doesn't offer, the spec is wrong.

## 2. Breakout — `public/games/breakout/`

- `requestAnimationFrame` loop, float positions, collision
- Mouse *and* keyboard paddle control
- Score = bricks × row value; 3 lives; `Arcade.setScore()` on every brick so the
  shell header updates live

**Tests:** the live-score channel, and pointer input inside an iframe (does the
mouse position math survive being framed? It should — clientX is frame-relative
— but this is exactly the kind of thing that's worth finding early).

## 3. Time Attack Maze — `public/games/maze/`

Generated maze, get from corner to corner, timed to the centisecond.

- `"scoring": "low"` — 1240 (12.40s) beats 3000 (30.00s)
- Score submitted as centiseconds
- `"maxScore"` set, and a floor: anything under a plausible minimum is rejected

**Tests:** the `scoring: "low"` sort path end to end — score table, tie handling,
and the "did it place?" logic all have to respect it. Easy to get wrong, so it
gets a real game rather than a unit test.

## Deliberately not in v1

- **Tetris** — the most requested, and the most fiddly (wall kicks, lock delay,
  7-bag). Build it once the platform is boring.
- **Asteroids** — vector rendering and physics; a good fourth.
- **Pong** — needs an opponent to be interesting, and an AI opponent means
  tuning difficulty. Low value per hour.

## The rule for every game

If a game requires editing anything outside its own folder, stop and fix
[02-game-plugin-spec.md](02-game-plugin-spec.md) plus the SDK instead. The
platform exists to make that unnecessary; a game that breaks the rule is
reporting a bug in the platform.
