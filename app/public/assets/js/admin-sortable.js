'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initAdminSortable();
});

function initAdminSortable() {
    const list = document.getElementById('sortableArtworks');
    if (!list || typeof Sortable === 'undefined') return;

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: () => { saveOrder(list); },
    });
}

function saveOrder(list) {
    const ids = Array.from(list.querySelectorAll('[data-id]'))
        .map(el => el.dataset.id);

    fetch('/admin/artworks/reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids }),
    })
    .then(res => {
        if (!res.ok) throw new Error('Error al guardar el orden');
    })
    .catch(err => console.error(err));
}
