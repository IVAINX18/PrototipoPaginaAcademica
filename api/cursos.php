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

// Conectar a la base de datos Clever Cloud
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
// GET - Listar todos los cursos
// ============================================
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT 
                c.*,
                d.nombre as docente_nombre,
                (SELECT COUNT(*) FROM estudiantes WHERE id_curso = c.id_curso) as num_estudiantes,
                (SELECT COUNT(*) FROM actividades WHERE id_curso = c.id_curso) as num_actividades,
                (SELECT ROUND(AVG(nota_final), 2) 
                 FROM estudiantes 
                 WHERE id_curso = c.id_curso 
                 AND nota_final IS NOT NULL) as promedio
            FROM cursos c
            LEFT JOIN docentes d ON c.id_docente = d.id_docente
            ORDER BY c.id_curso DESC
        ");
        
        $cursos = $stmt->fetchAll();
        
        // Asegurar que promedio sea numérico o null
        foreach ($cursos as &$curso) {
            if ($curso['promedio'] !== null) {
                $curso['promedio'] = floatval($curso['promedio']);
            }
        }
        
        sendJSON($cursos);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al listar cursos',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// POST - Crear nuevo curso
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
        
        if (empty($data['nombre']) || empty($data['codigo'])) {
            sendJSON([
                'success' => false,
                'error' => 'Nombre y código son obligatorios'
            ], 400);
        }
        
        // Verificar si el código ya existe
        $check = $pdo->prepare("SELECT id_curso FROM cursos WHERE codigo = ?");
        $check->execute([$data['codigo']]);
        
        if ($check->fetch()) {
            sendJSON([
                'success' => false,
                'error' => 'El código del curso ya existe'
            ], 400);
        }
        
        // Insertar el nuevo curso
        $stmt = $pdo->prepare("
            INSERT INTO cursos (nombre, codigo, descripcion, estado, id_docente) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['codigo'],
            $data['descripcion'] ?? '',
            $data['estado'] ?? 'Activo',
            $data['id_docente'] ?? 1
        ]);
        
        sendJSON([
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'message' => 'Curso creado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al crear curso',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// PUT - Actualizar curso
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
        
        if (empty($data['id_curso'])) {
            sendJSON([
                'success' => false,
                'error' => 'ID de curso es obligatorio'
            ], 400);
        }
        
        $stmt = $pdo->prepare("
            UPDATE cursos 
            SET nombre = ?, codigo = ?, descripcion = ?, estado = ?, id_docente = ?
            WHERE id_curso = ?
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['codigo'],
            $data['descripcion'] ?? '',
            $data['estado'] ?? 'Activo',
            $data['id_docente'] ?? 1,
            $data['id_curso']
        ]);
        
        sendJSON([
            'success' => true,
            'message' => 'Curso actualizado correctamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al actualizar curso',
            'details' => $e->getMessage()
        ], 500);
    }
}

// ============================================
// DELETE - Eliminar curso
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
        
        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id_curso = ?");
        $stmt->execute([$id]);
        
        sendJSON([
            'success' => true,
            'message' => 'Curso eliminado correctamente'
        ]);
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al eliminar curso',
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