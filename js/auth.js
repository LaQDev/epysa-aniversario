/* js/auth.js */

document.addEventListener('DOMContentLoaded', function () {

    // 1. DETECTAR PARÁMETROS URL
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('auth_action')) {
        const action = urlParams.get('auth_action');
        window.history.replaceState({}, document.title, window.location.pathname);

        if (action === 'complete_profile') {
            abrirAuthModal('view-complete-profile', true); // TRUE = Locked
        } else if (action === 'login_success') {
            abrirAuthModal('view-login-success');
        }
    } else if (urlParams.has('auth_error')) {
        window.history.replaceState({}, document.title, window.location.pathname);
        abrirAuthModal('view-auth-error');
    }

    // 2. FORMULARIO LOGIN (Validación Correo Epysa)
    const formLogin = document.getElementById('form-login-request');
    if (formLogin) {
        formLogin.addEventListener('submit', function (e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[name="email"]');
            const errorDiv = document.getElementById('login-error-msg');
            const btn = this.querySelector('button[type="submit"]');

            // Limpiar errores previos
            errorDiv.classList.remove('visible');
            errorDiv.innerText = '';

            // Validación Frontend Dominio
            const email = emailInput.value.trim().toLowerCase();
            if (!email.endsWith('@epysa.cl') && !email.endsWith('@laq.cl')) {
                errorDiv.innerText = 'Por favor, utiliza un correo de Epysa.';
                errorDiv.classList.add('visible');
                return;
            }

            btn.disabled = true;
            btn.innerText = 'Enviando...';

            const formData = new FormData();
            formData.append('action', 'epysa_solicitar_login');
            formData.append('email', email);
            formData.append('nonce', epysa_ajax.nonce);

            fetch(epysa_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        cambiarVistaAuth('view-email-sent');
                    } else {
                        errorDiv.innerText = data.data.message;
                        errorDiv.classList.add('visible');
                        btn.disabled = false;
                        btn.innerText = 'Recibir enlace de acceso';
                    }
                })
                .catch(err => {
                    console.error(err);
                    errorDiv.innerText = 'Error de conexión.';
                    errorDiv.classList.add('visible');
                    btn.disabled = false;
                    btn.innerText = 'Recibir enlace de acceso';
                });
        });
    }

    // 3. FORMULARIO PERFIL (Nombre/Apellido y Términos)
    const formProfile = document.getElementById('form-complete-profile');
    if (formProfile) {
        // Validación en tiempo real (solo letras)
        const inputs = formProfile.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
        });

        formProfile.addEventListener('submit', function (e) {
            e.preventDefault();
            const nombreInput = this.querySelector('input[name="nombre"]');
            const apellidoInput = this.querySelector('input[name="apellido"]');
            const termsCheckbox = document.getElementById('accept-terms'); // Checkbox
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('profile-error-msg');

            errorDiv.innerText = '';
            errorDiv.classList.remove('visible');

            // 3.1 Validación de Términos y Condiciones
            if (!termsCheckbox.checked) {
                errorDiv.innerText = 'Debes aceptar los términos y condiciones para participar';
                errorDiv.classList.add('visible');
                return;
            }

            // 3.2 Formatear Capitalización (Reglas de/del/los)
            const nombreFinal = formatName(nombreInput.value);
            const apellidoFinal = formatName(apellidoInput.value);

            if (nombreFinal.length < 2 || apellidoFinal.length < 2) {
                errorDiv.innerText = 'Por favor ingresa nombres válidos.';
                errorDiv.classList.add('visible');
                return;
            }

            btn.innerText = 'Guardando...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'epysa_guardar_nombre');
            formData.append('nombre', nombreFinal);
            formData.append('apellido', apellidoFinal);
            formData.append('nonce', epysa_ajax.nonce);

            fetch(epysa_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Cambiar título para nuevo usuario
                        const successView = document.getElementById('view-login-success');
                        if (successView) successView.querySelector('.auth-title').innerText = '¡Registro completado!';

                        // 5. NO RECARGAMOS LA PÁGINA AUTOMÁTICAMENTE
                        cambiarVistaAuth('view-login-success');

                        // Actualizamos visualmente el header si es posible, o esperamos acción del usuario
                        const overlay = document.getElementById('auth-modal-wrapper');
                        overlay.dataset.reloadOnClose = "true";
                    } else {
                        errorDiv.innerText = data.data.message;
                        errorDiv.classList.add('visible');
                        btn.disabled = false;
                        btn.innerText = 'Guardar y continuar';
                    }
                });
        });
    }

    // 4. CERRAR MODAL
    const overlay = document.getElementById('auth-modal-wrapper');
    const closeBtn = document.getElementById('auth-close-btn');

    function handleClose() {
        // Si hay flag de recarga (login exitoso), recargamos al cerrar para actualizar header
        if (overlay.dataset.reloadOnClose === "true") {
            window.location.reload();
        } else {
            cerrarAuthModal();
        }
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', handleClose);
    }

    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay && !overlay.classList.contains('locked')) {
                handleClose();
            }
        });
    }
});

// FUNCIONES AUXILIARES

// 4.2 Lógica de Capitalización Inteligente
function formatName(str) {
    const exceptions = ['de', 'del', 'los', 'la', 'las', 'y'];

    return str.trim().toLowerCase().split(' ').map((word, index) => {
        // La primera palabra siempre va en mayúscula, las demás dependen de la excepción
        if (index > 0 && exceptions.includes(word)) {
            return word;
        }
        return word.charAt(0).toUpperCase() + word.slice(1);
    }).join(' ');
}

function abrirModalLogin() {
    abrirAuthModal('view-login-email');
}

function abrirAuthModal(viewId, locked = false) {
    const wrapper = document.getElementById('auth-modal-wrapper');
    const closeBtn = document.getElementById('auth-close-btn');

    wrapper.style.display = 'flex';

    if (locked) {
        wrapper.classList.add('locked');
        if (closeBtn) closeBtn.style.display = 'none';
    } else {
        wrapper.classList.remove('locked');
        if (closeBtn) closeBtn.style.display = 'flex'; // Flex para centrar icono
    }

    cambiarVistaAuth(viewId);
}

function cerrarAuthModal() {
    const wrapper = document.getElementById('auth-modal-wrapper');
    if (!wrapper.classList.contains('locked')) {
        wrapper.style.display = 'none';
    }
}

function cambiarVistaAuth(viewId) {
    document.querySelectorAll('.auth-view').forEach(el => el.classList.remove('active'));
    const target = document.getElementById(viewId);
    if (target) target.classList.add('active');
}