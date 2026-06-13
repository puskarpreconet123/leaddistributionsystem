<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadField;
use Illuminate\Support\Str;

class ApiLeadController extends Controller
{
    /**
     * Store a dynamic lead received from API.
     */
    public function store(Request $request)
    {
        // Get all JSON inputs from the request body
        $inputData = $request->json()->all();

        if (empty($inputData)) {
            $inputData = $request->all();
        }

        if (empty($inputData)) {
            return response()->json([
                'success' => false,
                'message' => 'No data received.'
            ], 400);
        }

        // Dynamically discover and register fields
        $leadData = [];
        foreach ($inputData as $key => $value) {
            // Normalize key
            $normalizedKey = Str::snake($key);
            
            // Skip Laravel default parameters if they happen to bleed in
            if (in_array($normalizedKey, ['_token', '_method', 'status', 'assigned_to'])) {
                continue;
            }

            // Capitalize snake case key to form a clean label
            $label = ucwords(str_replace('_', ' ', $normalizedKey));

            // Register the field if it does not exist
            LeadField::firstOrCreate(
                ['key' => $normalizedKey],
                ['label' => $label, 'is_visible' => true]
            );

            $leadData[$normalizedKey] = is_array($value) ? json_encode($value) : trim($value);
        }

        // Create the lead
        $lead = Lead::create([
            'status' => 'new',
            'assigned_to' => null, // Left for Admin to manually assign
            'data' => $leadData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead registered and pending manual assignment.',
            'lead_id' => $lead->id
        ], 201);
    }
}
