<?php

use App\Http\Middleware\fileOwner;
use App\Http\Middleware\groupOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\UpdateFileController;
use App\Http\Controllers\Groups;
use App\Http\Controllers\Users;

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

Route::middleware('logging')->group(function () {
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//to use passport authentication i put this middlware

Route::middleware('auth:api')->prefix('files')->group(function (){
    Route::post('/upload',[FilesController::class,'uploadFile']);
    Route::post('/checkin/{id}',[FilesController::class,'checkIN']);
    Route::post('/checkout/{id}',[FilesController::class,'checkOUT']);
    Route::get('/download/{id}',[FilesController::class,'downloadFile']);
    Route::post('/multiCheckIn',[FilesController::class,'multiCheckIN']);
    Route::delete('/delete/{id}',[FilesController::class,'deleteFile'])->middleware(fileOwner::class);
//    Route::delete('/delete/{id}', [FilesController::class, 'deleteFile'])->middleware('can:deleteFile');
//    Route::delete('/delete/{id}',[FilesController::class, 'deleteFile'])
//        ->middleware('can:delete,file');

    Route::delete('/remove/{id}',[FilesController::class,'destroy']);
    Route::post('/edit/{id}',[UpdateFileController::class,'updateFile']);
});


Route::get('/all',[FilesController::class,'getAllFiles']);
Route::get('/reports',[FilesController::class,'getAllReports']);
Route::get('/state/{id}',[FilesController::class,'fileState']);


Route::middleware('auth:api')->group(function (){
    Route::put('/update',[UpdateFileController::class,'updateFile']);

    Route::post('createGroup', [Groups::class, 'createGroup']);
    Route::delete('deleteGroup/{id}', [Groups::class, 'deleteGroup'])->middleware(groupOwner::class);
    Route::post('addFileToGroup/{id}', [Groups::class, 'addFileToGroup'])->middleware("userInGroup");;
    Route::delete('deleteFileFromGroup/{id}', [Groups::class, 'deleteFileFromGroup']);

//    Route::delete('deleteFileFromGroup/{group}', [GroupController::class, 'deleteFile'])
//        ->middleware('can:deleteFile,group');

    Route::post('addUsersToGroup/{id}', [Groups::class, 'addUsersToGroup'])->middleware(groupOwner::class);
    Route::post('deleteUsersFromGroup/{id}', [Groups::class, 'deleteUsersFromGroup'])->middleware(groupOwner::class);
    Route::get('allGroupFiles/{id}', [Groups::class, 'allGroupFiles'])->middleware("userInGroup");
    Route::get('getUsersNotInGroup/{id}', [Users::class, 'getUsersNotInGroup'])->middleware(groupOwner::class);


    Route::post('getMyCheckInFiles', [Groups::class, 'getMyCheckInFiles']);
    Route::get('allUserGroups', [Users::class, 'allUserGroups']);
    Route::get('allUserOwnedGroups', [Users::class, 'allUserOwnedGroups']);
    Route::get('allUserFiles', [Users::class, 'allUserFiles']);


});
Route::get('allGroupUsers/{id}', [Groups::class, 'allGroupUsers']);

Route::get('/all',[FilesController::class,'getAllFiles']);
Route::get('/state/{id}',[FilesController::class,'fileState']);
});
