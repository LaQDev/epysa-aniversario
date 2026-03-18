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

});