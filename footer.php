<footer id="colophon" class="site-footer">
    <div class="container">
        <div class="footer-inner">

            <div class="footer-branding">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="logo-link">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-epysa-white.svg"
                        alt="Epysa" class="footer-logo">
                </a>
            </div>

            <div class="footer-info">
                <nav class="footer-navigation">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'menu-footer',
                            'menu_id' => 'footer-menu',
                            'depth' => 1,
                            'container' => false,
                        )
                    );
                    ?>
                </nav>
            </div>

        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<div id="modal-historia-wrapper" class="epysa-modal gallery-modal-theme">
    <div class="epysa-modal-overlay" onclick="cerrarModalHistoria()"></div>
    <div id="modal-historia-container" class="epysa-modal-container"></div>
</div>

<div id="auth-modal-wrapper" class="auth-modal-overlay" style="display: none;">
    <div class="auth-modal-container">

        <button class="auth-close-icon" id="auth-close-btn">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/x-close.svg" alt="Cerrar">
        </button>

        <div id="view-login-email" class="auth-view active">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-authentication.svg"
                    alt="">
            </div>
            <h3 class="auth-title">Confirma tu identidad</h3>
            <p class="auth-desc">
                Para asegurar que los ganadores sean elegidos por el equipo Epysa, necesitamos verificar tu identidad.
                Ingresa tu correo institucional y te enviaremos un enlace de acceso inmediato.
            </p>
            <form id="form-login-request">
                <input type="email" name="email" class="auth-input" placeholder="nombre.apellido@epysa.cl" required>
                <div class="auth-error-msg" id="login-error-msg">Por favor, utiliza un correo de Epysa.</div>
                <button type="submit" class="btn btn-primary auth-btn">Recibir enlace de acceso</button>
            </form>
            <button class="btn-text-back" onclick="cerrarAuthModal()">Volver</button>
        </div>

        <div id="view-email-sent" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-send-email.svg" alt="">
            </div>
            <h3 class="auth-title">¡Enlace enviado con éxito!</h3>
            <p class="auth-desc">
                Revisa tu correo corporativo. Te enviamos el enlace para activar tus votos.
            </p>
            <p class="auth-hint-small">Si no llega en unos minutos, revisa tu carpeta de Spam.</p>
            <button class="btn btn-primary auth-btn" onclick="cerrarAuthModal()">Entendido</button>
        </div>

        <div id="view-complete-profile" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-identification.svg"
                    alt="">
            </div>
            <h3 class="auth-title">Confirma tu nombre</h3>
            <p class="auth-desc">
                Para finalizar tu registro, indícanos tu nombre y apellido.
            </p>
            <form id="form-complete-profile">
                <input type="text" name="nombre" class="auth-input mb-2" placeholder="Nombre" required>
                <input type="text" name="apellido" class="auth-input mb-lg" placeholder="Apellido" required>

                <div class="terms-checkbox-wrapper">
                    <input type="checkbox" id="accept-terms" name="accept_terms">
                    <label for="accept-terms">
                        Declaro conocer los <a href="<?php echo home_url('/terminos-y-condiciones'); ?>"
                            target="_blank">Términos y Condiciones.</a>
                    </label>
                </div>

                <div class="auth-error-msg" id="profile-error-msg"></div>

                <button type="submit" class="btn btn-primary auth-btn">Guardar y continuar</button>
            </form>
        </div>

        <div id="view-login-success" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-approval.svg" alt="">
            </div>
            <h3 class="auth-title">¡Ingreso correcto!</h3>
            <p class="auth-desc">
                Ya has iniciado sesión. Puedes revisar las historias y emitir tus votos.
            </p>
            <div class="auth-actions-row">
                <a href="<?php echo home_url(); ?>" class="btn btn-outline auth-btn-half">Ir al inicio</a>
                <a href="<?php echo home_url('/galeria-de-votacion'); ?>" class="btn btn-primary auth-btn-half">Ir a
                    votar</a>
            </div>
        </div>

        <div id="view-auth-error" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-error.svg" alt="">
            </div>
            <h3 class="auth-title">Ocurrió un problema</h3>
            <p class="auth-desc" id="auth-error-text">
                El enlace ha caducado o no es válido. Por favor intenta ingresar nuevamente.
            </p>
            <button class="btn btn-primary auth-btn" onclick="abrirModalLogin()">Intentar de nuevo</button>
        </div>

        <div id="view-vote-login" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-elections.svg" alt="">
            </div>
            <h3 class="auth-title">Confirma tu identidad para votar</h3>
            <p class="auth-desc">
                Para asegurar que los ganadores sean elegidos por el equipo Epysa, necesitamos verificar tu identidad.
            </p>
            <button class="btn btn-primary auth-btn" onclick="abrirModalLogin()">Iniciar sesión</button>
            <button class="btn-text-back" onclick="cerrarAuthModal()">Volver</button>
        </div>

        <div id="view-vote-remove" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-important.svg" alt="">
            </div>
            <h3 class="auth-title">¿Quieres retirar tu voto?</h3>
            <p class="auth-desc">
                Esta acción liberará uno de tus cupos para que puedas elegir otra historia.
            </p>
            <div class="auth-actions-row">
                <button class="btn btn-outline auth-btn-half" onclick="cerrarAuthModal()">Mantener voto</button>
                <button class="btn btn-primary auth-btn-half" id="btn-confirm-remove-vote"
                    style="background-color: #DC2626;">Sí, retirar voto</button>
            </div>
        </div>

        <div id="view-vote-limit" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-error.svg" alt="">
            </div>
            <h3 class="auth-title">¡Ya usaste tus 3 votos!</h3>
            <p class="auth-desc">
                Has alcanzado el límite permitido. Si quieres votar por esta historia, primero debes quitarle tu voto a
                alguna de las anteriores.
            </p>
            <button class="btn btn-primary auth-btn" onclick="cerrarAuthModal()">Entendido</button>
        </div>

        <div id="view-profile-edit-name" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-identification.svg"
                    alt="">
            </div>
            <h3 class="auth-title">Cambiar nombre</h3>
            <p class="auth-desc">
                Ingresa tus datos correctamente. Recuerda no usar números ni caracteres especiales.
            </p>
            <form id="form-edit-name">
                <input type="text" name="nombre" class="auth-input mb-2" placeholder="Nombre" required>
                <input type="text" name="apellido" class="auth-input" placeholder="Apellido" required>
                <div class="auth-error-msg" id="edit-name-error"></div>
                <button type="submit" class="btn btn-primary auth-btn mt-3">Cambiar nombre</button>
            </form>
            <button class="btn-text-back" onclick="cerrarAuthModal()">Volver</button>
        </div>

        <div id="view-profile-name-success" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-name-ok.svg" alt="">
            </div>
            <h3 class="auth-title">¡Nombre actualizado!</h3>
            <p class="auth-desc">
                Tus datos se han guardado correctamente en nuestro registro.
            </p>
            <button class="btn btn-outline auth-btn" onclick="recargarPerfil()">Volver a mi perfil</button>
        </div>

        <div id="view-profile-name-error" class="auth-view">
            <div class="auth-icon">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/modal/icon-name-error.svg" alt="">
            </div>
            <h3 class="auth-title">No pudimos actualizar</h3>
            <p class="auth-desc">
                Ocurrió un error inesperado al intentar guardar tus datos. Por favor intenta nuevamente.
            </p>
            <button class="btn btn-primary auth-btn" onclick="abrirModalCambiarNombre()">Intentar de nuevo</button>
        </div>

    </div>
</div>

<?php wp_footer(); ?>
</body>

</html>