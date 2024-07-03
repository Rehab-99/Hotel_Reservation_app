<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomTypeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/forgot_password',[AuthController::class,'forgotPassword']);
Route::post('/reset_password',[AuthController::class,'resetPassword']);
Route::get('/profile',[ProfileController::class,'show'])->middleware('auth:sanctum');

//Home
Route::post('add_type',[RoomTypeController::class,'addType']);
Route::post('allTypes',[RoomTypeController::class,'allTypes']);
Route::post('add/Room',[RoomController::class,'addRoom']);
Route::get('allRooms/{id}',[RoomController::class,'allRooms']);
Route::get('room/details/{id}',[RoomController::class,'room_details']);
//Favorites
Route::get('add/favorite/{id}',[FavoriteController::class,'addToFavorites']);
Route::get('remove/favorite/{id}',[FavoriteController::class,'removeFromFavorites']);
Route::get('all/favorites',[FavoriteController::class,'favoriteRooms']);

//booking room
Route::post('booking/room/step1/',[BookingController::class,'booking_step1'])->middleware('auth:sanctum');
Route::post('booking/room/step2/',[BookingController::class,'booking_step2'])->middleware('auth:sanctum');
Route::get('payment/success',[BookingController::class,'paypalSuccess'])->name('paypal.success');
Route::get('payment/cancel',[BookingController::class,'paypalCancel'])->name('paypal.cancel');

//notifications
Route::get('unread/notifications/count',[NotificationController::class,'NotificationsCount'])->middleware('auth:sanctum');
Route::get('unread/notifications',[NotificationController::class,'Notifications'])->middleware('auth:sanctum');
Route::get('notifications/mark-as-read/{id}',[NotificationController::class,'markAsRead'])->middleware('auth:sanctum');
