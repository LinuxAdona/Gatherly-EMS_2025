<?php
function loadEnv($path)
{
    if (!file_exists($path)) {
        error_log("WARNING: .env file not found at: $path");
        return false;
    }

    if (!is_readable($path)) {
        error_log("WARNING: .env file is not readable at: $path");
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("ERROR: Could not read .env file at: $path");
        return false;
    }

    $loaded_count = 0;
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

        // Store in multiple places for maximum compatibility
        // $_ENV and $_SERVER are superglobals and accessible everywhere
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv("$name=$value");

        $loaded_count++;
    }

    if ($loaded_count > 0) {
        error_log("SUCCESS: Loaded $loaded_count environment variables from: $path");
    } else {
        error_log("WARNING: No environment variables loaded from: $path");
    }

    return $loaded_count > 0;
}
