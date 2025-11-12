<?php
require_once 'env.php';
loadEnv(__DIR__ . '/../.env');

// Database configuration
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USERNAME'));
define('DB_PASS', getenv('DB_PASSWORD'));
define('DB_NAME', getenv('DB_DATABASE'));
