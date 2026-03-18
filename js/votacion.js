/* js/votacion.js */

// Variable temporal para guardar qué historia se va a eliminar
let historiaPendienteBorrar = null;
let botonPendienteBorrar = null;

function gestionarVoto(btn, postId) {
    // 1. Verificar si está logueado
    const isLogged = document.body.classList.contains('logged-in');

    if (!isLogged) {
        abrirAuthModal('view-vote-login');
        return;
    }

    // 2. Determinar Acción
    if (btn.classList.contains('btn-quitar-voto')) {
        // ACCIÓN: QUITAR VOTO -> Abrir confirmación
        historiaPendienteBorrar = postId;
        botonPendienteBorrar = btn;
        abrirAuthModal('view-vote-remove');
    } else {
        // ACCIÓN: VOTAR -> Llamada directa
        enviarVotoBackend(postId, 'votar', btn);
    }
}

// Lógica AJAX
function enviarVotoBackend(postId, accion, btnRef) {
    const textoOriginal = btnRef.innerText;
    btnRef.innerText = '...';
    btnRef.disabled = true;

    const formData = new FormData();
    formData.append('action', 'epysa_procesar_voto');
    formData.append('post_id', postId);
    formData.append('tipo_accion', accion);
    formData.append('nonce', epysa_ajax.nonce);

    fetch(epysa_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            btnRef.disabled = false;

            if (data.success) {
                // ÉXITO: Actualizar UI
                actualizarBoton(btnRef, accion);

                if (accion === 'quitar') {
                    cerrarAuthModal();
                    historiaPendienteBorrar = null;
                    botonPendienteBorrar = null;

                    // --- EVENTO PARA PERFIL (Eliminar tarjeta) ---
                    const event = new CustomEvent('epysa:voto_removido', {
                        detail: { postId: postId }
                    });
                    document.dispatchEvent(event);
                }
            } else {
                // ERROR
                btnRef.innerText = textoOriginal;

                if (data.data.code === 'limit_reached') {
                    abrirAuthModal('view-vote-limit');
                } else if (data.data.code === 'not_logged_in') {
                    abrirAuthModal('view-vote-login');
                } else {
                    alert(data.data.message);
                }
            }
        })
        .catch(err => {
            console.error(err);
            btnRef.innerText = textoOriginal;
            btnRef.disabled = false;
        });
}

function actualizarBoton(btn, accionRealizada) {
    // Definir textos según contexto (Modal vs Grilla)
    const esModal = btn.closest('.modal-col-content') !== null;
    const txtVotar = esModal ? 'Votar por esta historia' : 'Votar';

    // Configurar el botón actual
    if (accionRealizada === 'votar') {
        btn.classList.remove('btn-votar', 'btn-votar-modal');
        btn.classList.add('btn-quitar-voto');
        btn.innerText = 'Quitar voto';
    } else {
        btn.classList.remove('btn-quitar-voto');
        btn.classList.add(esModal ? 'btn-votar-modal' : 'btn-votar');
        btn.innerText = txtVotar;
    }

    // SINCRONIZAR otros botones, EXCEPTO el botón de leer historia (.btn-link-read)
    const idHistoria = btn.getAttribute('data-id');
    const todosLosBotones = document.querySelectorAll(`button[data-id="${idHistoria}"]:not(.btn-link-read)`);

    todosLosBotones.forEach(otroBtn => {
        if (otroBtn !== btn) {
            // Copiar estado
            otroBtn.className = btn.className;

            // Ajustar texto según contexto
            const otroEsModal = otroBtn.closest('.modal-col-content') !== null;
            if (accionRealizada === 'votar') {
                otroBtn.innerText = 'Quitar voto';
            } else {
                otroBtn.innerText = otroEsModal ? 'Votar por esta historia' : 'Votar';
            }
        }
    });
}

// Listener para confirmar borrado
document.addEventListener('DOMContentLoaded', function () {
    const btnConfirmRemove = document.getElementById('btn-confirm-remove-vote');
    if (btnConfirmRemove) {
        btnConfirmRemove.addEventListener('click', function () {
            if (historiaPendienteBorrar && botonPendienteBorrar) {
                enviarVotoBackend(historiaPendienteBorrar, 'quitar', botonPendienteBorrar);
            }
        });
    }
});