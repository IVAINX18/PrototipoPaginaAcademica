<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "PHP funciona correctamente<br>";

// Test 1: Conexión a MySQL
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_academica;charset=utf8mb4", "root", "");
    echo "✅ Conexión a base de datos exitosa<br>";
    
    // Test 2: Contar cursos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cursos");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total de cursos en la BD: " . $result['total'] . "<br>";
    
    // Test 3: Listar cursos
    $stmt = $pdo->query("SELECT * FROM cursos LIMIT 3");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Primeros cursos:<br>";
    echo "<pre>";
    print_r($cursos);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>