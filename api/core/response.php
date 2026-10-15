<?php

require_once __DIR__ . '/configuration.php';

/**
 * Respuesta JSON para todo el API: { "success": bool, "message": string, "data": mixed }
 * Cada metodo envia la respuesta y termina la ejecucion.
 */
class Response
{
    public function __construct()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public function send(bool $success, string $message, $data, int $code)
    {
        http_response_code($code);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function success(string $message, $data = [], int $code = 200)
    {
        $this->send(true, $message, $data, $code);
    }

    public function error(string $message, $data = [], int $code = 400)
    {
        $this->send(false, $message, $data, $code);
    }

    public function invalidParams()
    {
        $this->error('Parametros invalidos', [], 400);
    }

    public function unauthorized(string $message = 'No autorizado')
    {
        $this->error($message, [], 401);
    }

    public function forbidden(string $message = 'No tienes permisos para esta accion')
    {
        $this->error($message, [], 403);
    }

    public function notFound(string $message = 'Registro no encontrado')
    {
        $this->error($message, [], 404);
    }

    public function methodNotAllowed()
    {
        $this->error('Metodo no permitido', [], 405);
    }

    /**
     * Error inesperado (500). Se guarda en el log y solo se muestra el
     * detalle cuando APP_DEBUG=true en el .env.
     */
    public function throwError(\Throwable $th, string $message = 'Error interno del servidor')
    {
        error_log('[nesflis] ' . $th->getMessage() . ' en ' . $th->getFile() . ':' . $th->getLine());

        $data = APP_DEBUG ? ['error' => $th->getMessage(), 'line' => $th->getLine()] : [];
        $this->error($message, $data, 500);
    }
}
