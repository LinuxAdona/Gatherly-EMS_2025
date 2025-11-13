<?php
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

// Database configuration - Use $_ENV directly as it's more reliable than getenv()
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USERNAME']);
define('DB_PASS', $_ENV['DB_PASSWORD']);
define('DB_NAME', $_ENV['DB_DATABASE']);
