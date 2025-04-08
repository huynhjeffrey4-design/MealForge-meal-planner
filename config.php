<?php

// Get values from .env if available
require_once __DIR__ . '/setup.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USERNAME', 'username'));
define('DB_PASS', env('DB_PASSWORD', 'password'));
define('DB_NAME', env('DB_NAME', 'database_name'));
