<?php
include 'db.php';

// Verificar si la página se está cargando desde PHP
if (basename($_SERVER['PHP_SELF']) == 'promocion.php') {
    // Consulta segura para obtener productos en promoción
    $query_promociones = $conn->prepare("SELECT nombre, descripcion, precio, imagen FROM productos WHERE promocion = ?");
    $promocion_flag = 1; // Valor para identificar promociones
    $query_promociones->bind_param("i", $promocion_flag);
    $query_promociones->execute();
    $result_promociones = $query_promociones->get_result();

    $contenido_dinamico = "";
    if ($result_promociones->num_rows > 0) {
        while ($producto = $result_promociones->fetch_assoc()) {
            $contenido_dinamico .= '<div class="promocion-item">';
            $contenido_dinamico .= '<img src="' . htmlspecialchars($producto['imagen']) . '" alt="' . htmlspecialchars($producto['nombre']) . '">';
            $contenido_dinamico .= '<h3>' . htmlspecialchars($producto['nombre']) . '</h3>';
            $contenido_dinamico .= '<p>' . htmlspecialchars($producto['descripcion']) . '</p>';
            $contenido_dinamico .= '<span class="precio">$' . number_format($producto['precio'], 2) . '</span>';
            $contenido_dinamico .= '</div>';
        }
    } else {
        $contenido_dinamico = "<p>No hay productos en promoción en este momento.</p>";
    }
    $query_promociones->close();

    // Cargar el contenido de promocion.html
    $html = file_get_contents('promocion.html');

    // Reemplazar el marcador {{dinamico}} con el contenido dinámico
    $html = str_replace('{{dinamico}}', $contenido_dinamico, $html);

    // Mostrar la página completa con el contenido dinámico
    echo $html;
} else {
    // Si se abre el archivo HTML directamente, simplemente mostrar el archivo
    echo file_get_contents('promocion.html');
}
?>
