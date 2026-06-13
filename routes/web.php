<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadUploadController;
use App\Http\Controllers\LeadFieldController;
use App\Http\Controllers\AgentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [LeadController::class, 'index'])->name('dashboard');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::delete('/leads/bulk-delete', [LeadController::class, 'bulkDestroy'])->name('leads.bulk-delete')->middleware('can:admin');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    
    Route::middleware('can:admin')->group(function () {
        Route::post('/leads/upload', [LeadUploadController::class, 'upload'])->name('leads.upload');
        Route::post('/leads/fields/visibility', [LeadFieldController::class, 'updateVisibility'])->name('leads.fields.visibility');
        Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
        Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
        Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
        Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
