<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        @include('documents._created-card', ['document' => $document])

        <div class="mt-4 text-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                Back to Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
