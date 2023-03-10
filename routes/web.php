<?php

use App\Http\Controllers\HoroscopesController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HoroscopesController::class, 'index'])->name('horoscopes.index');

//Route::middleware('auth')->group(function () {
//    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
//    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
//});

Route::post('/search', [HoroscopesController::class, 'search'])->name('search');
Route::post('/import_parse', [ImportController::class, 'parseImport'])->name('import_parse');
Route::post('/import_process', [ImportController::class, 'processImport'])->name('import_process');

require __DIR__.'/auth.php';
