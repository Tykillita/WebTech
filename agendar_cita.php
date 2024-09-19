<?php
// Configuración de CORS
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye las clases de PHPMailer
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si los datos del formulario fueron enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar datos del formulario
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $raza = $_POST['raza'];
    $fecha_cita = $_POST['fecha_cita'];
    $hora = $_POST['hora'];
    $email = $_POST['email'];

    // Crear una fecha y hora en formato ISO8601 para el archivo ICS
    $fechaInicio = date('Ymd\THis', strtotime($fecha_cita . ' ' . $hora));
    // Asumimos que la cita dura 1 hora
    $fechaFin = date('Ymd\THis', strtotime($fecha_cita . ' ' . $hora . ' +1 hour'));

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

    // Crear un token único para la confirmación
    $token = bin2hex(random_bytes(16));

    // Crear instancia de PHPMailer para el doctor
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
        $mail->addAddress('drpino03@gmail.com', 'Dr. Pino'); // Destinatario

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Nueva cita agendada - Confirmar disponibilidad';

        // Incluir los datos del formulario en el cuerpo del correo
        $mail->Body    = '<b>¡Nueva cita agendada!</b><br>'
                        . 'Nombre Mascota: ' . $nombre . '<br>'
                        . 'Edad: ' . $edad . ' años<br>'
                        . 'Raza: ' . $raza . '<br>'
                        . 'Fecha de la Cita: ' . $fecha_cita . '<br>'
                        . 'Hora de la Cita: ' . $hora . '<br>'
                        . 'Correo del Cliente: ' . $email . '<br><br>'
                        . 'Por favor, confirma tu disponibilidad para esta cita:<br>'
                        . '<a href="http://localhost/Pagina-Veterinaria-main/confirmar_disponibilidad.php?token=' . urlencode($token) . '&fecha_inicio=' . urlencode($fechaInicio) . '&fecha_fin=' . urlencode($fechaFin) . '&nombre=' . urlencode($nombre) . '&raza=' . urlencode($raza) . '&edad=' . urlencode($edad) . '&email=' . urlencode($email) . '">Confirmar Disponibilidad</a>';

        // Adjuntar el archivo .ics
        $mail->addAttachment($icsFilePath, 'Cita_Veterinaria.ics');

        // Enviar el correo
        $mail->send();
        echo 'El correo ha sido enviado con éxito';
        
        // Eliminar el archivo temporal después de enviarlo
        unlink($icsFilePath);

        // Guardar el token en un archivo temporal (simulando una base de datos simple)
        $tokenFile = sys_get_temp_dir() . '/cita_tokens/' . $token;
        if (!is_dir(dirname($tokenFile))) {
            mkdir(dirname($tokenFile), 0777, true);
        }
        file_put_contents($tokenFile, json_encode([
            'nombre' => $nombre,
            'raza' => $raza,
            'edad' => $edad,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'email' => $email
        ]));
    } catch (Exception $e) {
        echo "Hubo un error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
