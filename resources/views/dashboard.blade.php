<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 mb-0.5">
                    {{ Auth::user()->isAdmin() ? 'Admin' : 'Agent' }}
                </p>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    {{ Auth::user()->isAdmin() ? 'Lead Management' : 'My Pipeline' }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('agents.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0zM3 8a4 4 0 118 0"/>
                        </svg>
                        Manage Agents
                    </a>
                @endif
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

            {{-- ── Stats Row ── --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $statConfig = [
                        ['label' => 'Total',     'key' => 'total',     'dot' => 'bg-indigo-500'],
                        ['label' => 'New',        'key' => 'new',       'dot' => 'bg-sky-400'],
                        ['label' => 'Contacted',  'key' => 'contacted', 'dot' => 'bg-amber-400'],
                        ['label' => 'Converted',  'key' => 'converted', 'dot' => 'bg-emerald-400'],
                        ['label' => 'Lost',       'key' => 'lost',      'dot' => 'bg-rose-400'],
                    ];
                @endphp
                @foreach($statConfig as $s)
                    <div class="bg-white rounded-2xl border border-gray-100 px-5 py-4 flex flex-col gap-1 shadow-sm hover:-translate-y-0.5 transition-transform duration-200">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $s['dot'] }}"></span>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $s['label'] }}</span>
                        </div>
                        <span class="text-3xl font-bold text-gray-900 tabular-nums">{{ $stats[$s['key']] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- ── Admin Tools ── --}}
            @if(Auth::user()->isAdmin())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5" x-data="{}">

                    {{-- CSV Upload --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4" style="height: 360px;">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-indigo-50 shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm text-gray-900">Import CSV</h3>
                                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Auto-maps columns and ingests raw lead records.</p>
                            </div>
                        </div>
                        <form action="{{ route('leads.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 flex-1 justify-between min-h-0">
                            @csrf
                            
                            <!-- Upload Icon Placeholder Box -->
                            <label for="csv_file" class="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-indigo-100 rounded-2xl bg-indigo-50/10 py-6 gap-2 cursor-pointer hover:bg-indigo-50/30 hover:border-indigo-300 transition duration-150">
                                <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wider">CSV Ingestion Format</span>
                            </label>

                            <label class="block">
                                <span class="sr-only">Choose CSV file</span>
                                <input type="file" id="csv_file" name="csv_file" accept=".csv" required
                                    class="block w-full text-xs text-gray-500
                                           file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                           file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                           hover:file:bg-indigo-100 cursor-pointer focus:outline-none focus:ring-0"/>
                            </label>
                            <button type="submit"
                                class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition shadow-sm mt-auto">
                                Import Leads
                            </button>
                        </form>
                    </div>

                    {{-- Column Visibility --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4" style="height: 360px;">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-slate-50 shrink-0">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm text-gray-900">Table Columns</h3>
                                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Choose which fields appear in the leads table.</p>
                            </div>
                        </div>
                        <form action="{{ route('leads.fields.visibility') }}" method="POST" class="flex flex-col gap-3 flex-1 min-h-0 justify-between">
                            @csrf
                            <div class="overflow-y-auto flex-1 px-1 py-1 space-y-1.5 min-h-0">
                                @forelse($allFields as $field)
                                    <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer group">
                                        <input type="checkbox" name="visible_fields[]" value="{{ $field->key }}"
                                            {{ $field->is_visible ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-1">
                                        <span class="font-medium group-hover:text-indigo-600 transition-colors">{{ $field->label }}</span>
                                    </label>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Upload a CSV to populate fields.</span>
                                @endforelse
                            </div>
                            @if($allFields->count() > 0)
                                <button type="submit"
                                    class="w-full py-2 rounded-xl bg-gray-900 hover:bg-gray-700 text-white text-xs font-semibold transition shadow-sm mt-auto">
                                    Save Column Preferences
                                </button>
                            @endif
                        </form>
                    </div>

                    {{-- Add Lead --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4" style="height: 360px;">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-emerald-50 shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm text-gray-900">Add Lead</h3>
                                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Manually create a new lead record.</p>
                            </div>
                        </div>

                        <form action="{{ route('leads.store') }}" method="POST" class="flex flex-col gap-3 flex-1 min-h-0 justify-between">
                            @csrf
                            <div class="overflow-y-auto flex-1 px-1 py-1 space-y-2.5 min-h-0">
                                @forelse($allFields as $field)
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">{{ $field->label }}</label>
                                        <input type="text" name="field_{{ $field->key }}"
                                            placeholder="{{ $field->label }}"
                                            class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic">Upload a CSV first to define fields.</p>
                                @endforelse

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Assign to Agent</label>
                                    <select name="assigned_to" class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">— Unassigned —</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Status</label>
                                    <select name="status" class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="new">New</option>
                                        <option value="contacted">Contacted</option>
                                        <option value="converted">Converted</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Initial Notes</label>
                                    <textarea name="notes" rows="2"
                                        class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Background details..."></textarea>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm mt-auto">
                                Create Lead
                            </button>
                        </form>
                    </div>

                    {{-- Add Agent --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col gap-4" style="height: 360px;">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-purple-50 shrink-0">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm text-gray-900">Add Agent</h3>
                                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Create a new agent login account.</p>
                            </div>
                        </div>

                        <form action="{{ route('agents.store') }}" method="POST" class="flex flex-col gap-3 flex-1 min-h-0 justify-between">
                            @csrf
                            <div class="overflow-y-auto flex-1 px-1 py-1 space-y-2.5 min-h-0">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Full Name</label>
                                    <input type="text" name="name" required placeholder="Agent Name"
                                        class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Email Address</label>
                                    <input type="email" name="email" required placeholder="agent@example.com"
                                        class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Password</label>
                                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                                        class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-0.5">Confirm Password</label>
                                    <input type="password" name="password_confirmation" required placeholder="Repeat password"
                                        class="block w-full rounded-lg border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold transition shadow-sm mt-auto">
                                Create Agent
                            </button>
                        </form>
                    </div>

                </div>
            @endif

            {{-- ── Search & Filter Bar ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
                <form action="{{ route('dashboard') }}" method="GET"
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
                        <a href="{{ route('dashboard') }}"
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
                                                class="absolute left-4 top-full mt-1 bg-white border border-gray-200 shadow-xl rounded-xl p-1.5 z-20 min-w-[130px]">
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
                                            {{ $lead->data[$field->key] ?? '—' }}
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
                                            <span class="text-sm text-gray-400">No leads match the current filters.</span>
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