<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/src/helpers.php';

$debug = isset($_GET['debug']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Retro Arcade</title>
<link rel="stylesheet" href="/assets/site.css">
</head>
<body<?= $debug ? ' data-debug="1"' : '' ?>>

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

      <div id="stage">
        <div id="stage-status">loading&hellip;</div>
        <!-- iframe is inserted here by shell.js -->
      </div>

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

<footer id="site-footer">
  <span>Retro Arcade</span> &middot;
  <span id="footer-note">built to be boring</span>
</footer>

<script src="/assets/shell.js"></script>
</body>
</html>
