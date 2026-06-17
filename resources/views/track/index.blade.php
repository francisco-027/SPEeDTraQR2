<x-app-layout>
    <div class="mx-auto max-w-lg py-6">
        <div class="rounded-2xl border border-gray-200/90 bg-white p-8 shadow-lg shadow-gray-200/60 ring-1 ring-gray-100">
            <div class="mb-5 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4l13 8-13 8V4z"/></svg>
                </div>
                <p class="font-semibold text-emerald-950">Nothing in progress right now</p>
                <p class="mt-1 text-sm text-gray-500">No documents are currently moving through your department.</p>
            </div>
            <p class="text-center text-gray-600">Look up any document (including completed) by tracking number:</p>
            <form method="GET" action="{{ route('track.index') }}" class="mt-6 space-y-4">
                <input name="tracking_number" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-lg shadow-sm transition focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" placeholder="SPD-YYYYMMDD-XXXXXX">
                <button type="submit" class="w-full rounded-xl bg-emerald-800 py-3 text-lg font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Search
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
