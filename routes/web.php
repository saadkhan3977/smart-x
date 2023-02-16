<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\Admin\UserController;
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
    return view('welcome');
});
// Auth::routes(['register' => false]);
Auth::routes();
Route::group(['prefix' => 'admin','middleware'=>'auth'], function(){
    Route::resource('/users',UserController::class);
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
