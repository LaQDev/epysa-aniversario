/* assets/js/main.js */

document.addEventListener('DOMContentLoaded', function () {


    // --- 2. CARRUSEL HISTORIAS ---
    if (document.querySelector('.historias-swiper')) {
        const swiperHistorias = new Swiper('.historias-swiper', {
            slidesPerView: 1, // Móvil: 1 tarjeta completa
            spaceBetween: 20,
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 24 }, // Tablet: 2 tarjetas
                1200: { slidesPerView: 2, spaceBetween: 30 } // Desktop: 2 tarjetas (según PDF parece)
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: { // Agregamos navegación
                nextEl: '.story-next',
                prevEl: '.story-prev',
            },
        });
    }

    // --- 3. LÓGICA DE MODALES (Historias) ---
    const triggers = document.querySelectorAll('.btn-story-trigger');
    const closeButtons = document.querySelectorAll('[data-close-modal]');

    // Función Cerrar
    function closeModal(modal) {
        modal.classList.remove('is-open');
        document.body.style.overflow = ''; // Restaurar scroll

        // Pausar video al cerrar
        const video = modal.querySelector('video');
        if (video) video.pause();
    }

    // Abrir Modal
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault(); // Prevenir salto de página si es un enlace
            const modalId = this.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden'; // Bloquear scroll

                // Lazy-load fuente del video al abrir el modal (mejora rendimiento en iOS)
                const source = modal.querySelector('video source[data-src]');
                if (source) {
                    source.src = source.getAttribute('data-src');
                    source.closest('video').load();
                }

                // === GTM DATALAYER: HISTORIA VISTA ===
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'historia_vista',
                    'historia_id': modalId // Pasamos el ID del modal (ej: modal-story-1)
                });
                // =====================================

                // === GTM DATALAYER: VIDEO COMPLETADO ===
                const video = modal.querySelector('video');
                // Asegurarnos de no agregar el evento múltiples veces si abren y cierran el modal
                if (video && !video.dataset.gtmTracked) {
                    video.addEventListener('ended', function() {
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': 'video_completado',
                            'historia_id': modalId
                        });
                    });
                    video.dataset.gtmTracked = 'true';
                }
                // =======================================
            }
        });
    });

    // Cerrar Modal (Botones y Overlay)
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.epysa-modal');
            closeModal(modal);
        });
    });

    // Cerrar con ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.epysa-modal.is-open');
            if (openModal) closeModal(openModal);
        }
    });

    // --- 4. BOTÓN SCROLL TO TOP ---
    const btnScrollTop = document.getElementById('btn-scroll-top');
    
    if (btnScrollTop) {
        // Escuchar el scroll
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                // Si bajamos más de 300px, mostramos el botón
                btnScrollTop.classList.add('is-visible');
            } else {
                // Si estamos arriba, lo ocultamos
                btnScrollTop.classList.remove('is-visible');
            }
        });

        // Acción al hacer clic
        btnScrollTop.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth' // Scroll suave nativo
            });
        });
    }

});