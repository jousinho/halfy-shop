'use strict';

/* category-filter.js
 * Filtro de categorías con transición GSAP.
 * Actualmente la navegación es server-side (redirección a /categoria/{slug}).
 * Este script añade una transición de salida suave antes de navegar.
 */

document.addEventListener('DOMContentLoaded', () => {
    initCategoryFilter();
});

function initCategoryFilter() {
    const filterLinks = document.querySelectorAll('.filter-btn:not(.active)');
    const grid = document.getElementById('artworkGrid');

    if (!filterLinks.length || !grid) return;
    if (typeof gsap === 'undefined') return;

    filterLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = link.href;

            gsap.to(grid, {
                opacity: 0,
                y: -8,
                duration: 0.2,
                ease: 'power1.in',
                onComplete: () => { window.location.href = target; },
            });
        });
    });
}
