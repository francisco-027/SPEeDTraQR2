<x-app-layout>
    @php $filtersActive = request()->hasAny(['document_type', 'status', 'from', 'to']); @endphp

    <div class="mx-auto max-w-7xl space-y-4" x-data="{ filters: {{ $filtersActive ? 'true' : 'false' }} }">

        <form method="GET" id="history-filter" class="space-y-3">
            {{-- Toolbar: search + Filters toggle + Export CSV, all in one row --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[200px] flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
                    <input name="search" id="history-search" value="{{ request('search') }}" placeholder="Search tracking #, citizen, or category…" autocomplete="off"
                           class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <button type="button" x-on:click="filters = !filters"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        :class="filters ? 'ring-2 ring-emerald-500/30 border-emerald-300' : ''">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filters
                </button>

                <a href="{{ route('history.export', request()->query()) }}" id="history-export"
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Export CSV
                </a>
            </div>

            {{-- Collapsible filter panel (auto-open when a filter is active) --}}
            <div x-show="filters" x-cloak class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-200/90 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
                <select name="document_type" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All category</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type }}" @selected(request('document_type')===$type)>{{ $type }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <button type="submit" class="rounded-xl bg-emerald-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-900">Apply</button>
            </div>
        </form>

        <div id="history-results">
            @include('history._table')
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('history-filter');
            const search = document.getElementById('history-search');
            const results = document.getElementById('history-results');
            const exportLink = document.getElementById('history-export');
            if (!form || !results) return;

            const exportBase = "{{ route('history.export') }}";
            let timer = null;

            function currentParams() {
                const params = new URLSearchParams(new FormData(form));
                // Drop empty values to keep the URL tidy.
                [...params.entries()].forEach(([k, v]) => { if (!v) params.delete(k); });
                return params;
            }

            async function refresh() {
                const params = currentParams();
                const qs = params.toString();
                history.replaceState(null, '', qs ? `?${qs}` : location.pathname);
                exportLink.href = qs ? `${exportBase}?${qs}` : exportBase;

                params.set('partial', '1');
                try {
                    const res = await fetch(`{{ route('history') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (res.ok) results.innerHTML = await res.text();
                } catch (e) { /* keep current results on network error */ }
            }

            form.addEventListener('submit', (e) => { e.preventDefault(); refresh(); });
            search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(refresh, 300); });
            form.querySelectorAll('select, input[type="date"]').forEach(el => el.addEventListener('change', refresh));

            // Paginate via fetch too (keep filters + scroll position sane).
            results.addEventListener('click', async (e) => {
                const link = e.target.closest('a.page-link, .pagination a, nav a');
                if (!link || !link.href) return;
                e.preventDefault();
                try {
                    const url = new URL(link.href);
                    url.searchParams.set('partial', '1');
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (res.ok) {
                        results.innerHTML = await res.text();
                        const display = new URL(link.href);
                        display.searchParams.delete('partial');
                        history.replaceState(null, '', display.search || location.pathname);
                    }
                } catch (e) { /* ignore */ }
            });
        })();
    </script>
</x-app-layout>
