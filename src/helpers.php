<?php
declare(strict_types=1);

/**
 * Shared helpers. No framework, no autoloader — files require what they need.
 */

const GAME_ID_PATTERN = '/^[a-z0-9][a-z0-9-]{0,31}$/';

/** Absolute path to the project root (the parent of public/). */
function base_path(string $append = ''): string
{
    $root = dirname(__DIR__);
    return $append === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($append, '/\\');
}

/** Send a JSON response and stop. */
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function json_error(string $message, int $status = 400): never
{
    json_response(['error' => $message], $status);
}

/** Decode a JSON request body, or bail with 400. */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        json_error('empty request body');
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_error('body must be a JSON object');
    }
    return $data;
}

/**
 * Reject cross-origin writes. Same-origin requests either omit Origin or
 * match the host we are served from.
 */
function require_same_origin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') {
        return;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (parse_url($origin, PHP_URL_HOST) . ':' . (parse_url($origin, PHP_URL_PORT) ?: '') !== ''
        && rtrim($origin, '/') !== rtrim(request_origin($host), '/')) {
        json_error('cross-origin requests are not allowed', 403);
    }
}

function request_origin(string $host): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    return ($https ? 'https://' : 'http://') . $host;
}

/** Escape for HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Arcade initials: A-Z0-9 only, 3 characters, padded with A. */
function normalize_initials(string $raw): string
{
    $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper($raw)) ?? '';
    $clean = substr($clean, 0, 3);
    return $clean === '' ? 'AAA' : str_pad($clean, 3, 'A');
}

function is_valid_game_id(string $id): bool
{
    return (bool) preg_match(GAME_ID_PATTERN, $id);
}
