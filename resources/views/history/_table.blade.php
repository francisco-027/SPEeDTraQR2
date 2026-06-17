{{-- History results table + pagination. Rendered inside #history-results and
     returned on its own for live search/filter fetches. --}}
<div class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-md shadow-gray-200/50">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-100/90">
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">File Name</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tracking ID</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Category</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Sticker</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($documents as $doc)
                    @php
                        $index = ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration;
                    @endphp
                    <x-document-row
                        class="even:bg-gray-50/50"
                        :index="$index"
                        :date="$doc->created_at->format('M j, Y')"
                        :tracking="$doc->tracking_number"
                        :fileName="$doc->citizen_name ?: 'File '.substr($doc->tracking_number, -5)"
                        :category="$doc->document_type"
                        :status="$doc->status === 'completed' ? 'completed' : $doc->status"
                        :href="route('track.show', $doc->tracking_number)"
                        :sticker-href="route('documents.sticker', $doc)"
                    />
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-100 px-4 py-3">{{ $documents->links() }}</div>
</div>
