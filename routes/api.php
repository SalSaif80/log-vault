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

// API للسجلات - يتطلب Sanctum authentication و IP whitelist
Route::middleware(['auth:sanctum', 'ip.whitelist'])->group(function () {

    // استقبال دفعة من السجلات (الاستخدام الأساسي)
    Route::post('/logs/batch', [LogController::class, 'storeBatch']);
});
