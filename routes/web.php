<?php

use App\Http\Controllers\OpinionController;
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
    return view('maps.show');
});

Route::get('/map', function(){
    return view('maps.show');
})->name('map');

Route::resource('opinion',\App\Http\Controllers\OpinionController::class);

Route::get('/stations/{int}/opinions',[OpinionController::class,'showByStation']);

Route::get('/dashboard', function () {
    return view('maps.show');
})->name('dashboard');

require __DIR__.'/auth.php';
