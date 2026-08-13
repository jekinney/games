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
<title>Retro Arcade</title>
<link rel="stylesheet" href="/assets/site.css">
</head>
<body<?= $debug ? ' data-debug="1"' : '' ?><?= $theme ? ' data-theme="crt"' : '' ?>>

<header id="site-header">
  <a href="/" id="site-title">RETRO ARCADE</a>
  <nav>
    <a href="/scores.php">scores</a>
    <a href="/about.php">about</a>
  </nav>
</header>

<div id="layout">

  <aside id="sidebar">
    <h2>Games</h2>
    <ul id="game-list"><li class="muted">loading&hellip;</li></ul>
    <p id="game-count" class="muted"></p>
    <div id="problems" hidden>
      <h3>Skipped folders</h3>
      <ul id="problem-list"></ul>
    </div>
    <p class="muted sidebar-note">
      Drop a folder into <code>public/games/</code> to add a game.
      See <code>docs/plan</code>.
    </p>
  </aside>

  <main id="main">

    <section id="home">
      <h1>Pick a game</h1>
      <p class="muted">No accounts. No ads. Just get your initials on the board.</p>
      <ul id="game-grid"></ul>
    </section>

    <section id="player" hidden>
      <h1 id="game-title"></h1>
      <p id="game-description" class="muted"></p>
      <p id="game-controls" class="controls"></p>

      <p id="live-score" hidden>SCORE <span id="live-score-value">0</span></p>

      <div id="stage">
        <div id="stage-status">loading&hellip;</div>
        <!-- iframe is inserted here by shell.js -->
      </div>

      <p id="submit-note" class="muted" hidden></p>

      <h2 id="board-title">Top 10</h2>
      <table id="board">
        <thead>
          <tr><th class="rank">#</th><th class="who">Name</th><th class="score">Score</th><th class="when">Date</th></tr>
        </thead>
        <tbody id="board-body"></tbody>
      </table>
    </section>

    <section id="not-found" hidden>
      <h1>No such game</h1>
      <p>That game isn't installed. <a href="/">Back to the list</a>.</p>
    </section>

  </main>
</div>

<!-- Arcade initials entry. The shell owns this; games never draw their own. -->
<div id="initials-modal" hidden>
  <div id="initials-box" role="dialog" aria-modal="true" aria-labelledby="initials-heading" tabindex="-1">
    <h2 id="initials-heading">New High Score!</h2>
    <p id="initials-rank"></p>
    <div id="initials-slots" aria-hidden="true">
      <span class="slot" data-slot="0"></span>
      <span class="slot" data-slot="1"></span>
      <span class="slot" data-slot="2"></span>
    </div>
    <label for="initials-input" class="visually-hidden">Your initials, three characters</label>
    <input id="initials-input" type="text" maxlength="3" autocomplete="off"
           autocapitalize="characters" spellcheck="false" inputmode="latin">
    <p class="initials-hint">type 3 letters &middot; <b>ENTER</b></p>
    <p id="initials-countdown"></p>
  </div>
</div>

<footer id="site-footer">
  <span>Retro Arcade</span> &middot;
  <?php if ($theme): ?>
    <a href="/">plain mode</a>
  <?php else: ?>
    <a href="/?theme=crt">CRT mode</a>
  <?php endif; ?>
</footer>

<script src="/assets/shell.js"></script>
</body>
</html>
