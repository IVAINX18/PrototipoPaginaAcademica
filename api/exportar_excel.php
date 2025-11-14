<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    die("Error de conexión: " . $e->getMessage());
}

$tipo = $_GET['tipo'] ?? 'estudiantes';
$id_curso = $_GET['id_curso'] ?? null;

// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Agregar BOM para UTF-8
echo "\xEF\xBB\xBF";

try {
    switch ($tipo) {
        case 'estudiantes':
            // Exportar estudiantes
            if ($id_curso) {
                $stmt = $pdo->prepare("
                    SELECT 
                        e.id_estudiante as 'ID',
                        e.nombre as 'Nombre Completo',
                        c.nombre as 'Curso',
                        c.codigo as 'Código Curso',
                        e.nota_final as 'Nota Final',
                        CASE 
                            WHEN e.nota_final >= 3.0 THEN 'Aprobado'
                            WHEN e.nota_final < 3.0 THEN 'Reprobado'
                            ELSE 'Sin Calificar'
                        END as 'Estado'
                    FROM estudiantes e
                    LEFT JOIN cursos c ON e.id_curso = c.id_curso
                    WHERE e.id_curso = ?
                    ORDER BY e.nombre
                ");
                $stmt->execute([$id_curso]);
            } else {
                $stmt = $pdo->query("
                    SELECT 
                        e.id_estudiante as 'ID',
                        e.nombre as 'Nombre Completo',
                        c.nombre as 'Curso',
                        c.codigo as 'Código Curso',
                        e.nota_final as 'Nota Final',
                        CASE 
                            WHEN e.nota_final >= 3.0 THEN 'Aprobado'
                            WHEN e.nota_final < 3.0 THEN 'Reprobado'
                            ELSE 'Sin Calificar'
                        END as 'Estado'
                    FROM estudiantes e
                    LEFT JOIN cursos c ON e.id_curso = c.id_curso
                    ORDER BY c.nombre, e.nombre
                ");
            }
            break;
            
        case 'cursos':
            // Exportar cursos con estadísticas
            $stmt = $pdo->query("
                SELECT 
                    c.id_curso as 'ID',
                    c.nombre as 'Nombre del Curso',
                    c.codigo as 'Código',
                    c.descripcion as 'Descripción',
                    c.estado as 'Estado',
                    d.nombre as 'Docente',
                    COUNT(DISTINCT e.id_estudiante) as 'Total Estudiantes',
                    COUNT(DISTINCT a.id_actividad) as 'Total Actividades',
                    ROUND(AVG(e.nota_final), 2) as 'Promedio del Curso'
                FROM cursos c
                LEFT JOIN docentes d ON c.id_docente = d.id_docente
                LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
                LEFT JOIN actividades a ON c.id_curso = a.id_curso
                GROUP BY c.id_curso
                ORDER BY c.nombre
            ");
            break;
            
        case 'actividades':
            // Exportar actividades
            if ($id_curso) {
                $stmt = $pdo->prepare("
                    SELECT 
                        a.id_actividad as 'ID',
                        a.nombre as 'Nombre de la Actividad',
                        a.tipo as 'Tipo',
                        c.nombre as 'Curso',
                        c.codigo as 'Código Curso',
                        DATE_FORMAT(a.fecha_entrega, '%d/%m/%Y') as 'Fecha de Entrega',
                        a.porcentaje as 'Porcentaje (%)',
                        a.estado as 'Estado'
                    FROM actividades a
                    LEFT JOIN cursos c ON a.id_curso = c.id_curso
                    WHERE a.id_curso = ?
                    ORDER BY a.fecha_entrega DESC
                ");
                $stmt->execute([$id_curso]);
            } else {
                $stmt = $pdo->query("
                    SELECT 
                        a.id_actividad as 'ID',
                        a.nombre as 'Nombre de la Actividad',
                        a.tipo as 'Tipo',
                        c.nombre as 'Curso',
                        c.codigo as 'Código Curso',
                        DATE_FORMAT(a.fecha_entrega, '%d/%m/%Y') as 'Fecha de Entrega',
                        a.porcentaje as 'Porcentaje (%)',
                        a.estado as 'Estado'
                    FROM actividades a
                    LEFT JOIN cursos c ON a.id_curso = c.id_curso
                    ORDER BY c.nombre, a.fecha_entrega DESC
                ");
            }
            break;
            
        case 'reporte_completo':
            // Reporte completo por curso
            $stmt = $pdo->query("
                SELECT 
                    c.nombre as 'Curso',
                    c.codigo as 'Código',
                    e.nombre as 'Estudiante',
                    e.nota_final as 'Nota Final',
                    CASE 
                        WHEN e.nota_final >= 3.0 THEN 'Aprobado'
                        WHEN e.nota_final < 3.0 THEN 'Reprobado'
                        ELSE 'Sin Calificar'
                    END as 'Estado',
                    COUNT(a.id_actividad) as 'Actividades del Curso'
                FROM cursos c
                LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
                LEFT JOIN actividades a ON c.id_curso = a.id_curso
                WHERE c.estado = 'Activo'
                GROUP BY c.id_curso, e.id_estudiante
                ORDER BY c.nombre, e.nombre
            ");
            break;
            
        default:
            die("Tipo de exportación no válido");
    }
    
    $data = $stmt->fetchAll();
    
    if (empty($data)) {
        echo "No hay datos para exportar";
        exit;
    }
    
    // Escribir encabezados
    $headers = array_keys($data[0]);
    echo '<table border="1">';
    echo '<thead><tr>';
    foreach ($headers as $header) {
        echo '<th style="background-color: #1a5276; color: white; font-weight: bold; padding: 10px;">' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    
    // Escribir datos
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td style="padding: 5px;">' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    
    // Agregar pie de página con información
    echo '<br><br>';
    echo '<table>';
    echo '<tr><td><strong>Reporte generado:</strong></td><td>' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td><strong>Sistema:</strong></td><td>Plataforma de Gestión Académica</td></tr>';
    echo '<tr><td><strong>Total de registros:</strong></td><td>' . count($data) . '</td></tr>';
    echo '</table>';
    
} catch (PDOException $e) {
    echo '<h2>Error al generar el reporte</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>