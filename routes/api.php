<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\CookieController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1/auth')->group(function(){
    
    Route::post('register', [AuthController::class, 'createUser']);
    Route::post('login', [AuthController::class, 'loginUser']);
    
    Route::group(['middleware' => ['auth:sanctum']], function () {
        
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('user', function(Request $request){
            return $request->user();
        });

        Route::post('add-money-in-wallet', [WalletController::class, 'addMoney']);
        Route::post('buy-cookie', [CookieController::class, 'buyCookie']);

    });

});

