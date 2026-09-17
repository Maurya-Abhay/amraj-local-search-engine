<?php
/**
 * Minimal .env loader — no external dependencies.
 * Reads KEY=VALUE pairs from a .env file and exposes them via amraj_env().
 */

if (!function_exists('amraj_load_env')) {
    function amraj_load_env(string $path): void {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Strip matching surrounding quotes.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
            $_ENV[$key] = $_ENV[$key] ?? $value;
        }
    }
}

if (!function_exists('amraj_env')) {
    function amraj_env(string $key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? null;
        }
        return $value !== null && $value !== '' ? $value : $default;
    }
}
