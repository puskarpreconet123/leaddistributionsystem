<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadField;

class LeadFieldController extends Controller
{
    public function updateVisibility(Request $request)
    {
        $visibleKeys = $request->input('visible_fields', []);

        // Set all fields to hidden first
        LeadField::query()->update(['is_visible' => false]);

        if (!empty($visibleKeys)) {
            // Set selected fields to visible
            LeadField::whereIn('key', $visibleKeys)->update(['is_visible' => true]);
        }

        return back()->with('success', 'Lead table columns visibility updated successfully.');
    }
}
