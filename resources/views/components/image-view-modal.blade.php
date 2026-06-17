{{-- Full-screen image viewer with zoom + pan.
     Opens when any [data-image-view] link is clicked (those links keep their
     href + target=_blank as a no-JS fallback). Layers above other modals
     (z-[60]) and Esc closes only this viewer. --}}
<div id="image-viewer"
     class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/85 backdrop-blur-sm"
     aria-modal="true" role="dialog">

    {{-- Toolbar --}}
    <div class="absolute left-1/2 top-4 z-10 flex -translate-x-1/2 items-center gap-2 rounded-full bg-black/60 px-3 py-1.5 text-white shadow-lg ring-1 ring-white/10">
        <button type="button" data-iv-zoom-out class="flex h-8 w-8 items-center justify-center rounded-full transition hover:bg-white/15" aria-label="Zoom out">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14"/></svg>
        </button>
        <span data-iv-percent class="min-w-[3.5rem] text-center text-sm font-semibold tabular-nums">100%</span>
        <button type="button" data-iv-zoom-in class="flex h-8 w-8 items-center justify-center rounded-full transition hover:bg-white/15" aria-label="Zoom in">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        </button>
    </div>

    <button type="button" data-iv-close class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white shadow-lg ring-1 ring-white/10 transition hover:bg-black/80" aria-label="Close viewer">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <img data-iv-image src="" alt="Document attachment"
         class="max-h-[90vh] max-w-[92vw] cursor-grab select-none object-contain transition-transform duration-75 will-change-transform"
         draggable="false">
</div>

<script>
(function () {
    const viewer = document.getElementById('image-viewer');
    if (!viewer) return;

    const img = viewer.querySelector('[data-iv-image]');
    const percent = viewer.querySelector('[data-iv-percent]');
    const MIN = 1, MAX = 8;

    let scale = 1, tx = 0, ty = 0;
    let dragging = false, startX = 0, startY = 0;

    function apply() {
        img.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
        img.style.cursor = scale > 1 ? (dragging ? 'grabbing' : 'grab') : 'default';
        percent.textContent = Math.round(scale * 100) + '%';
    }

    function setScale(next) {
        scale = Math.min(MAX, Math.max(MIN, next));
        if (scale === 1) { tx = 0; ty = 0; }   // re-center at 100%
        apply();
    }

    function open(src) {
        img.src = src;
        scale = 1; tx = 0; ty = 0;
        apply();
        viewer.classList.remove('hidden');
        viewer.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function close() {
        viewer.classList.add('hidden');
        viewer.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        img.src = '';
    }

    function isOpen() { return !viewer.classList.contains('hidden'); }

    // Open from any [data-image-view] link (capture so it beats the default nav).
    document.addEventListener('click', (e) => {
        const link = e.target.closest('[data-image-view]');
        if (!link) return;
        e.preventDefault();
        open(link.getAttribute('href'));
    });

    viewer.querySelector('[data-iv-close]').addEventListener('click', close);
    viewer.querySelector('[data-iv-zoom-in]').addEventListener('click', () => setScale(scale + 0.5));
    viewer.querySelector('[data-iv-zoom-out]').addEventListener('click', () => setScale(scale - 0.5));

    // Click on the backdrop (not the image/toolbar) closes.
    viewer.addEventListener('click', (e) => { if (e.target === viewer) close(); });

    // Wheel zoom, centred enough for normal use.
    viewer.addEventListener('wheel', (e) => {
        if (!isOpen()) return;
        e.preventDefault();
        setScale(scale + (e.deltaY < 0 ? 0.25 : -0.25));
    }, { passive: false });

    // Drag-to-pan while zoomed.
    img.addEventListener('mousedown', (e) => {
        if (scale <= 1) return;
        dragging = true; startX = e.clientX - tx; startY = e.clientY - ty;
        apply(); e.preventDefault();
    });
    window.addEventListener('mousemove', (e) => {
        if (!dragging) return;
        tx = e.clientX - startX; ty = e.clientY - startY; apply();
    });
    window.addEventListener('mouseup', () => { if (dragging) { dragging = false; apply(); } });

    // Esc closes ONLY the viewer — capture phase + stopImmediatePropagation so it
    // runs before (and blocks) any underlying modal's Esc handler.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen()) {
            e.stopImmediatePropagation();
            e.preventDefault();
            close();
        }
    }, true);
})();
</script>
