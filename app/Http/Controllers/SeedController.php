<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SeedController extends Controller
{
    /**
     * Run database seeds via a protected URL.
     *
     * Example: GET https://your-app.onrender.com/seed?token=YOUR_SECRET_TOKEN
     */
    public function run(Request $request)
    {
        $provided = $request->query('token');
        $expected = env('SEED_TOKEN');

        if (empty($provided) || $provided !== $expected) {
            // Log unauthorized attempts for audit
            Log::warning('Unauthorized seed attempt', ['ip' => $request->ip()]);
            abort(403, 'Forbidden');
        }

        // Force seeding even in production
        Artisan::call('db:seed', ['--force' => true]);
        $output = Artisan::output();

        return response()->json([
            'status' => 'seeding completed',
            'output' => $output,
        ]);
    }
}
