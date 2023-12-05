<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\UpdateFileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//to use passport authentication i put this middlware

Route::middleware('auth:api')->prefix('files')->group(function (){
    Route::post('/upload',[FilesController::class,'uploadFile']);
    Route::post('/checkin/{id}',[FilesController::class,'checkIN']);
    Route::post('/checkout/{id}',[FilesController::class,'checkOUT']);
    Route::get('/download/{id}',[FilesController::class,'downloadFile']);
    Route::post('/multiCheckIn',[FilesController::class,'multiCheckIN']);
    Route::delete('/delete/{id}',[FilesController::class,'deleteFile']);
    Route::delete('/remove/{id}',[FilesController::class,'destroy']);
});

Route::middleware('auth:api')->group(function (){
    Route::put('/update',[UpdateFileController::class,'updateFile']);
});

Route::get('/all',[FilesController::class,'getAllFiles']);
Route::get('/state/{id}',[FilesController::class,'fileState']);