<?php
// Configuración de CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Verificar parámetros
if (!isset($_GET['token']) || !isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin']) || !isset($_GET['nombre']) || !isset($_GET['raza']) || !isset($_GET['edad']) || !isset($_GET['email'])) {
    die("Faltan parámetros en la solicitud.");
}

$token = $_GET['token'];
$fechaInicio = $_GET['fecha_inicio'];
$fechaFin = $_GET['fecha_fin'];
$nombre = $_GET['nombre'];
$raza = $_GET['raza'];
$edad = $_GET['edad'];
$email = $_GET['email'];

// Verificar que el token existe
$tokenFile = sys_get_temp_dir() . '/cita_tokens/' . $token;
if (!file_exists($tokenFile)) {
    die("Solicitud de confirmación inválida.");
}

// Leer los datos del token
$data = json_decode(file_get_contents($tokenFile), true);

// Eliminar el archivo del token
unlink($tokenFile);

// Configuración del correo para el cliente
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rmandopareds@gmail.com'; // Correo de origen
    $mail->Password   = 'onlw rbsi jzyt nkdt';    // Contraseña de aplicación de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Configurar el remitente y el destinatario
    $mail->setFrom('rmandopareds@gmail.com', 'Centro Veterinario');
    $mail->addAddress($data['email'], 'Cliente');

    // Crear el contenido del archivo .ics
    $icsContent = "BEGIN:VCALENDAR\r\n";
    $icsContent .= "VERSION:2.0\r\n";
    $icsContent .= "PRODID:-//Tu Empresa//Tu Producto//ES\r\n";
    $icsContent .= "BEGIN:VEVENT\r\n";
    $icsContent .= "UID:" . uniqid() . "\r\n";
    $icsContent .= "DTSTAMP:" . date('Ymd\THis') . "\r\n";
    $icsContent .= "DTSTART:$fechaInicio\r\n";
    $icsContent .= "DTEND:$fechaFin\r\n";
    $icsContent .= "SUMMARY:Cita Veterinaria para $nombre\r\n";
    $icsContent .= "DESCRIPTION:Cita para $nombre (Raza: $raza, Edad: $edad años).\r\n";
    $icsContent .= "END:VEVENT\r\n";
    $icsContent .= "END:VCALENDAR\r\n";

    // Guardar el contenido en un archivo temporal
    $icsFilePath = tempnam(sys_get_temp_dir(), 'cita') . '.ics';
    file_put_contents($icsFilePath, $icsContent);

    // Configurar el contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Disponibilidad del doctor confirmada';
    $mail->Body    = '<b>¡La disponibilidad del doctor ha sido confirmada!</b><br>'
                    . 'Nombre Mascota: ' . $data['nombre'] . '<br>'
                    . 'Edad: ' . $data['edad'] . ' años<br>'
                    . 'Raza: ' . $data['raza'] . '<br>'
                    . 'Fecha de la Cita: ' . date('d/m/Y', strtotime($data['fecha_inicio'])) . '<br>'
                    . 'Hora de la Cita: ' . date('H:i', strtotime($data['fecha_inicio'])) . '<br><br>'
                    . 'Puedes agregar esta cita a tu calendario utilizando el archivo adjunto.<br>'
                    . '<a href="http://localhost/Pagina-Veterinaria-main/Pagina.html">Ver detalles de la cita</a>';

    // Adjuntar el archivo .ics
    $mail->addAttachment($icsFilePath, 'Cita_Veterinaria.ics');

    // Enviar el correo
    $mail->send();
    echo 'El correo de confirmación ha sido enviado con éxito';
    
    // Eliminar el archivo temporal después de enviarlo
    unlink($icsFilePath);
} catch (Exception $e) {
    echo "Hubo un error al enviar el correo: {$mail->ErrorInfo}";
}

// Funciones necesarias para implementar todo en base de datos

//function updateCitaStatus($citaId) {
    // Aquí deberías implementar la lógica para actualizar el estado de la cita en tu base de datos.
    // Esto es solo un ejemplo:
    // $pdo = new PDO('mysql:host=localhost;dbname=tu_base_de_datos', 'usuario', 'contraseña');
    // $stmt = $pdo->prepare("UPDATE citas SET confirmada = 1 WHERE id = ?");
    // $stmt->execute([$citaId]);
//}

//function getCitaDetalles($citaId) {
    // Aquí deberías implementar la lógica para obtener los detalles de la cita desde tu base de datos.
    // Esto es solo un ejemplo:
    // $pdo = new PDO('mysql:host=localhost;dbname=tu_base_de_datos', 'usuario', 'contraseña');
    // $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ?");
    // $stmt->execute([$citaId]);
    // return $stmt->fetch(PDO::FETCH_ASSOC);
//}
?>
