{{-- Shared Add User form fields. Wrapped in a <form> by both the standalone
     create page (no-JS fallback) and the Add User modal on the index page.
     Expects: $roles, $departments, $deptLocked --}}
@csrf
<input type="hidden" name="_form" value="add-user">

<div>
    <label class="block text-sm font-semibold text-gray-700">Full Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name') }}" required
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('name') border-red-400 @enderror">
    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-semibold text-gray-700">Email Address <span class="text-red-500">*</span></label>
    <input type="email" name="email" value="{{ old('email') }}" required
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('email') border-red-400 @enderror">
    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
        <input type="password" name="password" required autocomplete="new-password"
               class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('password') border-red-400 @enderror">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
        <input type="password" name="password_confirmation" required autocomplete="new-password"
               class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-semibold text-gray-700">Role <span class="text-red-500">*</span></label>
        <select name="role" required
                class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:outline-none @error('role') border-red-400 @enderror">
            <option value="">Select a role…</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                </option>
            @endforeach
        </select>
        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700">Department</label>
        @if($deptLocked)
            <input type="text" value="{{ $departments->first()?->name ?? '—' }}" disabled
                   class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 shadow-sm cursor-not-allowed">
            <input type="hidden" name="department_id" value="{{ $departments->first()?->id }}">
            <p class="mt-1 text-xs text-gray-400">Assigned to your department automatically.</p>
        @else
            <select name="department_id"
                    class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:outline-none">
                <option value="">None</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>
</div>
