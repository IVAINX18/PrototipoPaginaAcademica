<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers para JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Función para enviar respuesta JSON
function sendJSON($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Conectar a la base de datos
try {
    $host = "bliw09vjkqs6npl8riiy-mysql.services.clever-cloud.com";
    $dbname = "bliw09vjkqs6npl8riiy";
    $username = "uzpowx253iteiypd";
    $password = "2xD6kfKRP2cjPlUe119e";
    $port = "3306";
    
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
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

// ============================================
// GET - Listar todos los estudiantes o uno específico
// ============================================
if ($method === 'GET') {
    try {
        // Si hay un ID, buscar un estudiante específico
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("
                SELECT e.*, c.nombre as curso_nombre, c.codigo as curso_codigo
                FROM estudiantes e
                LEFT JOIN cursos c ON e.id_curso = c.id_curso
                WHERE e.id_estudiante = ?
            ");
            $stmt->execute([$_GET['id']]);
            $estudiante = $stmt->fetch();
            
            if ($estudiante) {
                sendJSON($estudiante);
            } else {
                sendJSON(['success' => false, 'error' => 'Estudiante no encontrado'], 404);
            }
        } 
        // Listar todos los estudiantes
        else {
            $stmt = $pdo->query("
                SELECT 
                    e.*,
                    c.nombre as curso_nombre,
                    c.codigo as curso_codigo
                FROM estudiantes e
                LEFT JOIN cursos c ON e.id_curso = c.id_curso
                ORDER BY e.id_estudiante DESC
            ");
            
            $estudiantes = $stmt->fetchAll();
            sendJSON($estudiantes);
        }
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al listar estudiantes',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// POST - Crear nuevo estudiante
// ============================================
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
        
        if (empty($data['nombre'])) {
            sendJSON([
                'success' => false,
                'error' => 'El nombre es obligatorio'
            ], 400);
        }
        
        // Insertar el nuevo estudiante
        $stmt = $pdo->prepare("
            INSERT INTO estudiantes (nombre, id_curso, nota_final) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['id_curso'] ?? null,
            $data['nota_final'] ?? null
        ]);
        
        sendJSON([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'message' => 'Estudiante agregado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al crear estudiante',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// PUT - Actualizar estudiante
// ============================================
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
        
        if (empty($data['id_estudiante'])) {
            sendJSON([
                'success' => false,
                'error' => 'ID de estudiante es obligatorio'
            ], 400);
        }
        
        $stmt = $pdo->prepare("
            UPDATE estudiantes 
            SET nombre = ?, id_curso = ?, nota_final = ?
            WHERE id_estudiante = ?
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['id_curso'] ?? null,
            $data['nota_final'] ?? null,
            $data['id_estudiante']
        ]);
        
        sendJSON([
            'success' => true,
            'message' => 'Estudiante actualizado correctamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al actualizar estudiante',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// DELETE - Eliminar estudiante
// ============================================
if ($method === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            sendJSON([
                'success' => false,
                'error' => 'ID no proporcionado'
            ], 400);
        }
        
        $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id_estudiante = ?");
        $stmt->execute([$id]);
        
        sendJSON([
            'success' => true,
            'message' => 'Estudiante eliminado correctamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al eliminar estudiante',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// Método no permitido
// ============================================
sendJSON([
    'success' => false,
    'error' => 'Método no permitido'
], 405);
?>