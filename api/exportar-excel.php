<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=notas_estudiantes_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

try {
    $host = "bliw09vjkqs6npl8riiy-mysql.services.clever-cloud.com";
    $dbname = "bliw09vjkqs6npl8riiy";
    $username = "uzpowx253iteiypd";
    $password = "2xD6kfKRP2cjPlUe119e";

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

try {
    $id_curso = $_GET['curso'] ?? null;

    $query = "
        SELECT
            e.id_estudiante as ID,
            e.nombre as Estudiante,
            c.nombre as Curso,
            c.codigo as Código,
            e.nota_final as 'Nota Final'
        FROM estudiantes e
        LEFT JOIN cursos c ON e.id_curso = c.id_curso
    ";

    if ($id_curso) {
        $query .= " WHERE e.id_curso = :id_curso";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id_curso' => $id_curso]);
    } else {
        $query .= " ORDER BY c.nombre, e.nombre";
        $stmt = $pdo->query($query);
    }

    $estudiantes = $stmt->fetchAll();

    echo "\xEF\xBB\xBF";

    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    echo '</head>';
    echo '<body>';
    echo '<table border="1">';

    echo '<thead>';
    echo '<tr style="background-color: #1a5276; color: white; font-weight: bold;">';
    echo '<th>ID</th>';
    echo '<th>Estudiante</th>';
    echo '<th>Curso</th>';
    echo '<th>Código</th>';
    echo '<th>Nota Final</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';
    foreach ($estudiantes as $est) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($est['ID']) . '</td>';
        echo '<td>' . htmlspecialchars($est['Estudiante']) . '</td>';
        echo '<td>' . htmlspecialchars($est['Curso'] ?? 'Sin curso') . '</td>';
        echo '<td>' . htmlspecialchars($est['Código'] ?? '-') . '</td>';
        echo '<td>' . ($est['Nota Final'] !== null ? number_format($est['Nota Final'], 1) : '-') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';

    echo '</table>';
    echo '</body>';
    echo '</html>';

} catch (PDOException $e) {
    die("Error al exportar: " . $e->getMessage());
}
?>
