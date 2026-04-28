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
        moreLength: 0,
        width: '90vw',
        height: 'auto',
        afterSlideLoad: () => {
            preventSwipeCloseOnDesc();
        },
    });
}

function preventSwipeCloseOnDesc() {
    const descEl = document.querySelector('.gslide-description');
    if (!descEl || descEl.dataset.swipeLocked) return;
    descEl.dataset.swipeLocked = '1';

    const stop = (e) => e.stopPropagation();
    descEl.addEventListener('touchstart', stop, { passive: true });
    descEl.addEventListener('touchmove',  stop, { passive: true });
}
