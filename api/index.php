<?php
error_log("[HELPDESK ENTRY] Starting...");

$root = dirname(__DIR__);
chdir($root);

require_once $root . '/config.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$query = parse_url($uri, PHP_URL_QUERY);

if ($query) {
    parse_str($query, $params);
    $_GET = array_merge($_GET, $params);
}

if (preg_match('#/api\.php#', $path) || isset($_GET['action'])) {
    require $root . '/api.php';
} elseif (preg_match('#/auth\.php#', $path)) {
    require $root . '/auth.php';
} elseif (preg_match('#/tickets\.php#', $path)) {
    require $root . '/tickets.php';
} elseif (preg_match('#/users\.php#', $path)) {
    require $root . '/users.php';
} else {
    require $root . '/index.php';
}
