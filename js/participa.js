/* js/participa.js */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('participa-form');

    // Si no existe el formulario en esta página, no ejecutamos nada
    if (!form) return;

    // --- VARIABLES GLOBALES ---
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('materiales-input');
    const gallery = document.getElementById('preview-gallery');
    const uiContent = document.getElementById('upload-ui-content');

    // Array para almacenar los archivos válidos (Fuente de verdad)
    let uploadedFiles = [];
    const MAX_FILES = 3;

    // --- 1. CONTADOR DE CARACTERES (Relato) ---
    const relato = document.getElementById('relato-text');
    const charCountDisplay = document.getElementById('current-chars');

    if (relato && charCountDisplay) {
        relato.addEventListener('input', function () {
            const currentLength = this.value.length;
            charCountDisplay.textContent = currentLength;

            if (currentLength >= 1500) {
                charCountDisplay.style.color = '#E30613';
            } else {
                charCountDisplay.style.color = '';
            }
        });
    }

    // --- 2. DRAG & DROP Y CARGA DE ARCHIVOS ---

    // Prevenir comportamientos por defecto del navegador
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Estilos visuales al arrastrar
    dropZone.addEventListener('dragover', () => dropZone.classList.add('drag-over'));
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'));
    });

    // Manejar archivos soltados (Drop)
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        handleFiles(dt.files);
    });

    // Manejar archivos seleccionados por click (Input)
    fileInput.addEventListener('change', function () {
        handleFiles(this.files);
    });

    // Lógica principal de procesamiento de archivos
    function handleFiles(files) {
        const newFiles = Array.from(files);

        // Validar cantidad máxima
        if ((uploadedFiles.length + newFiles.length) > MAX_FILES) {
            alert(`Solo puedes subir un máximo de ${MAX_FILES} archivos en total.`);
            return;
        }

        newFiles.forEach(file => {
            // Validar tipo (Imagen o Video)
            if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                // Agregar al array maestro
                uploadedFiles.push(file);
                // Crear tarjeta visual
                createPreviewCard(file);
            } else {
                alert(`El archivo "${file.name}" no es válido. Solo se permiten imágenes (JPG, PNG) o videos (MP4).`);
            }
        });

        // Limpiar error visual si existía
        clearError(document.getElementById('group-materiales'));

        // Resetear input value para permitir seleccionar el mismo archivo de nuevo si se borró
        // (La sincronización real ocurre al enviar el form)
        fileInput.value = '';
    }

    // Crear Tarjeta de Previsualización (DOM)
    function createPreviewCard(file) {
        // Crear contenedor tarjeta
        const card = document.createElement('div');
        card.className = 'file-card';

        // Wrapper de la imagen/video
        const thumbWrapper = document.createElement('div');
        thumbWrapper.className = 'thumb-wrapper is-loading'; // Clase para efecto Blur inicial

        // Elemento Media
        let mediaElement;
        const objectUrl = URL.createObjectURL(file); // URL temporal

        if (file.type.startsWith('video/')) {
            mediaElement = document.createElement('video');
            mediaElement.muted = true; // Muteado para evitar autoplay con sonido
            // mediaElement.playsInline = true; // Opcional
        } else {
            mediaElement = document.createElement('img');
        }
        mediaElement.src = objectUrl;

        // Botón Eliminar
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-delete';
        deleteBtn.type = 'button';
        // Usamos epysa_vars.theme_url para la ruta correcta del icono
        deleteBtn.innerHTML = `<img src="${epysa_vars.theme_url}/assets/icons/trash.svg" alt="Borrar">`;
        deleteBtn.onclick = () => removeFile(file, card);

        // Barra de Progreso
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        progressContainer.style.display = 'block';

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        // Iniciamos en 0%
        progressBar.style.width = '0%';

        // Nombre del archivo
        const fileName = document.createElement('span');
        fileName.className = 'file-name';
        fileName.textContent = file.name;

        // Armar estructura DOM
        progressContainer.appendChild(progressBar);
        thumbWrapper.appendChild(mediaElement);
        thumbWrapper.appendChild(progressContainer);

        card.appendChild(deleteBtn);
        card.appendChild(thumbWrapper);
        card.appendChild(fileName);

        // Insertar en la galería
        gallery.appendChild(card);

        // --- SIMULACIÓN DE CARGA (UX) ---
        // 1. Iniciar animación de barra
        setTimeout(() => {
            progressBar.style.width = '100%';
        }, 50);

        // 2. Finalizar carga (Quitar blur y barra)
        setTimeout(() => {
            thumbWrapper.classList.remove('is-loading');
            progressContainer.style.opacity = '0'; // Desvanecer barra
            setTimeout(() => { progressContainer.style.display = 'none'; }, 300);
        }, 1500); // 1.5 segundos de "carga"
    }

    // Eliminar archivo
    function removeFile(fileToRemove, cardElement) {
        // Filtrar el array global
        uploadedFiles = uploadedFiles.filter(f => f !== fileToRemove);
        // Remover del DOM
        cardElement.remove();
        // Liberar memoria del objeto URL
        // URL.revokeObjectURL(...); // Opcional pero recomendado
    }

    // --- 3. VALIDACIÓN AL ENVIAR (SUBMIT) ---
    form.addEventListener('submit', function (e) {
        let isValid = true;
        let firstErrorElement = null;

        // A. Validar Inputs de Texto
        const inputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="email"], select, textarea');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
                if (!firstErrorElement) firstErrorElement = input;
            }
        });

        // B. Validar Radio Buttons (Valores)
        const radioGroup = document.querySelector('input[name="valor"]:checked');
        const radioContainer = document.getElementById('group-valor');
        if (!radioGroup) {
            showError(radioContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = radioContainer;
        } else {
            clearError(radioContainer);
        }

        // C. Validar Archivos (Nueva Lógica)
        const fotoContainer = document.getElementById('group-materiales');

        if (uploadedFiles.length === 0) {
            showError(fotoContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = fotoContainer;
        } else {
            clearError(fotoContainer);

            // --- SINCRONIZACIÓN FINAL ---
            // Creamos un DataTransfer para actualizar el input[type="file"] real
            // con los archivos que están en nuestro array 'uploadedFiles'
            const dataTransfer = new DataTransfer();
            uploadedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        // D. Validar Checkbox Legal
        const legalCheck = document.getElementById('legal-check');
        // Buscamos el contenedor padre .form-group para mostrar el error
        const legalContainer = legalCheck.closest('.form-group');

        if (!legalCheck.checked) {
            showError(legalContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = legalContainer;
        } else {
            clearError(legalContainer);
        }

        // Detener envío si hay errores
        if (!isValid) {
            e.preventDefault();
            // Scroll al primer error
            if (firstErrorElement) {
                // Intentamos scrollear al .form-group padre para mejor visibilidad
                const group = firstErrorElement.closest('.form-group') || firstErrorElement;
                group.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // --- FUNCIONES AUXILIARES DE VALIDACIÓN ---

    function validateField(input) {
        const group = input.closest('.form-group');
        let isValid = true;

        if (!input.value.trim()) {
            isValid = false;
        } else if (input.type === 'email') {
            // Validación de formato de correo electrónico
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value.trim())) {
                isValid = false;
            }
        }

        if (!isValid) {
            showError(group);
            return false;
        } else {
            clearError(group);
            return true;
        }
    }

    function showError(group) {
        if (group) group.classList.add('has-error');
    }

    function clearError(group) {
        if (group) group.classList.remove('has-error');
    }

    // Limpiar errores en tiempo real al escribir
    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', function () {
            if (this.type !== 'file') { // El file se maneja en handleFiles
                clearError(this.closest('.form-group'));
            }
            if (this.type === 'radio') clearError(document.getElementById('group-valor'));
        });

        el.addEventListener('change', function () {
            if (this.type === 'checkbox') {
                clearError(this.closest('.form-group'));
            }
        });
    });
});