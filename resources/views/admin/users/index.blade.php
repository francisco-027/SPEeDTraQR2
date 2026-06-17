<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filters (plain submit works without JS; JS upgrades to live search) --}}
        <form method="GET" id="users-filter" class="flex flex-wrap gap-3">
            <input type="text" name="search" id="users-search" value="{{ request('search') }}"
                   placeholder="Search name or email…" autocomplete="off"
                   class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 min-w-[200px]">
            <select name="role" id="users-role" class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-emerald-400 focus:outline-none">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-900">
                Filter
            </button>
            @if(request()->hasAny(['search','role']))
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </form>

        <div id="users-results">
            @include('admin.users._table')
        </div>
    </div>

    {{-- Add User modal (navbar button opens it; create page is the no-JS fallback) --}}
    <x-modal name="add-user-modal" :show="$errors->any() && old('_form') === 'add-user'" focusable maxWidth="2xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">New User Account</h2>
                <button type="button" x-on:click="$dispatch('close')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @include('admin.users._form')

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" x-on:click="$dispatch('close')"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                    Create User
                </button>
            </div>
        </form>
    </x-modal>

    <script>
        (function () {
            const form = document.getElementById('users-filter');
            const search = document.getElementById('users-search');
            const role = document.getElementById('users-role');
            const results = document.getElementById('users-results');
            if (!form || !results) return;

            let timer = null;

            async function refresh() {
                const params = new URLSearchParams();
                if (search.value) params.set('search', search.value);
                if (role.value) params.set('role', role.value);

                // Reflect the current filters in the URL (no reload).
                const qs = params.toString();
                history.replaceState(null, '', qs ? `?${qs}` : location.pathname);

                params.set('partial', '1');
                try {
                    const res = await fetch(`{{ route('admin.users.index') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (res.ok) results.innerHTML = await res.text();
                } catch (e) { /* keep current results on network error */ }
            }

            form.addEventListener('submit', (e) => { e.preventDefault(); refresh(); });
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(refresh, 300);
            });
            role.addEventListener('change', refresh);
        })();
    </script>
</x-app-layout>
