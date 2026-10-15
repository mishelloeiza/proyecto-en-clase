<?php

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../core/response.php';

/**
 * POST /api/auth/logout.php  -> cierra la sesion y borra la cookie.
 */

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response->methodNotAllowed();
}

Auth::logout();
$response->success('Sesion cerrada', []);
