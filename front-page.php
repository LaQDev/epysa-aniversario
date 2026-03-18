<?php
/**
 * Template Name: Página de Inicio (Router)
 */

get_header();

// Obtener la etapa actual desde las Opciones Globales de ACF
$etapa_actual = get_field('etapa_actual', 'option');
if (!$etapa_actual) $etapa_actual = 1;
$etapa_actual = intval($etapa_actual);

// Enrutador: Cargar la plantilla correspondiente según la etapa
if ($etapa_actual === 1) {
    get_template_part('template-parts/home', 'etapa-1');
} elseif ($etapa_actual === 2) {
    get_template_part('template-parts/home', 'etapa-2');
} elseif ($etapa_actual === 3) {
    get_template_part('template-parts/home', 'etapa-3');
} else {
    // Fallback por defecto
    get_template_part('template-parts/home', 'etapa-1');
}

get_footer();
?>