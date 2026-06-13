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
        </div>
    </div>
</x-app-layout>