{{-- Shared Add Department form fields, wrapped in a <form> by both the
     standalone create page (no-JS fallback) and the Add Department modal. --}}
@csrf
<input type="hidden" name="_form" value="add-department">

<div>
    <label class="block text-sm font-semibold text-gray-700">Department Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name') }}" required
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('name') border-red-400 @enderror"
           placeholder="e.g. Records Office">
    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-semibold text-gray-700">Short Code</label>
    <input type="text" name="code" value="{{ old('code') }}" maxlength="20"
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('code') border-red-400 @enderror"
           placeholder="e.g. REC">
    <p class="mt-1 text-xs text-gray-400">Optional. Used as a short identifier in reports.</p>
    @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-semibold text-gray-700">Alert Email</label>
    <input type="email" name="email" value="{{ old('email') }}"
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('email') border-red-400 @enderror"
           placeholder="department@office.gov">
    <p class="mt-1 text-xs text-gray-400">Receives SLA warning and breach notifications.</p>
    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-semibold text-gray-700">SLA Hours <span class="text-red-500">*</span></label>
    <input type="number" name="sla_hours" value="{{ old('sla_hours', 48) }}" required min="1" max="8760"
           class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm shadow-sm transition focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 @error('sla_hours') border-red-400 @enderror">
    <p class="mt-1 text-xs text-gray-400">Maximum hours a document may stay in this department before an alert is sent.</p>
    @error('sla_hours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
