import './bootstrap';
import './echo';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Progressive-enhancement modal triggers.
// Any element with [data-modal-open="modal-name"] opens the matching
// <x-modal name="modal-name"> via the Breeze modal event API. Triggers are
// plain <a href> links, so with JS disabled they fall back to navigation.
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-modal-open]');
    if (!trigger) return;
    e.preventDefault();
    window.dispatchEvent(
        new CustomEvent('open-modal', { detail: trigger.getAttribute('data-modal-open') })
    );
});
