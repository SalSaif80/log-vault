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

// Health check endpoint - لا يحتاج authentication
Route::get('/health', [LogController::class, 'health']);

// Endpoint سريع لإنشاء توكن (للاختبار فقط)
Route::get('/generate-token', function () {
    $user = User::first();

    if (!$user) {
        return response()->json(['error' => 'No user found'], 404);
    }

    $token = $user->createToken('LogVault API Token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'note' => 'انسخ هذا التوكن واستخدمه في المشروع المرسِل'
    ]);
});

// API للسجلات - يتطلب Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {

    // استقبال دفعة من السجلات (الاستخدام الأساسي)
    Route::post('/logs/batch', [LogController::class, 'storeBatch']);

    // إرسال سجل واحد (للاختبار)
    Route::post('/logs', [LogController::class, 'store']);

    // قراءة السجلات (للمراجعة)
    Route::get('/logs', [LogController::class, 'index']);
    Route::get('/logs/{id}', [LogController::class, 'show']);

    // إحصائيات
    Route::get('/logs/statistics', [LogController::class, 'statistics']);

    // إحصائيات متقدمة
    Route::get('/analytics', [LogController::class, 'statistics']);
});

// Admin API routes (للاستخدام الداخلي)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // إحصائيات النظام
    Route::get('/system-status', function () {
        return response()->json([
            'database' => 'connected',
            'cache' => 'working',
            'storage' => 'accessible',
            'memory_usage' => memory_get_usage(true),
            'timestamp' => now()->toISOString()
        ]);
    });

});

