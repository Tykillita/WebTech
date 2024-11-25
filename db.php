<?php
$host = "localhost";
$user = "cu46eyl7"; // Usuario
$password = "1!JwqQLR+O.&"; // contrasena del usuario
$dbname = "veterinaria"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($host, $user, $password, $dbname);

// Consulta preparada para evitar inyecciones SQL
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario); // El parámetro 's' indica que 'usuario' es un string
$stmt->execute();
$result = $stmt->get_result();

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
