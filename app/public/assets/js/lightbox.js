'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initLightbox();
});

function initLightbox() {
    if (typeof GLightbox === 'undefined') return;

    GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false,
        skin: 'clean',
        descPosition: 'right',
        width: '90vw',
        height: 'auto',
    });
}
