<?php
/**
 * Plantilla para manejar errores 404 (Página no encontrada)
 * Redirecciona automáticamente a la página de inicio.
 */

// Redirección segura (código 302 temporal o 301 permanente, por defecto wp_redirect usa 302)
wp_redirect(home_url());
exit;
?>