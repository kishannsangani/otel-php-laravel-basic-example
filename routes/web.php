<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloController;
use App\Http\Controllers\TreeBuilderController;

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

Route::get('/hello', [HelloController::class, 'index']);
Route::get('/treebuilder',  [TreeBuilderController::class, 'index']);
Route::get('/treebuilder/{width}',  [TreeBuilderController::class, 'index']);
Route::get('/treebuilder/{width}/{depth}',  [TreeBuilderController::class, 'index']);