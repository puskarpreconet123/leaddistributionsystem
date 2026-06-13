<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadField;
use Illuminate\Support\Str;

class LeadUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        if (($handle = fopen($path, 'r')) !== false) {
            // Read headers (first row)
            $rawHeaders = fgetcsv($handle, 1000, ',');
            
            if (!$rawHeaders) {
                fclose($handle);
                return back()->with('error', 'The uploaded CSV file is empty.');
            }

            $headers = [];
            foreach ($rawHeaders as $header) {
                $trimmed = trim($header);
                $key = Str::snake($trimmed);
                
                if (empty($key)) {
                    continue;
                }

                // Register the lead field if it doesn't exist
                LeadField::firstOrCreate(
                    ['key' => $key],
                    ['label' => $trimmed, 'is_visible' => true]
                );

                $headers[] = [
                    'key' => $key,
                    'label' => $trimmed,
                ];
            }

            // Read rows
            $rowCount = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                $rowData = [];
                foreach ($headers as $index => $headerInfo) {
                    $val = isset($row[$index]) ? trim($row[$index]) : '';
                    $rowData[$headerInfo['key']] = $val;
                }

                Lead::create([
                    'status' => 'new',
                    'data' => $rowData,
                    'assigned_to' => null, // Initially unassigned
                ]);
                $rowCount++;
            }

            fclose($handle);
            return back()->with('success', "CSV uploaded successfully! {$rowCount} leads imported.");
        }

        return back()->with('error', 'Unable to open the uploaded file.');
    }
}
