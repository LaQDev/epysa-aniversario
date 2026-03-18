<?php
/**
 * Componente Modular: Banner de Cierre (Etapa 3)
 * Ubicación: template-parts/banner-cierre.php
 */

$texto = get_field('banner_cierre_texto');

// Si no hay texto, no imprimimos la sección
if (!$texto)
    return;
?>

<section class="banner-cierre">
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