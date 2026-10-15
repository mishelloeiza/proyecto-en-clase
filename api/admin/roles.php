<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../core/connection.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$reponse = new Response();
requireAuth(ROLE_ADMIN); // solo el rol Admin

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $connection = new Connection();
            $sql = "SELECT id as codigo, name as nombre, status as estado FROM roles";

            $query = $connection->prepare($sql);
            $query->execute();
            $roles = $query->fetchAll();
            $reponse->success("Información obtenida", $roles);

        } catch (\Throwable $th) {
            $reponse->throwError($th);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input', true));
        try {
            $connection = new Connection();
            $name = $data->{"name"} ?? '';
            $status = (int)($data->{"status"} ?? 1);

            if (
                !isset($name) || empty($name) || is_numeric($name) || !is_string($name) ||
                !is_numeric($status) || $status < -1 || $status > 3
            ) {
                $reponse->invalidParams();
            }

            $sql = "INSERT INTO roles (name, status) VALUES (:name, :status)";
            $query = $connection->prepare($sql);
            $query->execute([
                "name" => $name,
                "status" => $status
            ]);
            $reponse->success("Rol creado", []);
        } catch (\Throwable $th) {
            #$reponse->throwError($th);
            $reponse->error("No se pudo crear el registro", []);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input', true));
        try {
            $connection = new Connection();
            $id = (int)($data->{"id"} ?? '');
            $name = $data->{"name"} ?? '';
            $status = (int)($data->{"status"} ?? '');

            if (
                !isset($name) || empty($name) || is_numeric($name) || !is_string($name) ||
                !is_numeric($status) || $status < -1 || $status > 3
            ) {
                $reponse->invalidParams();
            }

            $sql = "SELECT * FROM roles WHERE id = :id";
            $query = $connection->prepare($sql);
            $query->execute(["id" => $id]);
            $rolEncontrado = $query->fetch();
            if (!$rolEncontrado) {
                $reponse->error("Registro no encontrado", []);
            }

            $sql = "UPDATE roles SET name = :name, status = :status WHERE id = :id";
            $query = $connection->prepare($sql);
            $query->execute([
                "name" => $name,
                "status" => $status,
                "id" => $id
            ]);
            $reponse->success("Registro actualizado", []);
        } catch (\Throwable $th) {
            #$reponse->throwError($th);
            $reponse->error("No se pudo modificar el registro", []);
        }
        break;
    case 'DELETE':
        try {
            $connection = new Connection();
            $id = (int)($_GET["id"] ?? '');
            if (!is_numeric($id) || empty($id)) {
                $reponse->invalidParams();
            }

            $sql = "UPDATE roles SET status = -1 WHERE id = :id";
            $query = $connection->prepare($sql);
            $query->execute([
                "id" => $id
            ]);
            $reponse->success("Registro eliminado", []);
        } catch (\Throwable $th) {
            #$reponse->throwError($th);
            $reponse->error("No se pudo modificar el registro", []);
        }
        break;
    default:
        $reponse->invalidMethod();
}
