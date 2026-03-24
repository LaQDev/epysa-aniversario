<?php
/**
 * Componente Modular: Banner de Cierre (Etapa 3)
 * Ubicación: template-parts/banner-cierre.php
 */

$texto = get_field('banner_cierre_texto');

// 1. Llamamos al GRUPO completo
$grupo_fondo = get_field('banner_cierre_image');

$bg_desktop = '';
$bg_tablet = '';
$bg_mobile = '';

// 2. Extraemos los subcampos si el grupo tiene datos
if ($grupo_fondo) {
    $bg_desktop = isset($grupo_fondo['banner_cierre_image_desktop']) ? $grupo_fondo['banner_cierre_image_desktop'] : '';
    $bg_tablet = isset($grupo_fondo['banner_cierre_image_tablet']) ? $grupo_fondo['banner_cierre_image_tablet'] : '';
    $bg_mobile = isset($grupo_fondo['banner_cierre_image_mobile']) ? $grupo_fondo['banner_cierre_image_mobile'] : '';
}

// 3. Fallbacks de seguridad (si no suben la de tablet/móvil, heredan la de desktop)
if (!$bg_tablet)
    $bg_tablet = $bg_desktop;
if (!$bg_mobile)
    $bg_mobile = $bg_tablet;

if (!$texto)
    return;
?>

<section class="banner-cierre">
    <?php if ($bg_desktop): ?>
        <div class="bg-media responsive-bg"
            style="--bg-desktop: url('<?php echo esc_url($bg_desktop); ?>'); --bg-tablet: url('<?php echo esc_url($bg_tablet); ?>'); --bg-mobile: url('<?php echo esc_url($bg_mobile); ?>');">
        </div>
        <div class="bg-overlay"></div>
    <?php endif; ?>

    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-12 col-md-9 col-lg-7">
                <h2 class="banner-cierre-text">
                    <?php echo nl2br(esc_html($texto)); ?>
                </h2>
            </div>
        </div>
    </div>
</section>