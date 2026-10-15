<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/configuration.php';
require_once __DIR__ . '/../core/response.php';

/**
 * La cookie guarda "datos.firma":
 *   datos = JSON en base64 con { id, role: { id, name }, exp }
 *   firma = HMAC-SHA256 de los datos con COOKIE_KEY
 * Si alguien cambia los datos, la firma ya no coincide y la cookie se rechaza.
 */

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string
{
    return (string) base64_decode(strtr($data, '-_', '+/'));
}

function signAuthPayload(array $payload): string
{
    if (COOKIE_KEY === '') {
        throw new RuntimeException('COOKIE_KEY no esta definida en el .env');
    }

    $body = base64UrlEncode(json_encode($payload));
    $signature = base64UrlEncode(hash_hmac('sha256', $body, COOKIE_KEY, true));

    return $body . '.' . $signature;
}

// Devuelve los datos de la cookie si es valida y no ha vencido; si no, null.
function checkAuth(): ?array
{
    $cookie = $_COOKIE[COOKIE_NAME] ?? null;
    if (!is_string($cookie) || COOKIE_KEY === '' || substr_count($cookie, '.') !== 1) {
        return null;
    }

    [$body, $signature] = explode('.', $cookie);

    $expected = base64UrlEncode(hash_hmac('sha256', $body, COOKIE_KEY, true));
    if (!hash_equals($expected, $signature)) {
        return null;
    }

    $payload = json_decode(base64UrlDecode($body), true);

    if (
        !is_array($payload) ||
        !isset($payload['id'], $payload['exp'], $payload['role']['id']) ||
        !is_int($payload['id']) || !is_int($payload['exp']) || !is_int($payload['role']['id']) ||
        $payload['exp'] < time()
    ) {
        return null;
    }

    return $payload;
}
