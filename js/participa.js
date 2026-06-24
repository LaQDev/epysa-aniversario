/* js/participa.js */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('participa-form');
    if (!form) return;

    const dropZone            = document.getElementById('drop-zone');
    const fileInput           = document.getElementById('materiales-input');
    const gallery             = document.getElementById('preview-gallery');
    const attachmentContainer = document.getElementById('attachment-ids-container');
    const submitBtn           = document.getElementById('submit-btn');

    // fileQueue: array de objetos { file, uid, card, progressBar, status, attachmentId }
    let fileQueue = [];

    const MAX_FILES      = 3;
    const MAX_FILE_SIZE  = 200 * 1024 * 1024; // 200 MB
    const CHUNK_SIZE     =  2 * 1024 * 1024; //  2 MB

    // --- 1. MODAL DE PESO MÁXIMO ---
    const modalPeso         = document.getElementById('modal-archivo-peso');
    const modalPesoFilename = document.getElementById('modal-peso-filename');

    window.cerrarModalPeso = function () {
        if (modalPeso) modalPeso.classList.remove('is-open');
    };

    function abrirModalPeso(filename) {
        if (!modalPeso || !modalPesoFilename) return;
        modalPesoFilename.textContent = filename;
        modalPeso.classList.add('is-open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalPeso && modalPeso.classList.contains('is-open')) {
            cerrarModalPeso();
        }
    });

    // --- 2. CONTADOR DE CARACTERES (Relato) ---
    const relato          = document.getElementById('relato-text');
    const charCountDisplay = document.getElementById('current-chars');

    if (relato && charCountDisplay) {
        relato.addEventListener('input', function () {
            const len = this.value.length;
            charCountDisplay.textContent = len;
            charCountDisplay.style.color = len >= 1500 ? '#E30613' : '';
        });
    }

    // --- 3. DRAG & DROP ---
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    dropZone.addEventListener('dragover',  () => dropZone.classList.add('drag-over'));
    ['dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, () => dropZone.classList.remove('drag-over'));
    });

    dropZone.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));
    fileInput.addEventListener('change', function () {
        handleFiles(this.files);
        this.value = '';
    });

    // --- 4. PROCESAMIENTO DE ARCHIVOS ---
    function handleFiles(files) {
        const newFiles = Array.from(files);

        if ((fileQueue.length + newFiles.length) > MAX_FILES) {
            alert(`Solo puedes subir un máximo de ${MAX_FILES} archivos en total.`);
            return;
        }

        newFiles.forEach(file => {
            if (file.size > MAX_FILE_SIZE) {
                abrirModalPeso(file.name);
                return;
            }
            if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
                alert(`El archivo "${file.name}" no es válido. Solo se permiten imágenes (JPG, PNG) o videos (MP4).`);
                return;
            }

            const entry = {
                file,
                uid:          generateUid(),
                card:         null,
                progressBar:  null,
                status:       'uploading',
                attachmentId: null,
            };

            fileQueue.push(entry);
            buildPreviewCard(entry);
            clearError(document.getElementById('group-materiales'));
            updateSubmitState();

            uploadFileInChunks(entry);
        });
    }

    // --- 5. TARJETA DE PREVISUALIZACIÓN ---
    function buildPreviewCard(entry) {
        const { file } = entry;
        const card = document.createElement('div');
        card.className = 'file-card';

        const thumbWrapper = document.createElement('div');
        thumbWrapper.className = 'thumb-wrapper is-loading';

        const objectUrl = URL.createObjectURL(file);
        let mediaEl;
        if (file.type.startsWith('video/')) {
            mediaEl = document.createElement('video');
            mediaEl.muted = true;
        } else {
            mediaEl = document.createElement('img');
        }
        mediaEl.src = objectUrl;

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn-delete';
        deleteBtn.type = 'button';
        deleteBtn.innerHTML = `<img src="${epysa_vars.theme_url}/assets/icons/trash.svg" alt="Borrar">`;
        deleteBtn.onclick = () => removeFile(entry);

        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        progressContainer.style.display = 'block';

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressBar.style.width = '0%';
        progressBar.style.transition = 'width 0.15s linear';

        const fileName = document.createElement('span');
        fileName.className = 'file-name';
        fileName.textContent = file.name;

        progressContainer.appendChild(progressBar);
        thumbWrapper.appendChild(mediaEl);
        thumbWrapper.appendChild(progressContainer);
        card.appendChild(deleteBtn);
        card.appendChild(thumbWrapper);
        card.appendChild(fileName);
        gallery.appendChild(card);

        entry.card        = card;
        entry.progressBar = progressBar;
    }

    // --- 6. SUBIDA EN CHUNKS ---
    async function uploadFileInChunks(entry) {
        const { file, uid } = entry;
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

        try {
            for (let i = 0; i < totalChunks; i++) {
                if (entry.status === 'cancelled') return;

                const start = i * CHUNK_SIZE;
                const end   = Math.min(start + CHUNK_SIZE, file.size);
                const blob  = file.slice(start, end);

                const formData = new FormData();
                formData.append('action',       'epysa_upload_chunk');
                formData.append('nonce',        epysa_vars.upload_nonce);
                formData.append('file_uid',     uid);
                formData.append('chunk_index',  i);
                formData.append('total_chunks', totalChunks);
                formData.append('file_name',    file.name);
                formData.append('file_type',    file.type);
                formData.append('total_size',   file.size);
                formData.append('chunk',        blob, file.name);

                const result = await sendChunk(formData, i, totalChunks, entry.progressBar);

                if (!result.success) {
                    throw new Error(result.data?.message || 'Error al subir el fragmento.');
                }

                if (result.data.status === 'complete') {
                    entry.attachmentId = result.data.attachment_id;
                    markDone(entry);
                    addAttachmentInput(result.data.attachment_id);
                    updateSubmitState();
                    return;
                }
            }
        } catch (err) {
            if (entry.status !== 'cancelled') {
                markError(entry);
                updateSubmitState();
            }
        }
    }

    function sendChunk(formData, chunkIndex, totalChunks, progressBar) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable && progressBar) {
                    const overall = Math.round(((chunkIndex + e.loaded / e.total) / totalChunks) * 100);
                    progressBar.style.width = overall + '%';
                }
            });

            xhr.addEventListener('load', () => {
                try {
                    resolve(JSON.parse(xhr.responseText));
                } catch {
                    reject(new Error('Respuesta inválida del servidor.'));
                }
            });

            xhr.addEventListener('error',   () => reject(new Error('Error de red al subir el archivo.')));
            xhr.addEventListener('timeout', () => reject(new Error('Tiempo de espera agotado.')));

            xhr.open('POST', epysa_vars.ajax_url);
            xhr.timeout = 60000; // 60 s por chunk
            xhr.send(formData);
        });
    }

    // --- 7. ESTADOS DE TARJETA ---
    function markDone(entry) {
        entry.status = 'done';
        const thumbWrapper      = entry.card.querySelector('.thumb-wrapper');
        const progressContainer = entry.card.querySelector('.progress-container');

        thumbWrapper.classList.remove('is-loading');
        progressContainer.style.opacity = '0';
        setTimeout(() => { progressContainer.style.display = 'none'; }, 300);

        const check = document.createElement('div');
        check.className = 'upload-check';
        check.innerHTML = `<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M2 7L5.5 10.5L12 4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
        thumbWrapper.appendChild(check);
    }

    function markError(entry) {
        entry.status = 'error';
        const thumbWrapper      = entry.card.querySelector('.thumb-wrapper');
        const progressContainer = entry.card.querySelector('.progress-container');

        thumbWrapper.classList.remove('is-loading');
        thumbWrapper.classList.add('has-error');
        progressContainer.style.display = 'none';
        entry.card.classList.add('has-upload-error');
    }

    // --- 8. INPUTS OCULTOS DE ATTACHMENT IDs ---
    function addAttachmentInput(id) {
        const input = document.createElement('input');
        input.type                  = 'hidden';
        input.name                  = 'attachment_ids[]';
        input.value                 = id;
        input.dataset.attachmentId  = id;
        attachmentContainer.appendChild(input);
    }

    // --- 9. ELIMINAR ARCHIVO ---
    function removeFile(entry) {
        entry.status = 'cancelled';
        entry.card.remove();
        fileQueue = fileQueue.filter(e => e !== entry);

        if (entry.attachmentId) {
            const input = attachmentContainer.querySelector(`[data-attachment-id="${entry.attachmentId}"]`);
            if (input) input.remove();
        }

        updateSubmitState();
    }

    // --- 10. ESTADO DEL BOTÓN SUBMIT ---
    function updateSubmitState() {
        if (!submitBtn) return;
        const uploading = fileQueue.some(e => e.status === 'uploading');
        submitBtn.disabled    = uploading;
        submitBtn.textContent = uploading ? 'Subiendo archivos...' : 'Enviar mi historia';
    }

    // --- 11. VALIDACIÓN AL ENVIAR ---
    form.addEventListener('submit', function (e) {
        let isValid = true;
        let firstErrorElement = null;

        const inputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="email"], select, textarea');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
                if (!firstErrorElement) firstErrorElement = input;
            }
        });

        const radioGroup    = document.querySelector('input[name="valor"]:checked');
        const radioContainer = document.getElementById('group-valor');
        if (!radioGroup) {
            showError(radioContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = radioContainer;
        } else {
            clearError(radioContainer);
        }

        const fotoContainer  = document.getElementById('group-materiales');
        const uploadingFiles = fileQueue.filter(e => e.status === 'uploading');
        const doneFiles      = fileQueue.filter(e => e.status === 'done');

        if (uploadingFiles.length > 0) {
            e.preventDefault();
            alert('Espera a que terminen de subirse todos los archivos antes de enviar.');
            return;
        }

        if (doneFiles.length === 0) {
            showError(fotoContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = fotoContainer;
        } else {
            clearError(fotoContainer);
        }

        const legalCheck     = document.getElementById('legal-check');
        const legalContainer = legalCheck.closest('.form-group');
        if (!legalCheck.checked) {
            showError(legalContainer);
            isValid = false;
            if (!firstErrorElement) firstErrorElement = legalContainer;
        } else {
            clearError(legalContainer);
        }

        if (!isValid) {
            e.preventDefault();
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event':     'form_error',
                'form_name': 'participa_aniversario',
                'error_type': 'validacion_frontend'
            });
            if (firstErrorElement) {
                const group = firstErrorElement.closest('.form-group') || firstErrorElement;
                group.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // --- FUNCIONES AUXILIARES ---
    function validateField(input) {
        const group = input.closest('.form-group');
        let valid = true;

        if (!input.value.trim()) {
            valid = false;
        } else if (input.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value.trim())) valid = false;
        }

        if (!valid) { showError(group); return false; }
        clearError(group);
        return true;
    }

    function showError(group)  { if (group) group.classList.add('has-error'); }
    function clearError(group) { if (group) group.classList.remove('has-error'); }

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', function () {
            if (this.type !== 'file') clearError(this.closest('.form-group'));
            if (this.type === 'radio') clearError(document.getElementById('group-valor'));
        });
        el.addEventListener('change', function () {
            if (this.type === 'checkbox') clearError(this.closest('.form-group'));
        });
    });

    function generateUid() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
    }
});
