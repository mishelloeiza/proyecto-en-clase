<?php

require_once __DIR__ . '/../core/configuration.php';

/**
 * Manejo de la sesion. La cookie guarda solo el id de sesion
 * (HttpOnly + SameSite=Strict); los datos viven en el servidor.
 */
class Auth
{
    public static function login(int $userId): void
    {
        session_regenerate_id(true); // evita fijacion de sesion
        $_SESSION['user_id'] = $userId;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);

        session_destroy();
    }
}
