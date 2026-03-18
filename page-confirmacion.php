<?php
/* Template Name: Página Confirmación */
get_header();
?>

<div class="page-confirmacion">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">

                <div class="success-content">
                    <div class="icon-wrapper">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-approval.svg"
                            alt="Éxito">
                    </div>

                    <h1 class="success-title">
                        ¡Tus kilómetros ya son parte de la historia!
                    </h1>

                    <div class="success-text">
                        <p>Gracias por compartir tu experiencia. Tu relato ha sido recibido correctamente y ahora pasará
                            a una breve revisión. Pronto te avisaremos cuando comience la etapa de votación.</p>
                    </div>

                    <div class="action-wrapper">
                        <a href="<?php echo home_url(); ?>" class="btn btn-outline-red">
                            Volver al Inicio
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>