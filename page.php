<?php
/**
 * Plantilla por defecto para páginas estáticas
 * Se usará para "Términos y Condiciones", "Políticas", etc.
 */

get_header();
?>

<main id="primary" class="site-main default-page-template">
    
    <div class="container">
        
        <?php
        // Loop nativo de WordPress
        while (have_posts()) :
            the_post();
            ?>
            
            <header class="page-header text-center">
                <?php the_title('<h1 class="page-title">', '</h1>'); ?>
            </header>

            <div class="page-content">
                <?php
                // Imprime todo lo que se ponga en el editor de bloques/clásico
                the_content();
                ?>
            </div>

        <?php
        endwhile; // Fin del loop.
        ?>

    </div>
</main>

<?php get_footer(); ?>