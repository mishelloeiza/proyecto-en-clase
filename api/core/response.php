<?php

class Response
{
    public function ToJson($httpCode, $internalCode, $message, $data)
    {
        http_response_code($httpCode);
        die(json_encode([
            'code' => $internalCode,
            'message' => $message,
            'data' => $data
        ]));
    }

    public function success($message, $data)
    {
        $this->ToJson(200, 200, $message, $data);
    }

    public function error($message, $data)
    {
        $this->ToJson(400, 400, $message, $data);
    }

    public function invalidParams($data = [])
    {
        $this->ToJson(400, 400, "Parametros invalidos", $data);
    }

    public function invalidMethod($data = [])
    {
        $this->ToJson(405, 405, "Metodo no permitido", $data);
    }

    public function notFound()
    {
        $this->ToJson(404, 404, "Recurso no encontrado", []);
    }

    public function internalServerError()
    {
        $this->ToJson(500, 500, "No se pudo procesar la peticion", []);
    }

    public function throwError(Throwable $error)
    {
        $this->ToJson(500, 500, $error->getMessage(), [
            'trace' => $error->getTrace()
        ]);
    }

    public function debugError($error)
    {
        $this->ToJson(500, 500, $error->getMessage(), [
            'file' => $error->getFile(),
            'line' => $error->getLine()
        ]);
    }
}
