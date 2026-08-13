<?php
// Functional test of ScoreStore: truncation, tie rules, low-scoring, rank.
require_once dirname(__DIR__) . '/src/ScoreStore.php';

$dir = sys_get_temp_dir() . '/arcade-test-' . getmypid();
@mkdir($dir, 0775, true);
$store = new ScoreStore($dir);

function check(string $label, mixed $actual, mixed $expected): void {
    $ok = $actual === $expected;
    printf("%s %s  got=%s want=%s\n", $ok ? 'PASS' : 'FAIL', $label,
        json_encode($actual), json_encode($expected));
}

// 12 descending scores into a 10-slot board.
for ($i = 1; $i <= 12; $i++) {
    $store->submit('hello', 'P' . $i, $i * 100);
}
$top = $store->top('hello');
check('board caps at 10', count($top), 10);
check('best score first', $top[0]['score'], 1200);
check('worst surviving score', $top[9]['score'], 300);

// Rank of a mid-table score. Board is 1200..300, so 650 lands under 700 at #7.
[$rank, ] = $store->submit('hello', 'MID', 650);
check('mid-table rank', $rank, 7);

// A score that misses the board entirely.
[$rank2, $scores2] = $store->submit('hello', 'BAD', 1);
check('missed board => null rank', $rank2, null);
check('board still 10 after miss', count($scores2), 10);

// Ties: first to arrive holds the higher spot.
$store->clear('tiegame');
$store->submit('tiegame', 'AAA', 500);
sleep(1); // distinct ISO timestamps
$store->submit('tiegame', 'BBB', 500);
$tie = $store->top('tiegame');
check('tie: earlier entry ranks higher', $tie[0]['initials'], 'AAA');
check('tie: later entry second', $tie[1]['initials'], 'BBB');

// Low-scoring (time attack): smaller is better.
$store->clear('maze');
$store->submit('maze', 'FST', 1240, 'low');
$store->submit('maze', 'SLW', 3000, 'low');
$store->submit('maze', 'MID', 2000, 'low');
$maze = $store->top('maze');
check('low scoring: fastest first', $maze[0]['score'], 1240);
check('low scoring: slowest last', $maze[2]['score'], 3000);

// Initials coercion at the storage layer.
$store->clear('coerce');
$store->submit('coerce', 'ab', 10);
$store->submit('coerce', '<script>', 20);
$store->submit('coerce', '', 30);
$c = $store->top('coerce');
check('short initials padded', $c[2]['initials'], 'ABA');
check('unsafe initials stripped', $c[1]['initials'], 'SCR');
check('empty initials default', $c[0]['initials'], 'AAA');

// remove() by rank
$before = count($store->top('hello'));
$store->remove('hello', 1);
check('remove drops one entry', count($store->top('hello')), $before - 1);

// A corrupt file must read as an empty board, not crash.
file_put_contents($dir . '/junk.json', 'this is not json{{{');
check('corrupt file reads empty', $store->top('junk'), []);

array_map('unlink', glob($dir . '/*.json'));
rmdir($dir);
