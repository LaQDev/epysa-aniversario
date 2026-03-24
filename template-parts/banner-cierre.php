<?php
/**
 * Componente Modular: Banner de Cierre (Etapa 3)
 * Ubicación: template-parts/banner-cierre.php
 */

$texto = get_field('banner_cierre_texto');
$grupo_fondo = get_field('banner_cierre_image');

$bg_desktop = '';
$bg_tablet = '';
$bg_mobile = '';

if (is_array($grupo_fondo)) {
    $bg_desktop = isset($grupo_fondo['banner_cierre_image_desktop']) ? $grupo_fondo['banner_cierre_image_desktop'] : '';
    $bg_tablet = isset($grupo_fondo['banner_cierre_image_tablet']) ? $grupo_fondo['banner_cierre_image_tablet'] : '';
    $bg_mobile = isset($grupo_fondo['banner_cierre_image_mobile']) ? $grupo_fondo['banner_cierre_image_mobile'] : '';
}

// SOLUCIÓN: Extracción segura de URL
$bg_desktop_url = is_array($bg_desktop) ? ($bg_desktop['url'] ?? '') : $bg_desktop;
$bg_tablet_url = is_array($bg_tablet) ? ($bg_tablet['url'] ?? '') : $bg_tablet;
$bg_mobile_url = is_array($bg_mobile) ? ($bg_mobile['url'] ?? '') : $bg_mobile;

if (!$bg_tablet_url)
    $bg_tablet_url = $bg_desktop_url;
if (!$bg_mobile_url)
    $bg_mobile_url = $bg_tablet_url;

if (!$texto)
    return;
?>

<section class="banner-cierre">
    <?php if ($bg_desktop_url): ?>
        <div class="bg-media responsive-bg"
            style="--bg-desktop: url('<?php echo esc_url($bg_desktop_url); ?>'); --bg-tablet: url('<?php echo esc_url($bg_tablet_url); ?>'); --bg-mobile: url('<?php echo esc_url($bg_mobile_url); ?>');">
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