<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadField;
use App\Models\User;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Fetch dynamic columns config
        $visibleFields = LeadField::where('is_visible', true)->get();
        $allFields = LeadField::all();
        $agents = User::where('role', 'user')->get();

        // Base query
        $query = Lead::with('agent');

        // Search in JSON data column if search term is provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            // SQLite search inside JSON: we can perform a simple where like on the raw json text representation or scan keys.
            // Since it's SQLite, we can search by casting to string or using raw like on the 'data' field.
            $query->where('data', 'like', '%' . $search . '%');
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by Assignment (Admin only)
        if ($user->isAdmin() && $request->filled('agent_id')) {
            $agentId = $request->input('agent_id');
            if ($agentId === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $agentId);
            }
        }

        // Role-based scoping
        if (!$user->isAdmin()) {
            // Agent only sees leads assigned to them
            $query->where('assigned_to', $user->id);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(10)->appends(request()->query());

        // Calculate statistics
        if ($user->isAdmin()) {
            $stats = [
                'total' => Lead::count(),
                'new' => Lead::where('status', 'new')->count(),
                'contacted' => Lead::where('status', 'contacted')->count(),
                'converted' => Lead::where('status', 'converted')->count(),
                'lost' => Lead::where('status', 'lost')->count(),
                'unassigned' => Lead::whereNull('assigned_to')->count(),
            ];
        } else {
            $stats = [
                'total' => Lead::where('assigned_to', $user->id)->count(),
                'new' => Lead::where('assigned_to', $user->id)->where('status', 'new')->count(),
                'contacted' => Lead::where('assigned_to', $user->id)->where('status', 'contacted')->count(),
                'converted' => Lead::where('assigned_to', $user->id)->where('status', 'converted')->count(),
                'lost' => Lead::where('assigned_to', $user->id)->where('status', 'lost')->count(),
            ];
        }

        return view('dashboard', compact('leads', 'visibleFields', 'allFields', 'agents', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Validate basic status and assigned_to
        $request->validate([
            'status' => 'required|string|in:new,contacted,converted,lost',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Capture all dynamic fields that exist in lead_fields
        $fields = LeadField::all();
        $leadData = [];
        foreach ($fields as $field) {
            $leadData[$field->key] = $request->input('field_' . $field->key, '');
        }

        // Add note if provided
        if ($request->filled('notes')) {
            $leadData['notes'] = $request->input('notes');
        }

        Lead::create([
            'status' => $request->input('status', 'new'),
            'assigned_to' => $request->input('assigned_to'),
            'data' => $leadData,
        ]);

        return redirect()->route('dashboard')->with('success', 'Lead created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();

        // Agents can only update status and notes of their own assigned leads
        if (!$user->isAdmin()) {
            if ($lead->assigned_to !== $user->id) {
                abort(403, 'Unauthorized action.');
            }

            $request->validate([
                'status' => 'required|string|in:new,contacted,converted,lost',
                'notes' => 'nullable|string',
            ]);

            $leadData = $lead->data;
            if ($request->has('notes')) {
                $leadData['notes'] = $request->input('notes');
            }

            $lead->update([
                'status' => $request->input('status'),
                'data' => $leadData,
            ]);

            return redirect()->route('dashboard')->with('success', 'Lead updated successfully.');
        }

        // Admin can update everything, including assignment, status, and dynamic fields
        $request->validate([
            'status' => 'required|string|in:new,contacted,converted,lost',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $fields = LeadField::all();
        $leadData = $lead->data ?? [];
        foreach ($fields as $field) {
            if ($request->has('field_' . $field->key)) {
                $leadData[$field->key] = $request->input('field_' . $field->key);
            }
        }

        if ($request->has('notes')) {
            $leadData['notes'] = $request->input('notes');
        }

        $lead->update([
            'status' => $request->input('status'),
            'assigned_to' => $request->input('assigned_to'),
            'data' => $leadData,
        ]);

        return redirect()->route('dashboard')->with('success', 'Lead updated successfully.');
    }

    /**
     * Assign a lead directly (Quick action for Admin).
     */
    public function assign(Request $request, Lead $lead)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->update([
            'assigned_to' => $request->input('assigned_to'),
        ]);

        $agentName = $lead->assigned_to ? User::find($lead->assigned_to)->name : 'Unassigned';
        return redirect()->route('dashboard')->with('success', "Lead successfully assigned to {$agentName}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $lead->delete();
        return redirect()->route('dashboard')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Remove the specified resources from storage in bulk.
     */
    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = explode(',', $request->input('ids'));
        $deletedCount = Lead::whereIn('id', $ids)->delete();

        return redirect()->route('dashboard')->with('success', "{$deletedCount} lead(s) deleted successfully.");
    }
}
