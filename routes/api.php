<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Api\AuthController;
use App\Http\Api\ConectedAppController;
use App\Http\Api\EventController;
use App\Http\Api\FilterController;
use App\Http\Api\FilterToEventController;
use App\Http\Api\GuestController;
use App\Http\Api\MatchsController;
use App\Http\Api\PassionController;
use App\Http\Api\PersonalFilterController;
use App\Http\Api\PersonalFilterToFilterController;
use App\Http\Api\PhotoEventController;
use App\Http\Api\PromoteEventController;
use App\Http\Api\ReportController;
use App\Http\Api\SubscriptionController;
use App\Http\Api\UserContactController;
use App\Http\Api\UserController;
use App\Http\Api\UserPhotoController;
use App\Http\Api\UserToPassionController;
use App\Http\Api\UserProfileController;
use App\Http\Api\ValidationCodeController;
use App\Http\Api\Twilio\ChatController;
use App\Models\PersonalFilter;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('api')->group(function () {
    Route::get('/health', [App\Http\Api\MainController::class, 'health'])->name('api.main.health');
    Route::get('/ably/send', [App\Http\Api\Messaging\AblyController::class, 'send'])->name('api.messaging.ably.send');
});

Route::group([
    'middleware' => 'api'
], function ($router) {
    // Countries
    Route::get('/countries', [App\Http\Api\CountriesController::class, 'list'])->name('api.countries.list');
    Route::get('/country/{id}', [App\Http\Api\CountriesController::class, 'find'])->name('api.countries.find');
    Route::post('/countries/search', [App\Http\Api\CountriesController::class, 'search'])->name('api.countries.search');

    // States
    Route::get('/states', [App\Http\Api\StatesController::class, 'list'])->name('api.states.list');
    Route::get('/state/{id}', [App\Http\Api\StatesController::class, 'find'])->name('api.states.find');

    // Cities
    Route::get('/cities', [App\Http\Api\CitiesController::class, 'list'])->name('api.cities.list');
    Route::get('/city/{id}', [App\Http\Api\CitiesController::class, 'find'])->name('api.cities.find');
    Route::post('/cities/search', [App\Http\Api\CitiesController::class, 'search'])->name('api.cities.search');
    Route::get('/cities/top', [App\Http\Api\CitiesController::class, 'top'])->name('api.cities.top');
});



Route::group([
    'prefix' => 'crud'
], function ($router) {
    Route::get('/', [GuestController::class, 'list'])->name("guest.list");
    Route::get('find/{id}', [GuestController::class, 'find'])->name("guest.find");
    Route::get('me', [GuestController::class, 'me'])->name("guest.me");
    Route::delete('softremove/{id}', [GuestController::class, 'soft_remove'])->name("guest.soft_remove");
    Route::delete('remove/{id}', [GuestController::class, 'remove'])->name("guest.remove");
    Route::get('softrestore/{id}', [GuestController::class, 'soft_restore'])->name("guest.soft_restore");
    Route::post('addupdate', [GuestController::class, 'add_update'])->name("guest.add_update");
    Route::post('pushereventupdated', [GuestController::class, 'pusher_eventupdated'])->name("guest.pusher_eventupdated");
    Route::post('onlyupdate', [GuestController::class, 'only_update'])->name("guest.only_update");
    Route::post('createProduct', [GuestController::class, 'create_product'])->name("guest.create_product");
    Route::get('products', [GuestController::class, 'list_products'])->name("guest.list_products");
    Route::delete('deleteProduct/{id}', [GuestController::class, 'delete_product'])->name("guest.delete_product");
    Route::post('updateProduct/{id}', [GuestController::class, 'update_product'])->name("guest.update_product");
    Route::get('/create-symlink', function (){
        symlink(storage_path('/app/public'), public_path('Image'));
        echo "Symlink Created. Thanks";
    });
});



