<?php
declare(strict_types=1);

/**
 * Arcade admin CLI. Run from anywhere:
 *
 *   php scripts/arcade.php list
 *   php scripts/arcade.php board snake
 *   php scripts/arcade.php remove snake 3
 *   php scripts/arcade.php clear snake
 *
 * This is the real answer to a ruined board: wipe it in five seconds.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
require_once $root . '/src/GameRegistry.php';
require_once $root . '/src/ScoreStore.php';

$registry = GameRegistry::default();
$store = ScoreStore::default();

$command = $argv[1] ?? 'help';
$gameId = $argv[2] ?? '';

function fail(string $message): never
{
    fwrite(STDERR, "error: $message\n");
    exit(1);
}

function requireGame(GameRegistry $registry, string $id): array
{
    if ($id === '') {
        fail('which game? try: php scripts/arcade.php list');
    }
    $game = $registry->find($id);
    if ($game === null) {
        fail("no such game: $id");
    }
    return $game;
}

function printBoard(ScoreStore $store, array $game): void
{
    $scores = $store->top($game['id']);
    echo "\n{$game['title']} ({$game['id']})\n";
    echo str_repeat('-', 40), "\n";

    if (!$scores) {
        echo "  board is empty\n\n";
        return;
    }

    foreach ($scores as $row) {
        printf("  %2d  %-4s %12s  %s\n",
            $row['rank'], $row['initials'],
            number_format($row['score']),
            substr($row['date'], 0, 10));
    }
    echo "\n";
}

switch ($command) {
    case 'list':
        $games = $registry->all();
        if (!$games) {
            echo "no games installed\n";
            break;
        }
        printf("%-20s %-24s %s\n", 'ID', 'TITLE', 'SCORES');
        foreach ($games as $game) {
            printf("%-20s %-24s %d\n", $game['id'], $game['title'], count($store->top($game['id'])));
        }
        foreach ($registry->problems() as $problem) {
            echo "  skipped: $problem\n";
        }
        break;

    case 'board':
        printBoard($store, requireGame($registry, $gameId));
        break;

    case 'remove':
        $game = requireGame($registry, $gameId);
        $rank = (int) ($argv[3] ?? 0);
        if ($rank < 1) {
            fail('which rank? e.g. php scripts/arcade.php remove ' . $gameId . ' 3');
        }
        if (!$store->remove($game['id'], $rank)) {
            fail("no entry at rank $rank for {$game['id']}");
        }
        echo "removed rank $rank from {$game['title']}\n";
        printBoard($store, $game);
        break;

    case 'clear':
        $game = requireGame($registry, $gameId);
        echo "Wipe the entire board for {$game['title']}? [y/N] ";
        $answer = trim((string) fgets(STDIN));
        if (strtolower($answer) !== 'y') {
            echo "cancelled\n";
            break;
        }
        $store->clear($game['id']);
        echo "cleared {$game['title']}\n";
        break;

    default:
        echo <<<TXT
        Arcade admin

          php scripts/arcade.php list                 every game and how many scores it has
          php scripts/arcade.php board <game>         print one top-10 board
          php scripts/arcade.php remove <game> <rank> delete a single entry
          php scripts/arcade.php clear <game>         wipe a board (asks first)

        TXT;
}
