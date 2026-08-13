<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/src/helpers.php';

$debug = isset($_GET['debug']);
$theme = ($_GET['theme'] ?? '') === 'crt' ? 'crt' : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>About — Retro Arcade</title>
<link rel="stylesheet" href="/assets/site.css">
</head>
<body<?= $debug ? ' data-debug="1"' : '' ?><?= $theme ? ' data-theme="crt"' : '' ?>>

<header id="site-header">
  <a href="/" id="site-title">RETRO ARCADE</a>
  <nav>
    <a href="/scores.php">scores</a>
    <a href="/about.php" aria-current="page">about</a>
  </nav>
</header>

<main id="about-page">
  <h1>Retro Arcade</h1>
  <p>
    A simple multi-game arcade. Drop a folder in, and your game appears on the
    list. Scores persist across browsers and machines. No accounts, no ads, no
    cookies — just initials on a board.
  </p>

  <h2>How to add a game</h2>
  <p>
    Create a folder under <code>public/games/&lt;your-game-id&gt;/</code> with
    two files:
  </p>
  <pre>public/games/my-game/
  game.json
  index.html</pre>
  <p>
    The <code>game.json</code> manifest tells the arcade about your game:
  </p>
  <pre>{
  "title":       "My Game",
  "entry":       "index.html",
  "description": "One sentence.",
  "controls":    "Arrow keys to move &middot; SPACE to fire",
  "scoring":     "high",
  "author":      "you",
  "year":        2026
}</pre>
  <p>
    Reload the page and your game appears in the sidebar. See
    <a href="https://github.com/jekinney/games/blob/main/docs/adding-a-game.md">docs/adding-a-game.md</a>
    for the full field reference and the SDK call list.
  </p>

  <h2>The Arcade SDK</h2>
  <p>
    Games talk to the shell through a small script included in the page:
  </p>
  <pre>&lt;script src="/assets/arcade-sdk.js"&gt;&lt;/script&gt;</pre>
  <p>
    Four calls cover everything:
  </p>
  <pre>Arcade.ready();              // hide the loading text
Arcade.setScore(340);        // update the live score display
Arcade.submitScore(1240);    // game over — shell prompts for initials
await Arcade.getHighScores(); // read the current top 10</pre>

  <h2>Source &amp; docs</h2>
  <p>
    <a href="https://github.com/jekinney/games">github.com/jekinney/games</a>
    &mdash; design notes live in <code>docs/plan/</code>.
  </p>
</main>

<footer id="site-footer">
  <span>Retro Arcade</span> &middot;
  <?php if ($theme): ?>
    <a href="/about.php">plain mode</a>
  <?php else: ?>
    <a href="/about.php?theme=crt">CRT mode</a>
  <?php endif; ?>
</footer>

</body>
</html>
