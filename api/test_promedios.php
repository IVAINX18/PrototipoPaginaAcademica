<?php
// Script de prueba para verificar cálculo de promedios
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/html; charset=UTF-8");

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
    
    echo "<h1>✅ Conexión Exitosa</h1>";
    
} catch (PDOException $e) {
    die("<h1>❌ Error de conexión: " . $e->getMessage() . "</h1>");
}

echo "<h2>📊 Verificación de Promedios por Curso</h2>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #3498db; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .high { color: #27ae60; font-weight: bold; }
    .medium { color: #f39c12; font-weight: bold; }
    .low { color: #e74c3c; font-weight: bold; }
    .null { color: #999; }
</style>";

// Consulta principal
$stmt = $pdo->query("
    SELECT 
        c.id_curso,
        c.nombre as curso,
        c.codigo,
        c.estado,
        COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
        COUNT(DISTINCT CASE WHEN e.nota_final IS NOT NULL THEN e.id_estudiante END) as estudiantes_calificados,
        ROUND(AVG(e.nota_final), 2) as promedio,
        MIN(e.nota_final) as nota_minima,
        MAX(e.nota_final) as nota_maxima,
        SUM(CASE WHEN e.nota_final >= 3.0 THEN 1 ELSE 0 END) as aprobados,
        SUM(CASE WHEN e.nota_final < 3.0 THEN 1 ELSE 0 END) as reprobados
    FROM cursos c
    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
    GROUP BY c.id_curso
    ORDER BY c.nombre
");

$cursos = $stmt->fetchAll();

echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>Curso</th>";
echo "<th>Código</th>";
echo "<th>Estado</th>";
echo "<th>Total Est.</th>";
echo "<th>Calificados</th>";
echo "<th>Promedio</th>";
echo "<th>Min</th>";
echo "<th>Max</th>";
echo "<th>Aprobados</th>";
echo "<th>Reprobados</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($cursos as $curso) {
    $promedio = $curso['promedio'];
    $promedioClass = 'null';
    $promedioTexto = '-';
    
    if ($promedio !== null) {
        $promedioTexto = number_format($promedio, 2);
        if ($promedio >= 4.0) {
            $promedioClass = 'high';
        } elseif ($promedio >= 3.0) {
            $promedioClass = 'medium';
        } else {
            $promedioClass = 'low';
        }
    }
    
    echo "<tr>";
    echo "<td><strong>" . htmlspecialchars($curso['curso']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($curso['codigo']) . "</td>";
    echo "<td>" . htmlspecialchars($curso['estado']) . "</td>";
    echo "<td>" . $curso['total_estudiantes'] . "</td>";
    echo "<td>" . $curso['estudiantes_calificados'] . "</td>";
    echo "<td class='{$promedioClass}'>{$promedioTexto}</td>";
    echo "<td>" . ($curso['nota_minima'] ?? '-') . "</td>";
    echo "<td>" . ($curso['nota_maxima'] ?? '-') . "</td>";
    echo "<td class='high'>" . ($curso['aprobados'] ?? 0) . "</td>";
    echo "<td class='low'>" . ($curso['reprobados'] ?? 0) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

// Estadísticas generales
echo "<h2>📈 Estadísticas Generales</h2>";
echo "<table style='width: 50%;'>";

$statsGeneral = $pdo->query("
    SELECT 
        COUNT(DISTINCT c.id_curso) as total_cursos,
        COUNT(DISTINCT e.id_estudiante) as total_estudiantes,
        COUNT(DISTINCT CASE WHEN e.nota_final IS NOT NULL THEN e.id_estudiante END) as total_calificados,
        ROUND(AVG(e.nota_final), 2) as promedio_general
    FROM cursos c
    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
")->fetch();

echo "<tr><td><strong>Total de Cursos:</strong></td><td>" . $statsGeneral['total_cursos'] . "</td></tr>";
echo "<tr><td><strong>Total de Estudiantes:</strong></td><td>" . $statsGeneral['total_estudiantes'] . "</td></tr>";
echo "<tr><td><strong>Estudiantes Calificados:</strong></td><td>" . $statsGeneral['total_calificados'] . "</td></tr>";
echo "<tr><td><strong>Promedio General:</strong></td><td class='high'><strong>" . ($statsGeneral['promedio_general'] ?? '-') . "</strong></td></tr>";
echo "</table>";

// Detalle por curso con lista de estudiantes
echo "<h2>👥 Detalle de Estudiantes por Curso</h2>";

$cursosConEstudiantes = $pdo->query("
    SELECT 
        c.nombre as curso,
        c.codigo,
        e.nombre as estudiante,
        e.nota_final
    FROM cursos c
    LEFT JOIN estudiantes e ON c.id_curso = e.id_curso
    ORDER BY c.nombre, e.nombre
")->fetchAll();

$cursoActual = '';
foreach ($cursosConEstudiantes as $row) {
    if ($cursoActual !== $row['curso']) {
        if ($cursoActual !== '') {
            echo "</table>";
        }
        $cursoActual = $row['curso'];
        echo "<h3>📚 " . htmlspecialchars($row['curso']) . " (" . htmlspecialchars($row['codigo']) . ")</h3>";
        echo "<table style='width: 70%;'>";
        echo "<thead><tr><th>Estudiante</th><th>Nota Final</th></tr></thead>";
        echo "<tbody>";
    }
    
    $nota = $row['nota_final'];
    $notaClass = 'null';
    $notaTexto = 'Sin calificar';
    
    if ($nota !== null) {
        $notaTexto = number_format($nota, 1);
        if ($nota >= 3.0) {
            $notaClass = 'high';
        } else {
            $notaClass = 'low';
        }
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['estudiante'] ?? 'Sin estudiantes') . "</td>";
    echo "<td class='{$notaClass}'>{$notaTexto}</td>";
    echo "</tr>";
}
echo "</tbody></table>";

echo "<hr style='margin: 30px 0;'>";
echo "<p style='text-align: center; color: #777;'>Reporte generado: " . date('d/m/Y H:i:s') . "</p>";
?>