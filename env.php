<?php
function loadEnv($file) {
    if (!file_exists($file)) {
        die("File .env tidak ditemukan");
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') continue;
        if (substr($line, 0, 1) === '#') continue;

        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) continue;

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $_ENV[$key] = $value;
    }
}