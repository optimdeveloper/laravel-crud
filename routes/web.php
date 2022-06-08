<?php

// use App\Http\Api\UserController;

use App\Http\Api\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Intervention\Image\Facades\Image;


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


Auth::routes();


Route::get('/', [App\Http\Controllers\HomeController::class, 'root']);
Route::get('index', [App\Http\Controllers\HomeController::class, 'root']);
Route::get('/auth-redirect', [App\Http\Controllers\HomeController::class, 'auth_redirect']);

// Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index']);
// Language Translation

Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::post('/formsubmit', [App\Http\Controllers\HomeController::class, 'FormSubmit'])->name('FormSubmit');

// Users
Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users');
Route::get('/userlist', [App\Http\Controllers\UserController::class, 'list'])->name('userlist'); //Return data Json
Route::get('/user/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('user.detail');
Route::get('/user/destroy/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.delete');
Route::get('/user/destroyCascade/{id}', [App\Http\Controllers\UserController::class, 'destroyCascade'])->name('user.deleteCascade');
// Route::post('/user/{id}', [App\Http\Controllers\UserController::class, 'store'])->name('user.save');

// Events
Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events');
Route::get('/eventlist', [App\Http\Controllers\EventController::class, 'list'])->name('eventlist'); //Return data Json
Route::get('/event/{id}', [App\Http\Controllers\EventController::class, 'edit'])->name('event.detail');
Route::get('/event/destroy/{id}', [App\Http\Controllers\EventController::class, 'destroy'])->name('event.delete');
Route::get('/event/cancel/{id}', [App\Http\Controllers\EventController::class, 'cancel_event'])->name('event.cancel');
// Route::post('/user/{id}', [App\Http\Controllers\EventController::class, 'store'])->name('event.save');

// Logs
Route::get('/logs', [App\Http\Controllers\LogsController::class, 'index'])->name('logs');
Route::get('/loglist', [App\Http\Controllers\LogsController::class, 'list'])->name('loglist'); //Return data Json
Route::get('/logtruncate', [App\Http\Controllers\LogsController::class, 'truncate'])->name('logtruncate');


// usage inside a laravel route
Route::get('/image', function () {
    return $img = Image::cache(function ($image) {
        $image->make('no-image-available.png')->resize(300, 200)->greyscale();
    });
});
