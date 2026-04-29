<?php
require_once('wp-load.php');
$to = 'daraya@laq.cl'; // Cambia esto por tu correo
$subject = 'Prueba de correo desde Producción';
$message = 'Si recibes esto, el sistema de correo de PHP está funcionando.';
$sent = wp_mail($to, $subject, $message);

if($sent) {
    echo '¡Correo enviado correctamente!';
} else {
    echo 'Error: El servidor no pudo enviar el correo.';
}