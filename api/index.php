<?php
error_log("[HELPDESK] Starting...");
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo "PHP OK\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "CWD: " . getcwd() . "\n";
echo "DIR: " . __DIR__ . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . "\n";
