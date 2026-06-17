{{-- Users results table + pagination. Rendered inside #users-results on the
     index page and returned on its own for live search/filter fetches. --}}
<div class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Department</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($users as $user)
                    @php $archived = $user->trashed(); @endphp
                    <tr class="hover:bg-gray-50/60 transition {{ $archived ? 'bg-amber-50/40' : (! $user->is_active ? 'opacity-60' : '') }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </span>
                                <span class="text-sm font-semibold text-gray-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->department->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($archived)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Archived
                                </span>
                            @elseif($user->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($archived)
                                    <form method="POST" action="{{ route('admin.users.restore', $user) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">Restore</button>
                                    </form>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.force-delete', $user) }}"
                                              onsubmit="return confirm('Permanently delete {{ $user->name }}? This cannot be undone.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">Delete</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Edit</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                                                        {{ $user->is_active
                                                            ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.archive', $user) }}"
                                              onsubmit="return confirm('Archive {{ $user->name }}? They will not be able to sign in until restored.');">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">Archive</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
