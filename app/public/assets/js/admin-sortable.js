'use strict';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sortable-list').forEach(list => {
        initSortable(list);
    });
});

function initSortable(list) {
    if (typeof Sortable === 'undefined') return;

    const url = list.dataset.reorderUrl;
    if (!url) return;

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: () => saveOrder(list, url),
    });
}

function saveOrder(list, url) {
    const ids = Array.from(list.querySelectorAll('[data-id]'))
        .map(el => el.dataset.id);

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids }),
    })
    .then(res => {
        if (!res.ok) throw new Error('Error al guardar el orden');
    })
    .catch(err => console.error(err));
}
