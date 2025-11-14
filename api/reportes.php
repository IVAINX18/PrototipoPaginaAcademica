<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers para JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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
$action = $_GET['action'] ?? 'general';

if ($method === 'GET') {
    try {
        switch ($action) {
            case 'general':
                // Estadísticas generales
                $stats = [];
                
                // Total de cursos activos
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM cursos WHERE estado = 'Activo'");
                $stats['cursos_activos'] = $stmt->fetch()['total'];
                
                // Total de estudiantes
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiantes");
                $stats['total_estudiantes'] = $stmt->fetch()['total'];
                
                // Total de actividades
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM actividades");
                $stats['total_actividades'] = $stmt->fetch()['total'];
                
                // Promedio general
                $stmt = $pdo->query("SELECT AVG(nota_final) as promedio FROM estudiantes WHERE nota_final IS NOT NULL");
                $stats['promedio_general'] = round($stmt->fetch()['promedio'], 2);
                
                sendJSON($stats);
                break;
                
            case 'cursos_estadisticas':
                // Estadísticas por curso
                $stmt = $pdo->query("
                    SELECT 
                        c.id_curso,
                        c.nombre,
                        c.codigo,
                        COUNT(DISTINCT e.id_estudiante) as num_estudiantes,
                        COUNT(DISTINCT a.id_actividad) as num_actividades,
                        ROUND(AVG(e.nota_final), 2) as promedio
                    FROM cursos c
                    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
                    LEFT JOIN actividades a ON c.id_curso = a.id_curso
                    WHERE c.estado = 'Activo'
                    GROUP BY c.id_curso
                    ORDER BY promedio DESC
                ");
                
                $cursos = $stmt->fetchAll();
                sendJSON($cursos);
                break;
                
            case 'estudiantes_por_curso':
                // Distribución de estudiantes por curso (Top 10)
                $stmt = $pdo->query("
                    SELECT 
                        c.nombre as curso,
                        COUNT(e.id_estudiante) as cantidad
                    FROM cursos c
                    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
                    WHERE c.estado = 'Activo'
                    GROUP BY c.id_curso
                    ORDER BY cantidad DESC
                    LIMIT 10
                ");
                
                $distribucion = $stmt->fetchAll();
                sendJSON($distribucion);
                break;
                
            case 'rendimiento':
                // Distribución de rendimiento (aprobados/reprobados)
                $stmt = $pdo->query("
                    SELECT 
                        CASE 
                            WHEN nota_final >= 3.0 THEN 'Aprobados'
                            WHEN nota_final < 3.0 THEN 'Reprobados'
                            ELSE 'Sin Calificar'
                        END as estado,
                        COUNT(*) as cantidad
                    FROM estudiantes
                    GROUP BY estado
                ");
                
                $rendimiento = $stmt->fetchAll();
                sendJSON($rendimiento);
                break;
                
            case 'actividades_pendientes':
                // Actividades pendientes por curso
                $stmt = $pdo->query("
                    SELECT 
                        c.nombre as curso,
                        COUNT(a.id_actividad) as pendientes
                    FROM cursos c
                    LEFT JOIN actividades a ON c.id_curso = a.id_curso 
                    WHERE a.estado = 'Pendiente' OR a.fecha_entrega > CURDATE()
                    GROUP BY c.id_curso
                    ORDER BY pendientes DESC
                ");
                
                $pendientes = $stmt->fetchAll();
                sendJSON($pendientes);
                break;
                
            case 'top_estudiantes':
                // Top 10 estudiantes con mejor promedio
                $stmt = $pdo->query("
                    SELECT 
                        e.nombre as estudiante,
                        c.nombre as curso,
                        e.nota_final as promedio
                    FROM estudiantes e
                    LEFT JOIN cursos c ON e.id_curso = c.id_curso
                    WHERE e.nota_final IS NOT NULL
                    ORDER BY e.nota_final DESC
                    LIMIT 10
                ");
                
                $top = $stmt->fetchAll();
                sendJSON($top);
                break;
                
            case 'promedios_mensuales':
                // Promedios por mes (simulado con fechas de actividades)
                $stmt = $pdo->query("
                    SELECT 
                        DATE_FORMAT(a.fecha_entrega, '%Y-%m') as mes,
                        ROUND(AVG(e.nota_final), 2) as promedio
                    FROM actividades a
                    LEFT JOIN cursos c ON a.id_curso = c.id_curso
                    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
                    WHERE a.fecha_entrega IS NOT NULL 
                    AND e.nota_final IS NOT NULL
                    GROUP BY mes
                    ORDER BY mes
                ");
                
                $promedios = $stmt->fetchAll();
                sendJSON($promedios);
                break;
                
            default:
                sendJSON([
                    'success' => false,
                    'error' => 'Acción no válida'
                ], 400);
        }
        
    } catch (PDOException $e) {
        sendJSON([
            'success' => false,
            'error' => 'Error al generar reporte',
            'details' => $e->getMessage()
        ], 500);
    }
}

sendJSON([
    'success' => false,
    'error' => 'Método no permitido'
], 405);
?>