<?php

require_once __DIR__ . '/Env.php';
Env::load(__DIR__ . '/.env');

// Cookie de autenticacion
define('COOKIE_NAME', Env::get('COOKIE_NAME', 'nesflis_auth'));
define('COOKIE_KEY', Env::get('COOKIE_KEY', ''));
define('COOKIE_TTL', 60 * 60 * 8); // la cookie dura 8 horas

// "Secure" solo viaja por HTTPS; con http://localhost se desactiva sola.
define('COOKIE_SECURE',
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
);

// Base de datos
define('HOST_DB', Env::get('DATABASE_URL', 'localhost'));
define('DATABASE', Env::get('DATABASE_NAME', 'nesflis'));
define('CHARSET', Env::get('DATABASE_CHARSET', 'utf8mb4'));
define('USER_DB', Env::get('DB_USER', 'root'));
define('PASSWORD_DB', Env::get('DB_PSSW', ''));

// Id del rol Admin en la tabla roles
define('ROLE_ADMIN', 1);

date_default_timezone_set('America/Guatemala');
