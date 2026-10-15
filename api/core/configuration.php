<?php

require_once __DIR__ . '/Env.php';
Env::load(__DIR__ . '/.env');

define('COOKIE_NAME', Env::get('COOKIE_NAME', 'nesflis_session'));
define('SESSION_TTL', 60 * 60 * 8); // la sesion dura 8 horas

// La cookie "Secure" solo viaja por HTTPS; si se prueba con http://localhost se desactiva sola.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

ini_set('session.cookie_secure', $isHttps ? '1' : '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_lifetime', (string) SESSION_TTL);
ini_set('session.gc_maxlifetime', (string) SESSION_TTL);

if (session_status() === PHP_SESSION_NONE) {
    session_name(COOKIE_NAME);
    session_start();
}

// Base de datos
define('HOST_DB', Env::get('DATABASE_URL', 'localhost'));
define('DATABASE', Env::get('DATABASE_NAME', 'nesflis'));
define('CHARSET', Env::get('DATABASE_CHARSET', 'utf8mb4'));
define('USER_DB', Env::get('DB_USER', 'root'));
define('PASSWORD_DB', Env::get('DB_PSSW', ''));

define('APP_DEBUG', filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));

// Estados de los registros
define('STATUS_DELETED', -1);
define('STATUS_INACTIVE', 0);
define('STATUS_ACTIVE', 1);

date_default_timezone_set('America/Guatemala');
