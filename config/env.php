<?php
function loadEnv($path)
{
    if (!file_exists($path)) {
        error_log("WARNING: .env file not found at: $path");
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Skip empty lines
        if (empty(trim($line))) {
            continue;
        }

        // Check if line contains '='
        if (strpos($line, '=') === false) {
            continue;
        }

        // Split key=value
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present
        $value = trim($value, '"\'');

        // Store in both $_ENV, $_SERVER, and putenv for maximum compatibility
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv("$name=$value");
    }
}
