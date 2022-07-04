<?php

use App\Http\Controllers\PeramalanController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PermintaanController;
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

Route::get('/', function () {
    return view('periode');
});

Route::resource('periode', PeriodeController::class)->except(['create', 'edit']);
Route::resource('permintaan', PermintaanController::class)->except(['create', 'edit']);
Route::resource('peramalan', PeramalanController::class)->only(['index', 'store']);
