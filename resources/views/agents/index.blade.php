<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Agent Performance Directory') }}
            </h2>
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl border border-green-200 shadow-sm flex items-center" role="alert">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium">Success!</span> &nbsp;{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl border border-red-200 shadow-sm flex items-center" role="alert">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium">Error!</span> &nbsp;{{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl border border-red-200 shadow-sm" role="alert">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="font-medium">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Agents Performance Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ editingAgentId: null }">
                <div class="px-6 py-4 border-b border-gray-100 bg-slate-50">
                    <h3 class="font-bold text-gray-800">Agent Performance Stats</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-slate-50 text-gray-600 font-semibold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th scope="col" class="px-6 py-3">Agent Name</th>
                                <th scope="col" class="px-6 py-3">Email Address</th>
                                <th scope="col" class="px-6 py-3 text-center">Total Assigned</th>
                                <th scope="col" class="px-6 py-3 text-center text-blue-600">New</th>
                                <th scope="col" class="px-6 py-3 text-center text-amber-600">Contacted</th>
                                <th scope="col" class="px-6 py-3 text-center text-emerald-600">Converted</th>
                                <th scope="col" class="px-6 py-3 text-center text-rose-600">Lost</th>
                                <th scope="col" class="px-6 py-3 text-right">Conversion Rate</th>
                                <th scope="col" class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($agents as $agent)
                                <tr class="hover:bg-slate-50/70 transition duration-150">
                                    <!-- Agent Info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 text-indigo-600 flex items-center justify-center rounded-full font-bold">
                                                {{ substr($agent->name, 0, 2) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900">{{ $agent->name }}</div>
                                                <div class="text-[10px] text-gray-500 capitalize">{{ $agent->role }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Email -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $agent->email }}
                                    </td>

                                    <!-- Total Assigned -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-900 text-sm">
                                        {{ $agent->leads_count }}
                                    </td>

                                    <!-- Status Breakdown -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-600 font-semibold text-sm">
                                        {{ $agent->new_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-600 font-semibold text-sm">
                                        {{ $agent->contacted_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-emerald-600 font-bold text-sm">
                                        {{ $agent->converted_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-rose-600 font-semibold text-sm">
                                        {{ $agent->lost_count }}
                                    </td>

                                    <!-- Conversion Rate -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-sm">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $agent->conversion_rate >= 50 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($agent->conversion_rate >= 25 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                            {{ $agent->conversion_rate }}%
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                        <button @click="editingAgentId = (editingAgentId === {{ $agent->id }} ? null : {{ $agent->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 hover:bg-indigo-100/80 px-2.5 py-1.5 rounded-lg transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('agents.destroy', $agent->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this agent? All of their assigned leads will become unassigned.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold bg-rose-50 hover:bg-rose-100/80 px-2.5 py-1.5 rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Expandable Edit Agent Row -->
                                <tr x-show="editingAgentId === {{ $agent->id }}" x-cloak class="bg-indigo-50/20">
                                    <td colspan="9" class="px-6 py-4 border-t border-gray-100">
                                        <form action="{{ route('agents.update', $agent->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700">Full Name</label>
                                                    <input type="text" name="name" value="{{ $agent->name }}" required class="mt-1 block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700">Email Address</label>
                                                    <input type="email" name="email" value="{{ $agent->email }}" required class="mt-1 block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700">New Password (optional)</label>
                                                    <input type="password" name="password" class="mt-1 block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700">Confirm Password</label>
                                                    <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-xl border-gray-200 text-xs focus:border-indigo-500 focus:ring-indigo-500" placeholder="Repeat new password">
                                                </div>
                                            </div>

                                            <div class="flex justify-end space-x-2">
                                                <button type="button" @click="editingAgentId = null" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold py-2 px-4 rounded-xl transition">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-xl shadow-sm transition">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400 italic">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            <span>No agents registered in the system.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
