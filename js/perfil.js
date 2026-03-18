/* js/perfil.js */

// 1. Abrir Modal Cambio Nombre
function abrirModalCambiarNombre() {
    abrirAuthModal('view-profile-edit-name');
}

// 2. Recargar para ver cambios
function recargarPerfil() {
    window.location.reload();
}

document.addEventListener('DOMContentLoaded', function() {
    
    // 3. FORMULARIO CAMBIO NOMBRE
    const formEdit = document.getElementById('form-edit-name');
    if (formEdit) {
        // Validación input (solo letras)
        const inputs = formEdit.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
        });

        formEdit.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('edit-name-error');
            const nombre = this.querySelector('input[name="nombre"]').value;
            const apellido = this.querySelector('input[name="apellido"]').value;

            // Formatear Capitalización (reutilizamos función global si existe, o definimos local)
            const formatNameLocal = (str) => {
                const ex = ['de', 'del', 'los', 'la', 'las', 'y'];
                return str.trim().toLowerCase().split(' ').map((w, i) => 
                    (i > 0 && ex.includes(w)) ? w : w.charAt(0).toUpperCase() + w.slice(1)
                ).join(' ');
            };

            const nombreFinal = formatNameLocal(nombre);
            const apellidoFinal = formatNameLocal(apellido);

            if (nombreFinal.length < 2 || apellidoFinal.length < 2) {
                errorDiv.innerText = 'Nombres muy cortos.';
                errorDiv.classList.add('visible');
                return;
            }

            btn.innerText = 'Guardando...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'epysa_guardar_nombre'); // Reutilizamos la función backend existente
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
                    cambiarVistaAuth('view-profile-name-success');
                } else {
                    cambiarVistaAuth('view-profile-name-error');
                }
                btn.disabled = false;
                btn.innerText = 'Cambiar nombre';
            })
            .catch(() => {
                cambiarVistaAuth('view-profile-name-error');
            });
        });
    }

    // 4. LISTENER PARA ELIMINAR TARJETA (Evento disparado desde votacion.js)
    document.addEventListener('epysa:voto_removido', function(e) {
        const postId = e.detail.postId;
        const tarjeta = document.getElementById('story-' + postId);
        
        if (tarjeta) {
            // Animación de salida
            tarjeta.style.transition = 'all 0.5s ease';
            tarjeta.style.opacity = '0';
            tarjeta.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                tarjeta.remove();
                // Opcional: Si no quedan tarjetas, mostrar mensaje "No hay votos"
                const grid = document.getElementById('grid-votos-usuario');
                if (grid && grid.children.length === 0) {
                    location.reload(); // Recargar para mostrar empty state
                }
            }, 500);
        }
    });
});