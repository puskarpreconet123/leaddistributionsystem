<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AgentController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all agents with their leads count
        $agents = User::where('role', 'user')
            ->withCount('leads')
            ->get();

        foreach ($agents as $agent) {
            $agent->new_count = $agent->leads()->where('status', 'new')->count();
            $agent->contacted_count = $agent->leads()->where('status', 'contacted')->count();
            $agent->converted_count = $agent->leads()->where('status', 'converted')->count();
            $agent->lost_count = $agent->leads()->where('status', 'lost')->count();
            
            $total = $agent->leads_count;
            $agent->conversion_rate = $total > 0 ? round(($agent->converted_count / $total) * 100, 1) : 0;
        }

        return view('agents.index', compact('agents'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $agent = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return redirect()->back()->with('success', "Agent {$agent->name} successfully created.");
    }

    public function update(Request $request, User $agent)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $agent->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $agent->name = $request->name;
        $agent->email = $request->email;

        if ($request->filled('password')) {
            $agent->password = Hash::make($request->password);
        }

        $agent->save();

        return redirect()->back()->with('success', "Agent {$agent->name} successfully updated.");
    }

    public function destroy(User $agent)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Safety reassignment: set leads to unassigned
        Lead::where('assigned_to', $agent->id)->update(['assigned_to' => null]);

        $agentName = $agent->name;
        $agent->delete();

        return redirect()->back()->with('success', "Agent {$agentName} successfully deleted.");
    }
}
