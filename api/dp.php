<?php
// Configuración de la base de datos de Clever Cloud
$host = "bliw09vjkqs6npl8riiy-mysql.services.clever-cloud.com";
$dbname = "bliw09vjkqs6npl8riiy";
$username = "uzpowx253iteiypd";
$password = "2xD6kfKRP2cjPlUe119e";
$port = "3306";

try {
    // Crear la conexión PDO
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Opciones de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Manejo seguro del error
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "error" => "Error de conexión a la base de datos",
        "details" => $e->getMessage() // Coméntalo en producción si no quieres exponer detalles
    ]));
}
?>
