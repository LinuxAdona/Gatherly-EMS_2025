<?php

// Database configuration - Check multiple sources with fallbacks
define('DB_HOST', 'localhost');
define('DB_USER', 'gatherly_sys');
define('DB_PASS', 'zeND{ATJuYIY');
define('DB_NAME', 'gatherly_sad_db');

// Debug: Log if values are not loaded (can be removed in production)
if (empty(DB_HOST) || DB_HOST === 'localhost' && empty(DB_NAME)) {
    error_log("WARNING: Database configuration may not have loaded from .env file");
    error_log("DB_HOST: " . DB_HOST . ", DB_NAME: " . DB_NAME . ", DB_USER: " . DB_USER);
}
