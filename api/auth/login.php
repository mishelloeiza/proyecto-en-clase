<?php

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../core/connection.php';
require_once __DIR__ . '/../core/response.php';

/**
 * POST /api/auth/login.php
 * Body JSON: { "username": "admin", "password": "Admin123!" }
 *
 * Reglas:
 *  - username: texto obligatorio, maximo 20 caracteres.
 *  - password: texto obligatorio, maximo 72 caracteres (limite de bcrypt).
 *  - Usuario o contrasena incorrectos => mismo mensaje.
 *  - El usuario y su rol deben estar activos.
 * Si todo es correcto, el servidor crea la sesion y envia la cookie.
 */

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response->methodNotAllowed();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = is_array($data) ? ($data['username'] ?? null) : null;
    $password = is_array($data) ? ($data['password'] ?? null) : null;

    if (
        !is_string($username) || !is_string($password) ||
        trim($username) === '' || $password === '' ||
        strlen(trim($username)) > 20 || strlen($password) > 72
    ) {
        $response->invalidParams();
    }

    $connection = new Connection();
    $query = $connection->prepare(
        'SELECT u.id, u.username, u.password_hash, u.name, u.email, u.status AS user_status,
                r.name AS role, r.status AS role_status
           FROM usuarios u
           INNER JOIN roles r ON r.id = u.role_id
          WHERE u.username = :username'
    );
    $query->execute(['username' => trim($username)]);
    $user = $query->fetch();

    // Si el usuario no existe se compara contra un hash falso para no revelar si existe.
    $hash = $user['password_hash'] ?? '$2y$10$7amS00odl5L3/FeQK4tGXOr8/GEZ0qTrted1EBNResOqj2DZvS20G';
    $passwordOk = password_verify($password, $hash);

    if (!$user || !$passwordOk) {
        $response->unauthorized('Usuario o contrasena incorrectos');
    }

    if ((int) $user['user_status'] !== STATUS_ACTIVE) {
        $response->forbidden('Usuario inactivo');
    }

    if ((int) $user['role_status'] !== STATUS_ACTIVE) {
        $response->forbidden('El rol del usuario esta inactivo');
    }

    Auth::login((int) $user['id']);

    $response->success('Inicio de sesion correcto', [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
        'name'     => $user['name'],
        'email'    => $user['email'],
        'role'     => $user['role'],
    ]);
} catch (\Throwable $th) {
    $response->throwError($th);
}
