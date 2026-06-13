<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 mb-0.5">
                    {{ Auth::user()->isAdmin() ? 'Admin' : 'Agent' }}
                </p>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    {{ Auth::user()->isAdmin() ? 'Leads Directory' : 'My Pipeline Leads' }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-7">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
                    <p class="font-semibold mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Search & Filter Bar ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
                <form action="{{ route('leads.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                    {{-- Search --}}
                    <div class="relative">
                        <svg class="absolute inset-y-0 left-3 my-auto w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads…"
                            class="pl-8 w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Status --}}
                    <select name="status" class="w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="new"       {{ request('status') === 'new'       ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost"      {{ request('status') === 'lost'      ? 'selected' : '' }}>Lost</option>
                    </select>

                    @if(Auth::user()->isAdmin())
                        {{-- Agent --}}
                        <select name="agent_id" class="w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Assignments</option>
                            <option value="unassigned" {{ request('agent_id') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div></div>{{-- spacer to keep grid alignment --}}
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition shadow-sm">
                            Apply Filters
                        </button>
                        <a href="{{ route('leads.index') }}"
                            class="flex items-center justify-center px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition">
                            Reset
                        </a>
                    </div>

                </form>
            </div>

            {{-- ── Leads Table ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                x-data="{ editingLeadId: null, updatingStatusId: null, selectedLeads: [], allLeadIds: {{ json_encode($leads->pluck('id')->map(fn($id) => (string)$id)->toArray()) }} }">

                {{-- Table Header --}}
                <div class="px-6 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <h3 class="font-semibold text-sm text-gray-800">Leads Directory</h3>
                        
                        @if(Auth::user()->isAdmin())
                            <div x-show="selectedLeads.length > 0" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="flex items-center gap-2 bg-rose-50 border border-rose-100 px-3 py-1 rounded-xl shadow-sm">
                                <span class="text-[11px] font-bold text-rose-700">
                                    <span x-text="selectedLeads.length"></span> selected
                                </span>
                                <form action="{{ route('leads.bulk-delete') }}" method="POST"
                                      @submit.prevent="if (confirm('Delete ' + selectedLeads.length + ' selected leads?')) $el.submit()"
                                      class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="ids" :value="selectedLeads.join(',')">
                                    <button type="submit"
                                            class="px-2 py-0.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-[10px] transition shadow-sm">
                                        Delete Selected
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    <span class="text-xs text-gray-400">
                        Showing {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }}
                        of {{ $leads->total() }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-xs text-left">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                            <tr>
                                @if(Auth::user()->isAdmin())
                                    <th class="px-5 py-3 whitespace-nowrap w-10">
                                        <input type="checkbox"
                                               @change="selectedLeads = $el.checked ? [...allLeadIds] : []"
                                               :checked="selectedLeads.length === allLeadIds.length && allLeadIds.length > 0"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                @endif
                                <th class="px-5 py-3 whitespace-nowrap">Status</th>
                                <th class="px-5 py-3 whitespace-nowrap">Agent</th>
                                @foreach($visibleFields as $field)
                                    <th class="px-5 py-3 whitespace-nowrap">{{ $field->label }}</th>
                                @endforeach
                                <th class="px-5 py-3 whitespace-nowrap">Notes</th>
                                <th class="px-5 py-3 text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-50 text-gray-700">
                            @forelse($leads as $lead)

                                {{-- Main Row --}}
                                <tr class="hover:bg-slate-50/60 transition-colors duration-100 group">
                                    @if(Auth::user()->isAdmin())
                                        <td class="px-5 py-3.5 whitespace-nowrap w-10">
                                            <input type="checkbox" value="{{ $lead->id }}" x-model="selectedLeads"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                    @endif

                                    {{-- Status --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap relative">
                                        @if(Auth::user()->isAdmin())
                                            <button @click="updatingStatusId = (updatingStatusId === {{ $lead->id }} ? null : {{ $lead->id }})"
                                                class="focus:outline-none">
                                                @php
                                                    $badge = match($lead->status) {
                                                        'new'       => 'bg-sky-50 text-sky-700 border-sky-200',
                                                        'contacted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'converted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        default     => 'bg-rose-50 text-rose-700 border-rose-200',
                                                    };
                                                    $label = ucfirst($lead->status);
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $badge }}">
                                                    <span class="w-1.5 h-1.5 rounded-full
                                                        @if($lead->status === 'new') bg-sky-400
                                                        @elseif($lead->status === 'contacted') bg-amber-400
                                                        @elseif($lead->status === 'converted') bg-emerald-400
                                                        @else bg-rose-400 @endif">
                                                    </span>
                                                    {{ $label }}
                                                </span>
                                            </button>

                                            <div x-show="updatingStatusId === {{ $lead->id }}"
                                                x-cloak
                                                class="absolute left-4 top-full mt-1 bg-white border border-gray-200 shadow-xl rounded-xl p-1.5 z-20 min-w-[130px]"
                                                @click.away="updatingStatusId = null">
                                                <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    @foreach(['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'lost' => 'Lost'] as $val => $txt)
                                                        <button type="submit" name="status" value="{{ $val }}"
                                                            class="w-full text-left px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-50 hover:text-indigo-700 transition-colors {{ $lead->status === $val ? 'font-semibold text-indigo-600' : 'text-gray-700' }}">
                                                            {{ $txt }}
                                                        </button>
                                                    @endforeach
                                                </form>
                                            </div>

                                        @else
                                            <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" onchange="this.form.submit()"
                                                    class="text-[11px] font-semibold py-0.5 pl-2 pr-7 border rounded-full bg-white text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer border-gray-200">
                                                    <option value="new"       {{ $lead->status === 'new'       ? 'selected' : '' }}>New</option>
                                                    <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                    <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                                    <option value="lost"      {{ $lead->status === 'lost'      ? 'selected' : '' }}>Lost</option>
                                                </select>
                                            </form>
                                        @endif
                                    </td>

                                    {{-- Agent --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if(Auth::user()->isAdmin())
                                            <form action="{{ route('leads.assign', $lead->id) }}" method="POST"
                                                class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="assigned_to"
                                                    class="text-xs py-1 pl-2 pr-7 border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">Unassigned</option>
                                                    @foreach($agents as $agent)
                                                        <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>
                                                            {{ $agent->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                    title="Assign"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="font-medium text-gray-800">
                                                {{ $lead->agent ? $lead->agent->name : '—' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Dynamic Fields --}}
                                    @foreach($visibleFields as $field)
                                        <td class="px-5 py-3.5 max-w-[160px] truncate text-gray-600">
                                            @php
                                                $val = $lead->data[$field->key] ?? '';
                                                $isPhone = false;
                                                if (!empty($val)) {
                                                    $keyLower = strtolower($field->key);
                                                    $labelLower = strtolower($field->label);
                                                    
                                                    // 1. Keyword check in column key or label
                                                    $phoneKeywords = ['phone', 'mobile', 'tel', 'cell', 'contact', 'call', 'dial'];
                                                    foreach ($phoneKeywords as $kw) {
                                                        if (str_contains($keyLower, $kw) || str_contains($labelLower, $kw)) {
                                                            $isPhone = true;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // 2. Generic regex check (numbers, spaces, dashes, parentheses, plus, dots, length 7-22)
                                                    if (!$isPhone && preg_match('/^\+?[0-9\s\-\(\)\.]{7,22}$/', trim($val))) {
                                                        if (preg_match_all('/[0-9]/', $val) >= 5) {
                                                            $isPhone = true;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @if($isPhone)
                                                <div class="flex items-center gap-1.5">
                                                    <span>{{ $val }}</span>
                                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $val) }}" 
                                                       class="inline-flex items-center p-1 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 transition shadow-sm"
                                                       title="Call {{ $val }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" 
                                                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            @else
                                                {{ $val ?: '—' }}
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Notes --}}
                                    <td class="px-5 py-3.5 max-w-[200px] truncate italic text-gray-400 text-[11px]">
                                        {{ $lead->data['notes'] ?? 'No notes' }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button @click="editingLeadId = (editingLeadId === {{ $lead->id }} ? null : {{ $lead->id }})"
                                                class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold text-[11px] transition">
                                                Edit
                                            </button>
                                            @if(Auth::user()->isAdmin())
                                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="inline-block"
                                                    onsubmit="return confirm('Delete this lead?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold text-[11px] transition">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Edit Row --}}
                                <tr x-show="editingLeadId === {{ $lead->id }}" x-cloak>
                                    <td colspan="{{ count($visibleFields) + 4 + (Auth::user()->isAdmin() ? 1 : 0) }}"
                                        class="px-6 py-5 bg-indigo-50/30 border-t border-b border-indigo-100">
                                        <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">

                                                {{-- Notes --}}
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Notes & Comments</label>
                                                    <textarea name="notes" rows="3"
                                                        class="block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                                        placeholder="Update lead conversation details...">{{ $lead->data['notes'] ?? '' }}</textarea>
                                                </div>

                                                @if(Auth::user()->isAdmin())
                                                    <div class="grid grid-cols-2 gap-2">
                                                        @foreach($visibleFields as $field)
                                                            <div>
                                                                <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">{{ $field->label }}</label>
                                                                <input type="text" name="field_{{ $field->key }}"
                                                                    value="{{ $lead->data[$field->key] ?? '' }}"
                                                                    class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                                                        <select name="status"
                                                            class="block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="new"       {{ $lead->status === 'new'       ? 'selected' : '' }}>New</option>
                                                            <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                            <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                                            <option value="lost"      {{ $lead->status === 'lost'      ? 'selected' : '' }}>Lost</option>
                                                        </select>
                                                    </div>
                                                @endif

                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="editingLeadId = null"
                                                    class="px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="{{ count($visibleFields) + 4 + (Auth::user()->isAdmin() ? 1 : 0) }}" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-300">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-sm text-gray-400">No leads found.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $leads->links() }}
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
