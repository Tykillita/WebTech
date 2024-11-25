<?php
// Configuración de CORS
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generación de un token único para la cita
function generarToken() {
    return bin2hex(random_bytes(16));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $raza = $_POST['raza'];
    $fecha_cita = $_POST['fecha_cita'];
    $hora = $_POST['hora'];
    $email = $_POST['email'];

    $fechaInicio = date('Ymd\THis', strtotime($fecha_cita . ' ' . $hora));
    $fechaFin = date('Ymd\THis', strtotime($fecha_cita . ' ' . $hora . ' +1 hour'));

    // Contenido del archivo .ics
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

    $icsFilePath = tempnam(sys_get_temp_dir(), 'cita') . '.ics';
    file_put_contents($icsFilePath, $icsContent);

    // Generar un token único para la cita
    $token = generarToken();

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP con Mailgun
        $mail->isSMTP();
        $mail->Host       = 'smtp.mailgun.org'; // Host de Mailgun
        $mail->SMTPAuth   = true;
        $mail->Username   = 'postmaster@web-centroveterinario.vercel.app'; // Usuario (tu correo de Mailgun)
        $mail->Password   = '856893aa1cea4b9bcc0b575539e11691-c02fd0ba-707e30f41'; // Clave API de Mailgun
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('rmandopareds@gmail.com', 'Centro Veterinario'); // Correo del centro veterinario
        $mail->addAddress('drpino03@gmail.com', 'Dr. Pino'); // Correo del doctor

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Nueva cita agendada - Confirmar disponibilidad';

        $mail->Body    = '<b>¡Nueva cita agendada!</b><br>'
                        . 'Nombre Mascota: ' . $nombre . '<br>'
                        . 'Edad: ' . $edad . ' años<br>'
                        . 'Raza: ' . $raza . '<br>'
                        . 'Fecha de la Cita: ' . $fecha_cita . '<br>'
                        . 'Hora de la Cita: ' . $hora . '<br>'
                        . 'Correo del Cliente: ' . $email . '<br><br>'
                        . 'Por favor, confirma tu disponibilidad para esta cita:<br>'
                        . '<a href="https://web-centroveterinario.vercel.app/confirmar_disponibilidad.php?token=' . urlencode($token) . '&fecha_inicio=' . urlencode($fechaInicio) . '&fecha_fin=' . urlencode($fechaFin) . '&nombre=' . urlencode($nombre) . '&raza=' . urlencode($raza) . '&edad=' . urlencode($edad) . '&email=' . urlencode($email) . '">Confirmar Disponibilidad</a>';

        $mail->addAttachment($icsFilePath, 'Cita_Veterinaria.ics');

        $mail->send();
        echo 'El correo ha sido enviado con éxito';
        
        unlink($icsFilePath);

    } catch (Exception $e) {
        echo "Hubo un error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
