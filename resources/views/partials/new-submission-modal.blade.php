{{-- New Submission modal. Included by layouts.app for users who can create
     documents. Opens from the navbar "New document" button and the dashboard
     empty-state button (both [data-modal-open="new-submission-modal"]).
     Fed by the layouts.app view composer: $docFormDepartments,
     $docFormDefaultRoutes, $docFormCategories.

     No-JS: renders open server-side when the open flash / validation errors are
     present, and the form submits as a normal full-page POST. With JS: opens via
     Alpine, submits via fetch, and swaps in the "Document Created" card. --}}
@php
    $nsOpen = session('open_new_submission') || ($errors->any() && old('_form') === 'new-submission');
@endphp

<div x-data="{ show: {{ $nsOpen ? 'true' : 'false' }} }"
     x-show="show"
     style="display: {{ $nsOpen ? 'flex' : 'none' }};"
     x-on:open-modal.window="$event.detail === 'new-submission-modal' ? show = true : null"
     x-on:close-modal.window="$event.detail === 'new-submission-modal' ? show = false : null"
     x-on:keydown.escape.window="show = false"
     class="fixed inset-0 z-50 items-center justify-center p-4">

    <div x-show="show" x-on:click="show = false" data-ns-close class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <div x-show="show"
         class="relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">

        {{-- Pinned header --}}
        <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-xl font-bold tracking-tight text-emerald-950">New Submission</h2>
            <button type="button" x-on:click="show = false" data-ns-close class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body: whole thing scrolls on mobile; only the right column scrolls on desktop --}}
        <div class="min-h-0 flex-1 overflow-y-auto lg:overflow-hidden">
            <div id="nsFormWrap" class="grid h-full grid-cols-1 gap-6 p-6 lg:grid-cols-2">

                {{-- Left: drop zone (fills the column, stays put) --}}
                <div class="rounded-2xl bg-gray-100/80 p-4 lg:h-full lg:overflow-hidden">
                    <input type="file" id="nsAttachmentInput" name="attachments[]" form="newSubmissionForm" accept="image/*" multiple class="sr-only">
                    <div id="nsDropZone" role="button" tabindex="0" aria-label="Choose file or drop image here"
                         class="flex h-full min-h-[300px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-200/60 text-center transition hover:border-emerald-400 hover:bg-emerald-50/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                        <div id="nsDropZonePlaceholder" class="px-4">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 14v4a2 2 0 002 2h12a2 2 0 002-2v-4"/></svg>
                            <p class="text-lg font-semibold text-gray-600 sm:text-xl">Drop your file here</p>
                            <p class="mt-2 text-sm text-gray-500">or <span class="font-semibold text-emerald-800 underline">click to browse</span> — images only (multiple allowed)</p>
                        </div>
                        <div id="nsDropZonePreview" class="hidden w-full flex-col gap-3 p-4">
                            <p id="nsPreviewCount" class="text-sm font-semibold text-gray-800"></p>
                            <div id="nsPreviewGrid" class="grid max-h-[260px] w-full grid-cols-2 gap-2 overflow-y-auto sm:grid-cols-3"></div>
                            <button type="button" id="nsClearFileBtn" class="text-sm font-semibold text-red-600 underline hover:text-red-800">Remove all photos</button>
                        </div>
                    </div>
                </div>

                {{-- Right: scrollable form --}}
                <div id="nsRouteBuilder" class="lg:h-full lg:overflow-y-auto lg:pr-1"
                     data-departments='@json($docFormDepartments->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])->values())'
                     data-default-routes='@json($docFormDefaultRoutes)'
                     data-old-route='@json(collect(old('route_departments', []))->map(fn ($id) => (int) $id)->values())'
                     data-old-type='@json(old('document_type', ''))'>

                    <div id="nsErrorSummary" class="@if($errors->any() && old('_form')==='new-submission') mb-4 @else hidden @endif rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @if($errors->any() && old('_form')==='new-submission')
                            <ul class="list-disc space-y-1 pl-4">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        @endif
                    </div>

                    <form id="newSubmissionForm" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="new-submission">
                        <div id="nsRouteHiddenInputs"></div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">File Name</label>
                            <input name="remarks" value="{{ old('remarks') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" placeholder="File Name">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" placeholder="Description">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Category</label>
                            <select name="document_type" id="nsDocumentTypeSelect"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" required>
                                <option value="">Select Category</option>
                                @foreach($docFormCategories as $category)
                                    <option value="{{ $category }}" @selected(old('document_type') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/40 p-4">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <div>
                                    <label class="block text-sm font-semibold text-emerald-950">Routing Path</label>
                                    <p class="text-xs text-emerald-800/80">Set the departments this document will visit, in order.</p>
                                </div>
                                <span id="nsRouteStepCount" class="shrink-0 rounded-full bg-white px-2.5 py-0.5 text-xs font-bold text-emerald-800 ring-1 ring-emerald-200">0 steps</span>
                            </div>

                            <p id="nsRouteClientError" class="mb-2 hidden text-sm font-medium text-red-600"></p>

                            <div class="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                <select id="nsRouteDeptPicker" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                    <option value="">Select department…</option>
                                    @foreach($docFormDepartments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="nsRouteAddBtn" class="inline-flex w-full min-h-[42px] items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm ring-2 ring-emerald-600/30 transition hover:bg-emerald-800 active:scale-[0.98] sm:w-auto sm:min-w-[9.5rem]">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                    Add to path
                                </button>
                            </div>

                            <p id="nsRouteEmptyHint" class="rounded-lg border border-dashed border-emerald-300/80 bg-white/60 px-3 py-4 text-center text-sm text-gray-500">
                                No steps yet. Pick a category to load a suggested path, or use the dropdown and <strong>Add to path</strong> above.
                            </p>
                            <ul id="nsRouteStepsList" class="hidden space-y-2"></ul>
                            <p id="nsRouteSuggestedHint" class="mt-2 hidden text-xs text-gray-500">Suggested path loaded for this category — you can reorder or change it.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Citizen Name</label>
                            <input name="citizen_name" value="{{ old('citizen_name') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Citizen Contact</label>
                            <input name="citizen_contact" value="{{ old('citizen_contact') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" x-on:click="show = false" data-ns-close class="rounded-xl bg-gray-200 px-6 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-300">Cancel</button>
                            <button type="submit" id="nsSubmitBtn" class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-6 py-2.5 font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-70">
                                <svg id="nsSpinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                                <span id="nsSubmitLabel">Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Success card lifted here after a fetch submit --}}
            <div id="nsSuccess" class="hidden p-6"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('nsRouteBuilder');
    if (!root) return;

    /* ---- Routing path builder ---- */
    const departments = JSON.parse(root.dataset.departments || '[]');
    const defaultRoutes = JSON.parse(root.dataset.defaultRoutes || '{}');
    const oldRoute = JSON.parse(root.dataset.oldRoute || '[]');
    const oldType = root.dataset.oldType || '';

    const form = document.getElementById('newSubmissionForm');
    const typeSelect = document.getElementById('nsDocumentTypeSelect');
    const picker = document.getElementById('nsRouteDeptPicker');
    const addBtn = document.getElementById('nsRouteAddBtn');
    const listEl = document.getElementById('nsRouteStepsList');
    const emptyHint = document.getElementById('nsRouteEmptyHint');
    const suggestedHint = document.getElementById('nsRouteSuggestedHint');
    const stepCount = document.getElementById('nsRouteStepCount');
    const hiddenContainer = document.getElementById('nsRouteHiddenInputs');
    const clientError = document.getElementById('nsRouteClientError');

    let steps = [];
    const findDept = (id) => departments.find(d => d.id === id);

    function setStepsFromIds(ids) { steps = ids.map(findDept).filter(Boolean); render(); }
    function syncHiddenInputs() {
        hiddenContainer.innerHTML = '';
        steps.forEach(step => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'route_departments[]'; input.value = String(step.id);
            hiddenContainer.appendChild(input);
        });
    }
    function updateCount() {
        const n = steps.length;
        stepCount.textContent = n + ' step' + (n === 1 ? '' : 's');
        emptyHint.classList.toggle('hidden', n > 0);
        listEl.classList.toggle('hidden', n === 0);
        const type = typeSelect?.value || '';
        const hasDefault = type && Array.isArray(defaultRoutes[type]) && defaultRoutes[type].length > 0;
        suggestedHint.classList.toggle('hidden', !hasDefault || n === 0);
    }
    function render() {
        listEl.innerHTML = '';
        steps.forEach((step, index) => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 rounded-xl border border-white bg-white px-3 py-2 shadow-sm';
            li.innerHTML = `
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">${index + 1}</span>
                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800"></span>
                <div class="flex shrink-0 gap-1">
                    <button type="button" data-action="up" data-index="${index}" class="rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" ${index === 0 ? 'disabled' : ''}>↑</button>
                    <button type="button" data-action="down" data-index="${index}" class="rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-40" ${index === steps.length - 1 ? 'disabled' : ''}>↓</button>
                    <button type="button" data-action="remove" data-index="${index}" class="rounded-lg border border-red-200 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Remove</button>
                </div>`;
            li.querySelector('span:nth-child(2)').textContent = step.name;
            listEl.appendChild(li);
        });
        syncHiddenInputs();
        updateCount();
    }
    function addStep() {
        const id = parseInt(picker.value, 10);
        if (!id) return;
        if (steps.some(s => s.id === id)) { alert('That department is already in the route.'); return; }
        const dept = findDept(id);
        if (dept) { steps.push(dept); render(); }
        picker.value = '';
    }
    function applyDefaultRoute() {
        const ids = defaultRoutes[typeSelect?.value || ''] || [];
        if (ids.length) setStepsFromIds(ids);
    }
    addBtn?.addEventListener('click', addStep);
    picker?.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addStep(); } });
    listEl?.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const index = parseInt(btn.dataset.index, 10);
        const action = btn.dataset.action;
        if (action === 'remove') { steps.splice(index, 1); render(); }
        else if (action === 'up' && index > 0) { const i = steps.splice(index, 1)[0]; steps.splice(index - 1, 0, i); render(); }
        else if (action === 'down' && index < steps.length - 1) { const i = steps.splice(index, 1)[0]; steps.splice(index + 1, 0, i); render(); }
    });
    typeSelect?.addEventListener('change', () => {
        if (steps.length === 0) applyDefaultRoute();
        else if (confirm('Replace the current routing path with the suggested path for this category?')) applyDefaultRoute();
    });

    /* ---- Attachments (drop zone + preview) ---- */
    const input = document.getElementById('nsAttachmentInput');
    const zone = document.getElementById('nsDropZone');
    const placeholder = document.getElementById('nsDropZonePlaceholder');
    const previewWrap = document.getElementById('nsDropZonePreview');
    const previewGrid = document.getElementById('nsPreviewGrid');
    const previewCount = document.getElementById('nsPreviewCount');
    const clearBtn = document.getElementById('nsClearFileBtn');
    const MAX_FILES = 10;
    let selectedFiles = [];
    const objectUrls = new Map();
    const isImage = (f) => f && f.type.startsWith('image/');
    function syncInputFiles() { const dt = new DataTransfer(); selectedFiles.forEach(f => dt.items.add(f)); input.files = dt.files; }
    function addFiles(fileList) {
        const incoming = Array.from(fileList || []).filter(isImage);
        if (!incoming.length) { alert('Please choose image files only (PNG, JPG, etc.).'); return; }
        incoming.forEach(file => {
            if (selectedFiles.length >= MAX_FILES) return;
            if (selectedFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) return;
            selectedFiles.push(file);
        });
        syncInputFiles(); renderPreview();
    }
    function renderPreview() {
        previewGrid.innerHTML = '';
        objectUrls.forEach(url => URL.revokeObjectURL(url)); objectUrls.clear();
        if (!selectedFiles.length) {
            previewWrap.classList.add('hidden'); previewWrap.classList.remove('flex');
            placeholder.classList.remove('hidden'); return;
        }
        placeholder.classList.add('hidden');
        previewWrap.classList.remove('hidden'); previewWrap.classList.add('flex');
        previewCount.textContent = selectedFiles.length + ' photo' + (selectedFiles.length === 1 ? '' : 's') + ' selected';
        selectedFiles.forEach((file, index) => {
            const url = URL.createObjectURL(file); objectUrls.set(index, url);
            const wrap = document.createElement('div');
            wrap.className = 'relative overflow-hidden rounded-lg ring-1 ring-gray-200';
            wrap.innerHTML = `<img src="${url}" alt="" class="h-24 w-full object-cover bg-gray-100">
                <button type="button" data-index="${index}" class="remove-preview absolute right-1 top-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] font-bold text-white hover:bg-black/80">×</button>
                <p class="truncate px-1 py-0.5 text-[10px] text-gray-600">${file.name}</p>`;
            previewGrid.appendChild(wrap);
        });
    }
    function clearFiles() { selectedFiles = []; syncInputFiles(); renderPreview(); }
    previewGrid?.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-preview'); if (!btn) return;
        e.stopPropagation(); selectedFiles.splice(parseInt(btn.dataset.index, 10), 1); syncInputFiles(); renderPreview();
    });
    zone.addEventListener('click', (e) => { if (e.target.closest('#nsClearFileBtn') || e.target.closest('.remove-preview')) return; input.click(); });
    zone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    clearBtn?.addEventListener('click', (e) => { e.stopPropagation(); clearFiles(); });
    input.addEventListener('change', function () { if (this.files && this.files.length) addFiles(this.files); else clearFiles(); });
    ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); zone.classList.add('border-emerald-500', 'bg-emerald-100/50'); }));
    zone.addEventListener('dragleave', (e) => { e.preventDefault(); zone.classList.remove('border-emerald-500', 'bg-emerald-100/50'); });
    zone.addEventListener('drop', (e) => { e.preventDefault(); e.stopPropagation(); zone.classList.remove('border-emerald-500', 'bg-emerald-100/50'); addFiles(e.dataTransfer.files); });

    /* ---- Fetch submit + success swap ---- */
    const submitBtn = document.getElementById('nsSubmitBtn');
    const submitLabel = document.getElementById('nsSubmitLabel');
    const spinner = document.getElementById('nsSpinner');
    const errorSummary = document.getElementById('nsErrorSummary');
    const formWrap = document.getElementById('nsFormWrap');
    const success = document.getElementById('nsSuccess');
    let didSucceed = false;

    function setLoading(on) {
        submitBtn.disabled = on;
        spinner.classList.toggle('hidden', !on);
        submitLabel.textContent = on ? 'Submitting…' : 'Submit';
    }
    function showErrors(messages) {
        errorSummary.innerHTML = '<ul class="list-disc space-y-1 pl-4">' + messages.map(m => `<li>${m}</li>`).join('') + '</ul>';
        errorSummary.classList.remove('hidden');
        errorSummary.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    form.addEventListener('submit', async function (e) {
        syncHiddenInputs();
        if (steps.length < 1) {
            e.preventDefault();
            clientError.textContent = 'Add at least one department to the routing path.';
            clientError.classList.remove('hidden');
            return;
        }
        // Progressive enhancement: intercept with fetch.
        e.preventDefault();
        errorSummary.classList.add('hidden');
        clientError.classList.add('hidden');
        setLoading(true);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (res.status === 422) {
                const data = await res.json();
                showErrors(Object.values(data.errors || {}).flat());
                setLoading(false);
                return;
            }
            if (!res.ok) throw new Error('Request failed');

            const data = await res.json();
            // Lift the "Document Created" card out of the created page.
            const page = await (await fetch(data.redirect, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).text();
            const card = new DOMParser().parseFromString(page, 'text/html').querySelector('[data-created-card]');
            if (card) {
                formWrap.classList.add('hidden');
                success.innerHTML = '';
                success.appendChild(card);
                const closeWrap = document.createElement('div');
                closeWrap.className = 'mt-4 text-center';
                closeWrap.innerHTML = '<button type="button" id="nsSuccessClose" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Close</button>';
                success.appendChild(closeWrap);
                success.classList.remove('hidden');
                didSucceed = true;
                document.getElementById('nsSuccessClose').addEventListener('click', () => location.reload());
            } else {
                window.location = data.redirect;
            }
        } catch (err) {
            showErrors(['Something went wrong submitting the document. Please try again.']);
            setLoading(false);
        }
    });

    // Reload on close after a successful submit so dashboard counters refresh.
    // Covers every close affordance: ✕, Cancel, backdrop (data-ns-close) and Esc.
    document.querySelectorAll('[data-ns-close]').forEach(el =>
        el.addEventListener('click', () => { if (didSucceed) location.reload(); })
    );
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape' && didSucceed) location.reload(); });

    // Initialise from old input (validation round-trip) or suggested path.
    if (oldRoute.length) setStepsFromIds(oldRoute);
    else if (oldType) applyDefaultRoute();
    else render();
})();
</script>
