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
use PHPMailer\PHMailer\Exception;

// Configuración de Supabase
define('SUPABASE_URL', 'https://iukaeqbocpeaegszkpnj.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml1a2FlcWJvY3BlYWVnc3prcG5qIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzI1MTg5OTgsImV4cCI6MjA0ODA5NDk5OH0.Lv79Y3gTFUbrHcdjMCvtZy8is4EBFqrWh3jS72q0Avg');

// Función para insertar datos en Supabase
function insertarCitaEnSupabase($nombre, $edad, $raza, $fecha_cita, $hora, $email, $token) {
    $url = SUPABASE_URL . '/rest/v1/citas';  
    $data = [
        'nombre' => $nombre,
        'edad' => $edad,
        'raza' => $raza,
        'fecha_cita' => $fecha_cita,  
        'hora' => $hora,
        'email' => $email,
        'token' => $token
    ];

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "apikey: " . SUPABASE_KEY,
            "Authorization: Bearer " . SUPABASE_KEY,
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response; 
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

    $token = bin2hex(random_bytes(16));

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

        // Datos del formulario en el cuerpo del correo
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

        // Insertar en Supabase
        $response = insertarCitaEnSupabase($nombre, $edad, $raza, $fecha_cita, $hora, $email, $token);
        // Puedes procesar la respuesta aquí si es necesario

    } catch (Exception $e) {
        echo "Hubo un error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
