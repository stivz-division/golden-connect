<?php

// В Docker-контейнере используем отдельную MySQL-базу для тестов.
// В CI (без Docker) используем SQLite in-memory из phpunit.xml.
if (getenv('DB_HOST') === 'db') {
    $testVars = [
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'db',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'golden_connect_testing',
        'DB_USERNAME' => getenv('DB_USERNAME') ?: 'app',
        'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'changeme',
    ];

    foreach ($testVars as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Отключаем reCAPTCHA в тестах
foreach (['RECAPTCHA_SITE_KEY', 'RECAPTCHA_SECRET_KEY'] as $key) {
    putenv("{$key}=");
    $_ENV[$key] = '';
    $_SERVER[$key] = '';
}

require __DIR__.'/../vendor/autoload.php';
