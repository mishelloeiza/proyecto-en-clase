<?php

declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../core/response.php';

// Sin argumentos pide login; con un ID o una lista, también exige uno de esos roles.
function requireAuth(int|array $roles = []): array
{
    $response = new Response();
    $data = checkAuth();

    if ($data === null) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $response->ToJson(401, 401, 'Debes iniciar sesion', []);
    }

    if ($roles === []) {
        return $data;
    }

    $allowedRoles = is_int($roles) ? [$roles] : $roles;

    // El login guarda el rol como un objeto: ['id' => ..., 'name' => ...].
    // Comparamos el ID con los roles permitidos por este endpoint, no por el navegador.
    if (!in_array($data['role']['id'], $allowedRoles, true)) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $response->ToJson(403, 403, 'No tienes permiso para realizar esta accion', []);
    }

    return $data;
}
