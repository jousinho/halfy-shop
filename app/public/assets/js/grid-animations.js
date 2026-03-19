'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
});

function initScrollAnimations() {
    const cards = document.querySelectorAll('.artwork-card');
    if (!cards.length) return;

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    cards.forEach((card, i) => {
        gsap.fromTo(card,
            { opacity: 0, y: 24 },
            {
                opacity: 1,
                y: 0,
                duration: 0.5,
                ease: 'power2.out',
                delay: (i % 3) * 0.07,
                scrollTrigger: {
                    trigger: card,
                    start: 'top 92%',
                    toggleActions: 'play none none none',
                },
            }
        );
    });
}
