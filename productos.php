<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $imagen = isset($_POST['imagen']) && !empty($_POST['imagen']) ? htmlspecialchars(trim($_POST['imagen'])) : null;

    if (!empty($nombre) && !empty($descripcion) && $precio > 0 && $stock >= 0) {
        // Uso de consultas preparadas para evitar inyección SQL
        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $nombre, $descripcion, $precio, $stock, $imagen);

        // Si no se proporciona imagen, usar valor por defecto
        $imagen = $imagen ?: 'default.jpg';

        if ($stmt->execute()) {
            $mensaje = "<p style='color: green;'>Producto agregado correctamente.</p>";
        } else {
            $mensaje = "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        $mensaje = "<p style='color: red;'>Por favor, completa todos los campos correctamente.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <style>
        form {
            margin: 20px auto;
            width: 50%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        label, input, textarea, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .mensaje {
            text-align: center;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <h1>Gestión de Productos</h1>

    <?php if (isset($mensaje)) echo "<div class='mensaje'>$mensaje</div>"; ?>

    <!-- Formulario para agregar productos -->
    <h2>Agregar Producto</h2>
    <form action="productos.php" method="post">
        <label for="nombre">Nombre del producto:</label>
        <input type="text" name="nombre" id="nombre" placeholder="Nombre del producto" required>

        <label for="descripcion">Descripción:</label>
        <textarea name="descripcion" id="descripcion" placeholder="Descripción del producto" required></textarea>

        <label for="precio">Precio:</label>
        <input type="number" name="precio" id="precio" step="0.01" placeholder="Precio en USD" required>

        <label for="stock">Stock:</label>
        <input type="number" name="stock" id="stock" placeholder="Cantidad en inventario" required>

        <label for="imagen">URL de la imagen (opcional):</label>
        <input type="text" name="imagen" id="imagen" placeholder="URL de la imagen del producto (opcional)">

        <button type="submit" name="agregar">Agregar Producto</button>
    </form>
</body>
</html>
