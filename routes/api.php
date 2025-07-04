<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LogController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| نظام LogVault - API بسيط لاستقبال السجلات من المشاريع الأخرى
|
*/

// Route للحصول على معلومات المستخدم المصادق عليه
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API للسجلات - يتطلب project token validation (يتضمن auth) و IP whitelist
Route::middleware(['validate.project.token', 'ip.whitelist'])->group(function () {

    Route::post('/logs/batch', [LogController::class, 'storeBatch']);
    
});
