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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

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
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login'])->name("auth.login");
    Route::post('register', [AuthController::class, 'register'])->name("auth.register");
    Route::post('sendcode', [AuthController::class, 'sendCode'])->name("auth.send.code");
    Route::post('sendcodenewnumber', [AuthController::class, 'sendCodeToNewNumber'])->name("auth.send.code.to.new.number");
    Route::post('sendsms', [AuthController::class, 'sendSMS'])->name("auth.send.SMS");
    Route::post('verifyemail', [AuthController::class, 'verifyEmail'])->name("auth.verify.email");
    Route::post('verifyphone', [AuthController::class, 'verifyPhone'])->name("auth.verify.phone");
    Route::post('verifycode', [AuthController::class, 'verifyCode'])->name("auth.verify.code");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'auth'
], function ($router) {
    Route::post('logout', [AuthController::class, 'logout'])->name("auth.logout");
    Route::post('refresh', [AuthController::class, 'refresh'])->name("auth.refresh");
    Route::get('me', [AuthController::class, 'me'])->name("auth.me");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'userprofiles'
], function ($router) {
    Route::get('/', [UserProfileController::class, 'list'])->name("userprofiles.list");
    Route::get('find/{id}', [UserProfileController::class, 'find'])->name("userprofiles.find");
    Route::get('withuser/{id}', [UserProfileController::class, 'find_with_user'])->name("userprofiles.find_with_user");
    Route::get('withuser', [UserProfileController::class, 'list_with_user'])->name("userprofiles.list_with_user");
    Route::delete('softremove/{id}', [UserProfileController::class, 'soft_remove'])->name("userprofiles.soft_remove");
    Route::get('softrestore/{id}', [UserProfileController::class, 'soft_restore'])->name("userprofiles.soft_restore");
    Route::post('addupdate', [UserProfileController::class, 'add_update'])->name("userprofiles.add_update");
    Route::post('setstatus', [UserProfileController::class, 'set_status'])->name("userprofiles.set_status");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'conectedapp'
], function ($router) {
    Route::get('/', [ConectedAppController::class, 'list'])->name("conectedapp.list");
    Route::get('find/{id}', [ConectedAppController::class, 'find'])->name("conectedapp.find");
    Route::get('withuser', [ConectedAppController::class, 'list_with_user'])->name("conectedapp.list_with_user");
    Route::get('withuser/{id}', [ConectedAppController::class, 'find_with_user'])->name("conectedapp.find_with_user");
    Route::delete('softremove/{id}', [ConectedAppController::class, 'soft_remove'])->name("conectedapp.soft_remove");
    Route::get('softrestore/{id}', [ConectedAppController::class, 'soft_restore'])->name("conectedapp.soft_restore");
    Route::post('addupdate', [ConectedAppController::class, 'add_update'])->name("conectedapp.add_update");
    Route::get('me', [ConectedAppController::class, 'me'])->name("conectedapp.me");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'event'
], function ($router) {
    // Route::get('/', [EventController::class, 'list'])->name("event.list_null");
    Route::get('find/{id}', [EventController::class, 'find'])->name("event.find");
    Route::post('copy', [EventController::class, 'copy'])->name("event.copy");
    Route::get('nearby/{local_lat}/{local_long}', [EventController::class, 'list_nearby'])->name("event.list_nearby");
    Route::get('detail', [EventController::class, 'list_detail'])->name("event.list_detail");
    Route::get('listfiltertotal', [EventController::class, 'list_total_filter'])->name("event.list_total_filter");
    Route::get('me', [EventController::class, 'me'])->name("event.me");
    Route::get('me/{filter}', [EventController::class, 'me'])->name("event.me_filter");

    Route::post('searchgeocode', [EventController::class, 'search_geocode'])->name("event.search_geocode");

    // Route::get('/{filter}', [EventController::class, 'list'])->name("event.list");
    // Route::get('/{filter}/{time}', [EventController::class, 'list'])->name("event.list_time");

    Route::get('withuser', [EventController::class, 'list_with_user'])->name("event.list_with_user");
    Route::get('withuser/{id}', [EventController::class, 'find_with_user'])->name("event.find_with_user");
    Route::get('detail/{id}', [EventController::class, 'find_detail'])->name("event.find_detail");

    Route::delete('softremove/{id}', [EventController::class, 'soft_remove'])->name("event.soft_remove");
    Route::post('publishevent', [EventController::class, 'publish_event'])->name("event.publish_event");
    Route::get('softrestore/{id}', [EventController::class, 'soft_restore'])->name("event.soft_restore");
    Route::post('addupdate', [EventController::class, 'add_update'])->name("event.add_update");

    Route::post('listsearch', [EventController::class, 'list_search'])->name("event.list_search");

    Route::post('currentnearby', [EventController::class, 'current_nearby'])->name("event.current_nearby");
    Route::get('/{filter?}/{time?}', [EventController::class, 'list'])->name("event.list");
    Route::post('eventresponses', [EventController::class, 'event_responses'])->name("event.eventresponses");
    Route::post('listnearbyattending', [EventController::class, 'list_nearby_attending'])->name("event.list_nearby_attending");

});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'filter'
], function ($router) {
    Route::get('/', [FilterController::class, 'list'])->name("filter.list");
    Route::get('find/{id}', [FilterController::class, 'find'])->name("filter.find");
    Route::delete('softremove/{id}', [FilterController::class, 'soft_remove'])->name("filter.soft_remove");
    Route::get('softrestore/{id}', [FilterController::class, 'soft_restore'])->name("filter.soft_restore");
    Route::post('addupdate', [FilterController::class, 'add_update'])->name("filter.add_update");

    Route::get('listtypeevent', [FilterController::class, 'list_type_event'])->name("filter.list_type_event");
    Route::get('listtypeadvanced/{mode}', [FilterController::class, 'list_type_advanced'])->name("filter.list_type_advanced");
    Route::get('listtypeadvancedsub/{mode}/{parent_id}', [FilterController::class, 'list_type_advanced_sub'])->name("filter.list_type_advanced_sub");
    Route::post('listfilters', [FilterController::class, 'list_filters'])->name("filter.list_filters");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'filtertoevent'
], function ($router) {
    Route::get('/', [FilterToEventController::class, 'list'])->name("filtertoevent.list");
    Route::get('find/{id}', [FilterToEventController::class, 'find'])->name("filtertoevent.find");
    Route::delete('softremove', [FilterToEventController::class, 'soft_remove'])->name("filtertoevent.soft_remove");
    Route::get('softrestore/{id}', [FilterToEventController::class, 'soft_restore'])->name("filtertoevent.soft_restore");
    Route::post('addupdate', [FilterToEventController::class, 'add_update'])->name("filtertoevent.add_update");
    Route::post('finddetails', [FilterToEventController::class, 'find_details'])->name("filtertoevent.find_details");
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

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'matchs'
], function ($router) {
    Route::get('/', [MatchsController::class, 'list'])->name("matchs.list");
    Route::get('find/{id}', [MatchsController::class, 'find'])->name("matchs.find");
    Route::delete('softremove/{id}', [MatchsController::class, 'soft_remove'])->name("matchs.soft_remove");
    Route::get('softrestore/{id}', [MatchsController::class, 'soft_restore'])->name("matchs.soft_restore");
    Route::post('addupdate', [MatchsController::class, 'add_update'])->name("matchs.add_update");
    Route::post('matchesbyevent', [MatchsController::class, 'list_by_event'])->name("matchs.list_by_event");
    Route::post('matchesinbox', [MatchsController::class, 'matches_inbox'])->name("matchs.matches_inbox");
    Route::post('creatematch', [MatchsController::class, 'create_match'])->name("matchs.create_match");
    Route::post('decidematch', [MatchsController::class, 'decide_match'])->name("matchs.decide_match");
    Route::post('getloggedmatches', [MatchsController::class, 'get_logged_matches'])->name("matchs.get_logged_matches");
    Route::post('getloggedmatchessent', [MatchsController::class, 'get_logged_matches_sent'])->name("matchs.get_logged_matches_sent");
    Route::post('getloggedmatchesreceived', [MatchsController::class, 'get_logged_matches_received'])->name("matchs.get_logged_matches_received");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'passion'
], function ($router) {
    Route::get('/', [PassionController::class, 'list'])->name("passion.list");
    Route::get('find/{id}', [PassionController::class, 'find'])->name("passion.find");
    Route::delete('softremove/{id}', [PassionController::class, 'soft_remove'])->name("passion.soft_remove");
    Route::get('softrestore/{id}', [PassionController::class, 'soft_restore'])->name("passion.soft_restore");
    Route::post('addupdate', [PassionController::class, 'add_update'])->name("passion.add_update");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'personalfilter'
], function ($router) {
    Route::get('/', [PersonalFilterController::class, 'list'])->name("personalfilter.list");
    Route::get('find/{id}', [PersonalFilterController::class, 'find'])->name("personalfilter.find");
    Route::delete('softremove/{id}', [PersonalFilterController::class, 'soft_remove'])->name("personalfilter.soft_remove");
    Route::get('softrestore/{id}', [PersonalFilterController::class, 'soft_restore'])->name("personalfilter.soft_restore");
    Route::post('addupdate', [PersonalFilterController::class, 'add_update'])->name("personalfilter.add_update");
    Route::get('me', [PersonalFilterController::class, 'me'])->name("personalfilter.me");
    Route::post('addupdateheightrange', [PersonalFilterController::class, 'add_update_height_range'])->name("personalfilter.add_update_height_range");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'personalfiltertofilter'
], function ($router) {
    Route::get('/', [PersonalFilterToFilterController::class, 'list'])->name("personalfiltertofilter.list");
    Route::get('find/{id}', [PersonalFilterToFilterController::class, 'find'])->name("personalfiltertofilter.find");
    Route::delete('softremove', [PersonalFilterToFilterController::class, 'soft_remove'])->name("personalfiltertofilter.soft_remove");
    Route::get('softrestore/{id}', [PersonalFilterToFilterController::class, 'soft_restore'])->name("personalfiltertofilter.soft_restore");
    Route::post('addupdate', [PersonalFilterToFilterController::class, 'add_update'])->name("personalfiltertofilter.add_update");
    Route::post('addupdateme', [PersonalFilterToFilterController::class, 'add_update_me'])->name("personalfiltertofilter.add_update_me");
    Route::post('addupdatelooking', [PersonalFilterToFilterController::class, 'add_update_looking'])->name("personalfiltertofilter.add_update_looking");
    Route::get('listme', [PersonalFilterToFilterController::class, 'list_me'])->name("personalfiltertofilter.list_me");
    Route::get('listlooking', [PersonalFilterToFilterController::class, 'list_looking'])->name("personalfiltertofilter.list_looking");
    Route::post('listuserself', [PersonalFilterToFilterController::class, 'list_user_self'])->name("personalfiltertofilter.list_user_self");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'photoevent'
], function ($router) {
    Route::get('/', [PhotoEventController::class, 'list'])->name("photoevent.list");
    Route::get('find/{id}', [PhotoEventController::class, 'find'])->name("photoevent.find");
    Route::delete('softremove/{id}', [PhotoEventController::class, 'soft_remove'])->name("photoevent.soft_remove");
    Route::get('softrestore/{id}', [PhotoEventController::class, 'soft_restore'])->name("photoevent.soft_restore");
    Route::post('addupdate', [PhotoEventController::class, 'add_update'])->name("photoevent.add_update");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'promoteevent'
], function ($router) {
    Route::get('/', [PromoteEventController::class, 'list'])->name("promoteevent.list");
    Route::get('find/{id}', [PromoteEventController::class, 'find'])->name("promoteevent.find");
    Route::delete('softremove/{id}', [PromoteEventController::class, 'soft_remove'])->name("promoteevent.soft_remove");
    Route::get('softrestore/{id}', [PromoteEventController::class, 'soft_restore'])->name("promoteevent.soft_restore");
    Route::post('addupdate', [PromoteEventController::class, 'add_update'])->name("promoteevent.add_update");
    Route::get('me', [PromoteEventController::class, 'me'])->name("promoteevent.me");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'report'
], function ($router) {
    Route::get('/', [ReportController::class, 'list'])->name("report.list");
    Route::get('find/{id}', [ReportController::class, 'find'])->name("report.find");
    Route::delete('softremove/{id}', [ReportController::class, 'soft_remove'])->name("report.soft_remove");
    Route::get('softrestore/{id}', [ReportController::class, 'soft_restore'])->name("report.soft_restore");
    Route::post('addupdate', [ReportController::class, 'add_update'])->name("report.add_update");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'subscription'
], function ($router) {
    Route::get('/', [SubscriptionController::class, 'list'])->name("subscription.list");
    Route::get('find/{id}', [SubscriptionController::class, 'find'])->name("subscription.find");

    Route::get('withuser', [SubscriptionController::class, 'list_with_user'])->name("subscription.list_with_user");
    Route::get('withuser/{id}', [SubscriptionController::class, 'find_with_user'])->name("subscription.find_with_user");

    Route::delete('softremove/{id}', [SubscriptionController::class, 'soft_remove'])->name("subscription.soft_remove");
    Route::get('softrestore/{id}', [SubscriptionController::class, 'soft_restore'])->name("subscription.soft_restore");
    Route::post('addupdate', [SubscriptionController::class, 'add_update'])->name("subscription.add_update");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'user'
], function ($router) {
    Route::get('/', [UserController::class, 'list'])->name("user.list");
    Route::get('detail', [UserController::class, 'list_detail'])->name("user.list_detail");
    Route::get('detail/{id}', [UserController::class, 'find_detail'])->name("user.find_detail");
    Route::get('find/{id}', [UserController::class, 'find'])->name("user.find");
    Route::delete('softremove/{id}', [UserController::class, 'soft_remove'])->name("user.soft_remove");
    Route::get('softrestore/{id}', [UserController::class, 'soft_restore'])->name("user.soft_restore");
    Route::post('update', [UserController::class, 'update'])->name("user.update");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'usercontact'
], function ($router) {
    Route::get('/', [UserContactController::class, 'list'])->name("usercontact.list");
    Route::get('find/{id}', [UserContactController::class, 'find'])->name("usercontact.find");
    Route::delete('softremove/{id}', [UserContactController::class, 'soft_remove'])->name("usercontact.soft_remove");
    Route::get('softrestore/{id}', [UserContactController::class, 'soft_restore'])->name("usercontact.soft_restore");
    Route::post('addupdate', [UserContactController::class, 'add_update'])->name("usercontact.add_update");
    Route::post('addupdatemycontacts', [UserContactController::class, 'add_update_my_contacts'])->name("usercontact.add_update_my_contacts");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'usertopassion'
], function ($router) {
    Route::get('/', [UserToPassionController::class, 'list'])->name("usertopassion.list");
    Route::get('listforuser/{id}', [UserToPassionController::class, 'list_for_user'])->name("usertopassion.listforuser");
    Route::get('find/{id}', [UserToPassionController::class, 'find'])->name("usertopassion.find");
    Route::delete('softremove', [UserToPassionController::class, 'soft_remove'])->name("usertopassion.soft_remove");
    Route::get('softrestore/{id}', [UserToPassionController::class, 'soft_restore'])->name("usertopassion.soft_restore");
    Route::post('addpassions', [UserToPassionController::class, 'add_passions'])->name("usertopassion.add_update");
    Route::get('me', [UserToPassionController::class, 'me'])->name("usertopassion.me");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'userphoto'
], function ($router) {
    Route::get('/', [UserPhotoController::class, 'list'])->name("userphoto.list");
    Route::get('find/{id}', [UserPhotoController::class, 'find'])->name("userphoto.find");

    Route::get('withuser', [UserPhotoController::class, 'list_with_user'])->name("userphoto.list_with_user");
    Route::get('withuser/{id}', [UserPhotoController::class, 'find_with_user'])->name("userphoto.find_with_user");

    Route::delete('softremove/{id}', [UserPhotoController::class, 'soft_remove'])->name("userphoto.soft_remove");
    Route::get('softrestore/{id}', [UserPhotoController::class, 'soft_restore'])->name("userphoto.soft_restore");
    Route::post('addupdate', [UserPhotoController::class, 'add_update'])->name("userphoto.add_update");

    Route::get('me', [UserPhotoController::class, 'me'])->name("userphoto.me");
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'validationcode'
], function ($router) {
    Route::post('validatecode', [ValidationCodeController::class, 'validate_code'])->name("validationcode.validate_code");
});

Route::group([
    'middleware' => 'jwt.verify'
], function ($router) {
    Route::get('/chat/token', [ChatController::class, 'token'])->name('api.chat.token');
});

Route::get('prueba', function () {
    return PersonalFilter::all();
});

Route::group([
    'middleware' => 'jwt.verify',
    'prefix' => 'ejem'
], function ($router) {
    Route::get('data', function () {
        return "Datos de ejemplo";
    });
});


