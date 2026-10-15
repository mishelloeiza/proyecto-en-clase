<?php

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/../core/connection.php';
require_once __DIR__ . '/../core/response.php';

/**
 * Protege un endpoint:
 *   $user = AuthMiddleware::handle($response);            // cualquier usuario con sesion
 *   $user = AuthMiddleware::handle($response, ['Admin']); // solo ciertos roles
 *
 * Reglas: debe haber sesion, el usuario y su rol deben seguir activos
 * y el rol debe estar en la lista permitida (si se indica).
 */
class AuthMiddleware
{
    public static function handle(Response $response, array $roles = []): array
    {
        $userId = Auth::userId();
        if ($userId === null) {
            $response->unauthorized('Sesion requerida');
        }

        try {
            $connection = new Connection();
            $query = $connection->prepare(
                'SELECT u.id, u.username, u.name, u.email, u.status AS user_status,
                        r.name AS role, r.status AS role_status
                   FROM usuarios u
                   INNER JOIN roles r ON r.id = u.role_id
                  WHERE u.id = :id'
            );
            $query->execute(['id' => $userId]);
            $user = $query->fetch();
        } catch (\Throwable $th) {
            $response->throwError($th);
        }

        if (!$user || (int) $user['user_status'] !== STATUS_ACTIVE || (int) $user['role_status'] !== STATUS_ACTIVE) {
            Auth::logout();
            $response->unauthorized('Usuario inactivo o inexistente');
        }

        if ($roles && !in_array($user['role'], $roles, true)) {
            $response->forbidden();
        }

        return [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
    }
}
