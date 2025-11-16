<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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

try {
    $stats = [];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cursos WHERE estado = 'Activo'");
    $stats['cursos_activos'] = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiantes");
    $stats['total_estudiantes'] = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM actividades WHERE estado = 'Activo'");
    $stats['actividades_pendientes'] = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT AVG(nota_final) as promedio FROM estudiantes WHERE nota_final IS NOT NULL");
    $promedio = $stmt->fetch()['promedio'];
    $stats['promedio_general'] = $promedio ? round($promedio, 1) : 0;

    $stmt = $pdo->query("
        SELECT c.nombre, COUNT(e.id_estudiante) as cantidad
        FROM cursos c
        LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
        WHERE c.estado = 'Activo'
        GROUP BY c.id_curso, c.nombre
        ORDER BY cantidad DESC
        LIMIT 10
    ");
    $stats['estudiantes_por_curso'] = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT c.nombre, AVG(e.nota_final) as promedio
        FROM cursos c
        LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
        WHERE e.nota_final IS NOT NULL AND c.estado = 'Activo'
        GROUP BY c.id_curso, c.nombre
        ORDER BY promedio DESC
    ");
    $stats['promedios_por_curso'] = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT
            CASE
                WHEN nota_final >= 4.5 THEN 'Excelente'
                WHEN nota_final >= 4.0 THEN 'Bueno'
                WHEN nota_final >= 3.0 THEN 'Aprobado'
                ELSE 'Reprobado'
            END as categoria,
            COUNT(*) as cantidad
        FROM estudiantes
        WHERE nota_final IS NOT NULL
        GROUP BY categoria
        ORDER BY FIELD(categoria, 'Excelente', 'Bueno', 'Aprobado', 'Reprobado')
    ");
    $stats['distribucion_notas'] = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT tipo, COUNT(*) as cantidad
        FROM actividades
        WHERE estado = 'Activo'
        GROUP BY tipo
    ");
    $stats['actividades_por_tipo'] = $stmt->fetchAll();

    sendJSON([
        'success' => true,
        'data' => $stats
    ]);

} catch (PDOException $e) {
    sendJSON([
        'success' => false,
        'error' => 'Error al obtener estadísticas',
        'details' => $e->getMessage()
    ], 500);
}
?>
