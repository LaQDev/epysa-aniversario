<?php
/**
 * Componente Modular: Banner Intermedio (Etapa 2 y 3)
 * Ubicación: template-parts/banner-intermedio.php
 */

$bg_type = get_field('banner_mid_bg_type');
$video_id = get_field('banner_mid_video_id');
$grupo_fondo = get_field('banner_mid_image');

$bg_desktop = '';
$bg_tablet = '';
$bg_mobile = '';

if (is_array($grupo_fondo)) {
    $bg_desktop = isset($grupo_fondo['banner_mid_image_desktop']) ? $grupo_fondo['banner_mid_image_desktop'] : '';
    $bg_tablet = isset($grupo_fondo['banner_mid_image_tablet']) ? $grupo_fondo['banner_mid_image_tablet'] : '';
    $bg_mobile = isset($grupo_fondo['banner_mid_image_mobile']) ? $grupo_fondo['banner_mid_image_mobile'] : '';
} elseif (is_string($grupo_fondo) && !empty($grupo_fondo)) {
    $bg_desktop = $grupo_fondo;
}

// SOLUCIÓN: Extracción segura de URL
$bg_desktop_url = is_array($bg_desktop) ? ($bg_desktop['url'] ?? '') : $bg_desktop;
$bg_tablet_url = is_array($bg_tablet) ? ($bg_tablet['url'] ?? '') : $bg_tablet;
$bg_mobile_url = is_array($bg_mobile) ? ($bg_mobile['url'] ?? '') : $bg_mobile;

if (!$bg_tablet_url)
    $bg_tablet_url = $bg_desktop_url;
if (!$bg_mobile_url)
    $bg_mobile_url = $bg_tablet_url;

$title = get_field('banner_mid_title');
$show_subtitle = get_field('banner_mid_show_subtitle');
$subtitle = get_field('banner_mid_subtitle');
$show_btn = get_field('banner_mid_show_btn');
$btn_text = get_field('banner_mid_btn_text');
$btn_link = get_field('banner_mid_btn_link');

if (!$title)
    return;
?>

<section class="hero-section banner-mid">
    <div class="hero-bg">
        <?php if ($bg_type === 'video' && $video_id): ?>
            <div class="video-container">
                <iframe
                    src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?controls=0&autoplay=1&mute=1&loop=1&playlist=<?php echo esc_attr($video_id); ?>&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1"
                    frameborder="0" allowfullscreen></iframe>
            </div>
            <div class="bg-overlay"></div>

        <?php elseif ($bg_desktop_url): ?>
            <div class="bg-media responsive-bg"
                style="--bg-desktop: url('<?php echo esc_url($bg_desktop_url); ?>'); --bg-tablet: url('<?php echo esc_url($bg_tablet_url); ?>'); --bg-mobile: url('<?php echo esc_url($bg_mobile_url); ?>');">
            </div>
            <div class="bg-overlay"></div>

        <?php else: ?>
            <div class="bg-media" style="background-color: #E30613;"></div>
        <?php endif; ?>
    </div>

    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="hero-content">
                <h2 class="hero-title"><?php echo nl2br(esc_html($title)); ?></h2>
                <?php if ($show_subtitle && $subtitle): ?>
                    <div class="hero-subtitle"><?php echo $subtitle; ?></div>
                <?php endif; ?>
                <?php if ($show_btn && $btn_text && $btn_link): ?>
                    <div class="hero-actions">
                        <a href="<?php echo esc_url($btn_link); ?>"
                            class="btn btn-outline-white"><?php echo esc_html($btn_text); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>