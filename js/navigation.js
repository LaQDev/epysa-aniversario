/* js/navigation.js (Versión con Diagnóstico) */
document.addEventListener('DOMContentLoaded', function () {
    console.log('JS de Navegación cargado correctamente ✅'); // Mensaje 1

    const menuToggle = document.querySelector('.menu-toggle');
    const siteNav = document.querySelector('.main-navigation');

    if (menuToggle && siteNav) {
        menuToggle.addEventListener('click', function (e) {
            e.preventDefault(); // Prevenir cualquier comportamiento extraño
            console.log('¡Clic detectado en el botón! 🖱️'); // Mensaje 2

            // 1. Alternar la clase
            siteNav.classList.toggle('toggled');
            console.log('Clase "toggled" alternada. Estado actual: ', siteNav.classList.contains('toggled')); // Mensaje 3

            // 2. Accesibilidad
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
        });
    } else {
        console.error('Error Crítico: No se encontró el botón o el menú ❌');
        console.log('Botón:', menuToggle);
        console.log('Menú:', siteNav);
    }
});

/* Lógica Dropdown Usuario (Etapa 2) */
document.addEventListener('DOMContentLoaded', function() {
    const userTrigger = document.getElementById('userDropdownTrigger');
    const userMenu = document.querySelector('.user-dropdown-menu');
    
    if (userTrigger && userMenu) {
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation(); // Evita que se cierre al instante
            this.classList.toggle('active');
            userMenu.classList.toggle('is-visible');
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!userTrigger.contains(e.target) && !userMenu.contains(e.target)) {
                userTrigger.classList.remove('active');
                userMenu.classList.remove('is-visible');
            }
        });
    }
});