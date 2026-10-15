<?php

require_once __DIR__ . '/../auth/AuthMiddleware.php';
require_once __DIR__ . '/../core/connection.php';
require_once __DIR__ . '/../core/response.php';

/**
 * CRUD de roles (solo Admin, requiere sesion iniciada).
 *
 *  GET    roles.php           -> lista de roles (sin los eliminados)
 *  GET    roles.php?id=1      -> un rol
 *  POST   roles.php           -> crea   { "name": "Editor", "status": 1 }
 *  PUT    roles.php           -> edita  { "id": 3, "name": "Editor", "status": 1 }
 *  DELETE roles.php?id=3      -> elimina (logico: status = -1)
 *
 * Estados: 1 = activo, 0 = inactivo, -1 = eliminado.
 */

$response = new Response();
AuthMiddleware::handle($response, ['Admin']);

switch ($_SERVER['REQUEST_METHOD']) {

    case 'GET':
        try {
            $connection = new Connection();

            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                if (!is_numeric($_GET['id']) || $id < 1) {
                    $response->invalidParams();
                }

                $query = $connection->prepare('SELECT id, name, status FROM roles WHERE id = :id AND status <> -1');
                $query->execute(['id' => $id]);
                $rol = $query->fetch();

                if (!$rol) {
                    $response->notFound();
                }
                $response->success('Registro encontrado', $rol);
            }

            $query = $connection->prepare('SELECT id, name, status FROM roles WHERE status <> -1');
            $query->execute();
            $response->success('Listado de roles', $query->fetchAll());
        } catch (\Throwable $th) {
            $response->throwError($th);
        }
        break;

    case 'POST':
        try {
            $connection = new Connection();
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $data['name'] ?? '';
            $status = isset($data['status']) ? (int) $data['status'] : 1;

            if (
                !is_string($name) || empty($name) || is_numeric($name) ||
                $status < 0 || $status > 1
            ) {
                $response->invalidParams();
            }

            $sql = 'INSERT INTO roles (name, status) VALUES (:name, :status)';
            $query = $connection->prepare($sql);
            $query->execute([
                'name'   => $name,
                'status' => $status,
            ]);
            $response->success('Rol creado', [], 201);
        } catch (\Throwable $th) {
            $response->throwError($th, 'No se pudo crear el registro');
        }
        break;

    case 'PUT':
        try {
            $connection = new Connection();
            $data = json_decode(file_get_contents('php://input'), true);
            $id = isset($data['id']) ? (int) $data['id'] : 0;
            $name = $data['name'] ?? '';
            $status = isset($data['status']) ? (int) $data['status'] : 1;

            if (
                $id < 1 || !is_string($name) || empty($name) || is_numeric($name) ||
                $status < -1 || $status > 1
            ) {
                $response->invalidParams();
            }

            $sql = 'SELECT * FROM roles WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->execute(['id' => $id]);
            $rolEncontrado = $query->fetch();
            if (!$rolEncontrado) {
                $response->notFound();
            }

            $sql = 'UPDATE roles SET name = :name, status = :status WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->execute([
                'name'   => $name,
                'status' => $status,
                'id'     => $id,
            ]);
            $response->success('Registro actualizado', []);
        } catch (\Throwable $th) {
            $response->throwError($th, 'No se pudo modificar el registro');
        }
        break;

    case 'DELETE':
        try {
            $connection = new Connection();
            $id = (int) ($_GET['id'] ?? 0);
            if (!is_numeric($_GET['id'] ?? null) || $id < 1) {
                $response->invalidParams();
            }

            $sql = 'SELECT * FROM roles WHERE id = :id AND status <> -1';
            $query = $connection->prepare($sql);
            $query->execute(['id' => $id]);
            if (!$query->fetch()) {
                $response->notFound();
            }

            $sql = 'UPDATE roles SET status = -1 WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->execute(['id' => $id]);
            $response->success('Registro eliminado', []);
        } catch (\Throwable $th) {
            $response->throwError($th, 'No se pudo eliminar el registro');
        }
        break;

    default:
        $response->methodNotAllowed();
}
