<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

function sendJSON($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {
    $host = "bliw09vjkqs6npl8riiy-mysql.services.clever-cloud.com";
    $dbname = "bliw09vjkqs6npl8riiy";
    $username = "uzpowx253iteiypd";
    $password = "2xD6kfKRP2cjPlUe119e";

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    sendJSON([
        'success' => false,
        'error' => 'Error de conexión a la base de datos',
        'details' => $e->getMessage()
    ], 500);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT a.*, c.nombre as curso_nombre, c.codigo as curso_codigo
                FROM actividades a
                LEFT JOIN cursos c ON a.id_curso = c.id_curso
                WHERE a.id_actividad = ?
            ");
            $stmt->execute([$_GET['id']]);
            $actividad = $stmt->fetch();

            if ($actividad) {
                sendJSON($actividad);
            } else {
                sendJSON(['success' => false, 'error' => 'Actividad no encontrada'], 404);
            }
        } else {
            $stmt = $pdo->query("
                SELECT
                    a.*,
                    c.nombre as curso_nombre,
                    c.codigo as curso_codigo
                FROM actividades a
                LEFT JOIN cursos c ON a.id_curso = c.id_curso
                ORDER BY a.fecha_entrega DESC
            ");

            $actividades = $stmt->fetchAll();
            sendJSON($actividades);
        }

    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al listar actividades',
            'details' => $e->getMessage()
        ], 500);
    }
}

if ($method === 'POST') {
    try {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJSON([
                'success' => false,
                'error' => 'JSON inválido',
                'details' => json_last_error_msg()
            ], 400);
        }

        if (empty($data['nombre']) || empty($data['id_curso'])) {
            sendJSON([
                'success' => false,
                'error' => 'Nombre y curso son obligatorios'
            ], 400);
        }

        $stmt = $pdo->prepare("
            INSERT INTO actividades (nombre, tipo, fecha_entrega, porcentaje, estado, id_curso)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['nombre'],
            $data['tipo'] ?? 'Tarea',
            $data['fecha_entrega'] ?? null,
            $data['porcentaje'] ?? 0,
            $data['estado'] ?? 'Activo',
            $data['id_curso']
        ]);

        sendJSON([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'message' => 'Actividad creada exitosamente'
        ]);

    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al crear actividad',
            'details' => $e->getMessage()
        ], 500);
    }
}

if ($method === 'PUT') {
    try {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJSON([
                'success' => false,
                'error' => 'JSON inválido'
            ], 400);
        }

        if (empty($data['id_actividad'])) {
            sendJSON([
                'success' => false,
                'error' => 'ID de actividad es obligatorio'
            ], 400);
        }

        $stmt = $pdo->prepare("
            UPDATE actividades
            SET nombre = ?, tipo = ?, fecha_entrega = ?, porcentaje = ?, estado = ?, id_curso = ?
            WHERE id_actividad = ?
        ");

        $stmt->execute([
            $data['nombre'],
            $data['tipo'] ?? 'Tarea',
            $data['fecha_entrega'] ?? null,
            $data['porcentaje'] ?? 0,
            $data['estado'] ?? 'Activo',
            $data['id_curso'],
            $data['id_actividad']
        ]);

        sendJSON([
            'success' => true,
            'message' => 'Actividad actualizada correctamente'
        ]);

    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al actualizar actividad',
            'details' => $e->getMessage()
        ], 500);
    }
}

if ($method === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            sendJSON([
                'success' => false,
                'error' => 'ID no proporcionado'
            ], 400);
        }

        $stmt = $pdo->prepare("DELETE FROM actividades WHERE id_actividad = ?");
        $stmt->execute([$id]);

        sendJSON([
            'success' => true,
            'message' => 'Actividad eliminada correctamente'
        ]);

    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al eliminar actividad',
            'details' => $e->getMessage()
        ], 500);
    }
}

sendJSON([
    'success' => false,
    'error' => 'Método no permitido'
], 405);
?>
