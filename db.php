<?php
$host = "localhost";
$user = "cu46eyl7"; // Usuario
$password = "1!JwqQLR+O.&"; // contrasena del usuario
$dbname = "veterinaria"; // Nombre de la base de datos

$conn = new mysqli($host, $user, $password, $dbname);

// Consulta preparada para evitar inyecciones SQL
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario); 
$stmt->execute();
$result = $stmt->get_result();

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
