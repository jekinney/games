<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/src/GameRegistry.php';
require_once $root . '/src/ScoreStore.php';
require_once $root . '/src/RateLimiter.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$registry = GameRegistry::default();
$store = ScoreStore::default();

if ($method === 'GET') {
    $id = (string) ($_GET['game'] ?? '');
    $game = $registry->find($id);
    if ($game === null) {
        json_error('unknown game', 404);
    }

    json_response([
        'game'    => $game['id'],
        'scoring' => $game['scoring'],
        'scores'  => $store->top($game['id']),
    ]);
}

if ($method !== 'POST') {
    json_error('method not allowed', 405);
}

require_same_origin();

$body = read_json_body();

/* --- game --- */
$id = (string) ($body['game'] ?? '');
$game = $registry->find($id);
if ($game === null) {
    json_error('unknown game', 400);
}

/* --- score --- */
if (!isset($body['score']) || !is_numeric($body['score']) || (float) $body['score'] != floor((float) $body['score'])) {
    json_error('score must be a whole number', 400);
}
$score = (int) $body['score'];
if ($score < 0 || $score > ScoreStore::MAX_SCORE) {
    json_error('score out of range', 400);
}

/* --- per-game sanity bounds from game.json --- */
if ($game['maxScore'] !== null && $score > $game['maxScore']) {
    json_error('score above this game\'s maximum', 422);
}
if ($game['minScore'] !== null && $score < $game['minScore']) {
    json_error('score below this game\'s minimum', 422);
}

/* --- rate limit --- */
$limiter = RateLimiter::default();
if (!$limiter->allow(RateLimiter::callerKey())) {
    json_error('too many submissions, slow down', 429);
}

/* --- initials: always coerced, never rejected --- */
$initials = normalize_initials((string) ($body['initials'] ?? ''));

try {
    [$rank, $scores] = $store->submit($game['id'], $initials, $score, $game['scoring']);
} catch (Throwable $e) {
    error_log('[arcade] score submit failed: ' . $e->getMessage());
    json_error('could not save the score', 500);
}

json_response([
    'accepted' => $rank !== null,
    'rank'     => $rank,
    'initials' => $initials,
    'scores'   => $scores,
]);
