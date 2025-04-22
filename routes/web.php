<?php

use App\Http\Controllers\PublicFormController;
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
    return view('public-welcome');
});

// Public form routes (no authentication required)
Route::get('/submit', [PublicFormController::class, 'index'])->name('public.form');
Route::post('/submit', [PublicFormController::class, 'store'])->name('public.form.store');
Route::get('/submit/success', [PublicFormController::class, 'success'])->name('public.form.success');
