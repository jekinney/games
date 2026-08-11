<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/GameRegistry.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_error('method not allowed', 405);
}

$registry = GameRegistry::default();

json_response([
    'games'    => $registry->all(),
    'problems' => $registry->problems(),
]);
