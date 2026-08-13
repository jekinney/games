<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/GameRegistry.php';
require_once dirname(__DIR__) . '/src/ScoreStore.php';

$debug  = isset($_GET['debug']);
$theme  = ($_GET['theme'] ?? '') === 'crt' ? 'crt' : '';

$registry = GameRegistry::default();
$store    = ScoreStore::default();
$games    = $registry->all();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>All Boards — Retro Arcade</title>
<link rel="stylesheet" href="/assets/site.css">
</head>
<body<?= $debug ? ' data-debug="1"' : '' ?><?= $theme ? ' data-theme="crt"' : '' ?>>

<header id="site-header">
  <a href="/" id="site-title">RETRO ARCADE</a>
  <nav>
    <a href="/scores.php" aria-current="page">scores</a>
    <a href="/about.php">about</a>
  </nav>
</header>

<main id="scores-page">
  <h1>All Boards</h1>

  <?php if (!$games): ?>
    <p class="muted">No games installed yet. Drop a folder into <code>public/games/</code> to get started.</p>
  <?php else: ?>
    <?php foreach ($games as $game):
      $scores  = $store->top($game['id']);
      $filled  = count($scores);
    ?>
    <section class="board-section">
      <h2><a href="/#<?= htmlspecialchars(rawurlencode($game['id'])) ?>"><?= htmlspecialchars($game['title']) ?></a></h2>
      <table class="board">
        <thead>
          <tr><th class="rank">#</th><th class="who">Name</th><th class="score">Score</th><th class="when">Date</th></tr>
        </thead>
        <tbody>
          <?php for ($i = 0; $i < ScoreStore::BOARD_SIZE; $i++): ?>
            <?php if ($i < $filled): $row = $scores[$i]; ?>
            <tr>
              <td class="rank"><?= $row['rank'] ?></td>
              <td><?= htmlspecialchars($row['initials']) ?></td>
              <td class="score"><?= number_format($row['score']) ?></td>
              <td class="when"><?= htmlspecialchars(substr($row['date'], 0, 10)) ?></td>
            </tr>
            <?php else: ?>
            <tr class="empty">
              <td class="rank"><?= $i + 1 ?></td>
              <td>---</td>
              <td class="score">0</td>
              <td class="when">--</td>
            </tr>
            <?php endif; ?>
          <?php endfor; ?>
        </tbody>
      </table>
    </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer id="site-footer">
  <span>Retro Arcade</span> &middot;
  <?php if ($theme): ?>
    <a href="/scores.php">plain mode</a>
  <?php else: ?>
    <a href="/scores.php?theme=crt">CRT mode</a>
  <?php endif; ?>
</footer>

</body>
</html>
