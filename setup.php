<?php
require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
	$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
}

// Function to get environment variables with fallbacks
function env($key, $default = null)
{
	return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}
