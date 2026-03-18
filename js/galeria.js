/* js/galeria.js */

// Variable global para la instancia del Swiper del modal
let modalSwiper = null;

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       1. LÓGICA DE FILTROS Y GRILLA (Tu código original)
       ========================================= */
    const form = document.getElementById('filtros-form');
    const resultsContainer = document.getElementById('resultados-galeria');
    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreBtn = document.getElementById('btn-cargar-mas');

    let currentPage = 1;
    let isLoading = false;
    let debounceTimer;

    // Función Principal de Carga
    function cargarHistorias(page = 1, append = false) {
        if (isLoading) return;
        isLoading = true;

        if (!form || !resultsContainer) return; // Validación seguridad

        if (!append) {
            resultsContainer.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-danger"></div></div>';
            if (loadMoreContainer) loadMoreContainer.style.display = 'none';
        }

        const formData = new FormData(form);
        formData.append('action', 'filtrar_historias');
        formData.append('paged', page);

        fetch(epysa_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                if (!append) {
                    resultsContainer.innerHTML = html;
                } else {
                    const oldTrigger = document.getElementById('next-page-trigger');
                    if (oldTrigger) oldTrigger.remove();
                    resultsContainer.insertAdjacentHTML('beforeend', html);
                }

                const nextTrigger = document.getElementById('next-page-trigger');
                if (loadMoreContainer) {
                    if (nextTrigger) {
                        loadMoreContainer.style.display = 'block';
                        currentPage = parseInt(nextTrigger.getAttribute('data-next'));
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Error:', err))
            .finally(() => {
                isLoading = false;
            });
    }

    // Event Listeners Filtros
    if (form) {
        // Búsqueda
        const inputBusqueda = form.querySelector('input[name="busqueda"]');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    cargarHistorias(1, false);
                }, 500);
            });
        }

        // Selects
        form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => {
                currentPage = 1;
                cargarHistorias(1, false);
            });
        });
    }

    // Cargar más
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', (e) => {
            e.preventDefault();
            cargarHistorias(currentPage, true);
        });
    }

    // Carga inicial
    if (form && resultsContainer) {
        cargarHistorias();
    }

    /* =========================================
       2. DELEGACIÓN DE EVENTOS PARA EL MODAL
       (Como las tarjetas son AJAX, escuchamos en el padre)
       ========================================= */
    if (resultsContainer) {
        resultsContainer.addEventListener('click', function (e) {
            // Buscamos si el click fue en el botón "Leer historia"
            if (e.target.classList.contains('btn-link-read')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                cargarModalHistoria(id);
            }
        });
    }
});

/* =========================================
   3. FUNCIONES GLOBALES DEL MODAL
   (Fuera del DOMContentLoaded para que el HTML inyectado las encuentre)
   ========================================= */

function cargarModalHistoria(id) {
    const modalWrapper = document.getElementById('modal-historia-wrapper');
    const modalContainer = document.getElementById('modal-historia-container');

    if (!modalWrapper || !modalContainer) return;

    // Loader
    modalContainer.innerHTML = '<div style="padding:50px; text-align:center;"><div class="spinner-border text-danger"></div></div>';

    // Abrir Modal (Clase .is-open según referencia)
    modalWrapper.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    const formData = new FormData();
    formData.append('action', 'cargar_historia_modal');
    formData.append('post_id', id);

    fetch(epysa_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                modalContainer.innerHTML = response.data;
                initModalSwiper();
            } else {
                modalContainer.innerHTML = '<p class="text-center p-5">Error al cargar.</p>';
            }
        })
        .catch(err => console.error(err));
}

function initModalSwiper() {
    const swiperEl = document.querySelector('.modal-swiper.is-slider');
    if (!swiperEl) return;

    modalSwiper = new Swiper(swiperEl, {
        loop: false,
        slidesPerView: 1,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        on: {
            slideChange: function () {
                // Pausar videos al mover slide
                const videos = swiperEl.querySelectorAll('video');
                videos.forEach(v => v.pause());
            }
        }
    });
}

function cerrarModalHistoria() {
    const modalWrapper = document.getElementById('modal-historia-wrapper');
    const modalContainer = document.getElementById('modal-historia-container');

    if (modalWrapper) modalWrapper.classList.remove('is-open');
    document.body.style.overflow = '';

    if (modalSwiper) {
        modalSwiper.destroy();
        modalSwiper = null;
    }

    // Limpiar HTML para matar videos
    setTimeout(() => {
        if (modalContainer) modalContainer.innerHTML = '';
    }, 300); // Esperar transición
}

function abrirVotacion(id) {
    cerrarModalHistoria();
    // Lógica futura de votación
    console.log("Votar ID:", id);
}