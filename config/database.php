<?php
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

// Database configuration - Check multiple sources with fallbacks
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: $_SERVER['DB_USERNAME'] ?? 'gatherly_sys');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: $_SERVER['DB_PASSWORD'] ?? 'zeND{ATJuYIY');
define('DB_NAME', $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: $_SERVER['DB_DATABASE'] ?? 'gatherly_sad_db');

// Debug: Log if values are not loaded (can be removed in production)
if (empty(DB_HOST) || DB_HOST === 'localhost' && empty(DB_NAME)) {
    error_log("WARNING: Database configuration may not have loaded from .env file");
    error_log("DB_HOST: " . DB_HOST . ", DB_NAME: " . DB_NAME . ", DB_USER: " . DB_USER);
}