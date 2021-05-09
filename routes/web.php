<?php

use App\Events\Chat;
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
    return view('chat');
})->middleware('auth');
Route::get('/login',function (){
  return view('login');
})->name('login');
Route::get('/register',function (){
    return view('register');
})->name('register');
Route::post('/login',[\App\Http\Controllers\Controller::class,'login']);
Route::post('/register',[\App\Http\Controllers\Controller::class,'register']);
Route::post('/chat',function(\Illuminate\Http\Request $req){
    broadcast(new Chat($req->message));
});
