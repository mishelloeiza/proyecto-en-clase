<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/connection.php';
require_once __DIR__ . '/Auth.php';
$response = new Response();
$cookieOptions = [
    'expires' => time() + COOKIE_TTL,
    'path' => '/',
    'secure' => COOKIE_SECURE,
    'httponly' => true,
    'samesite' => 'Strict',
];

// Datos del usuario con su rol y su suscripcion mas reciente.
function findAuthUser(Connection $connection, int $userId): ?array
{
    $query = $connection->prepare('
        SELECT u.id, u.username, u.name, u.email,
               r.id AS role_id, r.name AS role_name,
               s.id AS subscription_id, s.plan_id, p.name AS plan_name,
               s.billing_period, s.starts_at, s.ends_at, s.next_billing_at,
               s.status AS subscription_status,
               CASE WHEN s.status = 1 AND s.starts_at <= CURRENT_TIMESTAMP
                         AND s.ends_at > CURRENT_TIMESTAMP THEN 1 ELSE 0 END AS subscription_active
        FROM usuarios u
        INNER JOIN roles r ON r.id = u.role_id
        LEFT JOIN suscripciones s ON s.user_id = u.id
        LEFT JOIN planes p ON p.id = s.plan_id
        WHERE u.id = :id AND u.status = 1
        ORDER BY s.ends_at DESC
        LIMIT 1
    ');
    $query->execute(['id' => $userId]);
    $user = $query->fetch();
    if (!$user) {
        return null;
    }
    return [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => [
            'id' => (int) $user['role_id'],
            'name' => $user['role_name'],
        ],
        'subscription' => $user['subscription_id'] === null ? null : [
            'id' => (int) $user['subscription_id'],
            'plan_id' => (int) $user['plan_id'],
            'plan_name' => $user['plan_name'],
            'billing_period' => $user['billing_period'],
            'starts_at' => $user['starts_at'],
            'ends_at' => $user['ends_at'],
            'next_billing_at' => $user['next_billing_at'],
            'status' => (int) $user['subscription_status'],
            'active' => (bool) $user['subscription_active'],
        ],
    ];
}

switch ($_SERVER['REQUEST_METHOD']) {
    // Devuelve el usuario de la sesion actual.
    case 'GET':
        $payload = checkAuth();
        if ($payload === null) {
            $response->ToJson(401, 401, 'Cookie no valida o vencida', []);
        }

        try {
            $connection = new Connection();
            $user = findAuthUser($connection, $payload['id']);
            if ($user === null) {
                $response->ToJson(401, 401, 'Usuario no disponible', []);
            }
            $user['exp'] = $payload['exp'];
            $response->success('Usuario autenticado', $user);
        } catch (Throwable $error) {
            $response->internalServerError();
        }
        break;

    // Inicia sesion: body form-data o x-www-form-urlencoded con username y password.
    case 'POST':
        if (
            !isset($_POST['username'], $_POST['password'])
            || !is_string($_POST['username']) || !is_string($_POST['password'])
            || trim($_POST['username']) === '' || $_POST['password'] === ''
        ) {
            $response->invalidParams();
        }

        try {
            $connection = new Connection();
            $query = $connection->prepare('
                SELECT u.id, u.password_hash, u.status AS user_status, r.status AS role_status
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.username = :username
                LIMIT 1
            ');
            $query->execute(['username' => trim($_POST['username'])]);
            $row = $query->fetch();

            // Si el usuario no existe se compara contra un hash falso para no revelar si existe.
            $hash = $row['password_hash'] ?? '$2y$10$7amS00odl5L3/FeQK4tGXOr8/GEZ0qTrted1EBNResOqj2DZvS20G';
            if (!password_verify($_POST['password'], $hash) || !$row) {
                $response->ToJson(401, 401, 'Usuario o contrasena incorrectos', []);
            }

            if ((int) $row['user_status'] !== 1 || (int) $row['role_status'] !== 1) {
                $response->ToJson(403, 403, 'Usuario inactivo', []);
            }

            $user = findAuthUser($connection, (int) $row['id']);
            if ($user === null) {
                $response->ToJson(401, 401, 'Usuario no disponible', []);
            }

            $token = signAuthPayload([
                'id' => $user['id'],
                'role' => $user['role'],
                'exp' => $cookieOptions['expires'],
            ]);
            setcookie(COOKIE_NAME, $token, $cookieOptions);

            $user['exp'] = $cookieOptions['expires'];
            $response->success('Inicio de sesion correcto', $user);
        } catch (Throwable $error) {
            $response->internalServerError();
        }
        break;

    // Cierra sesion: borra la cookie.
    case 'DELETE':
        setcookie(COOKIE_NAME, '', array_merge($cookieOptions, ['expires' => time() - 3600]));
        $response->success('Sesion cerrada', []);
        break;

    default:
        $response->invalidMethod();
}
