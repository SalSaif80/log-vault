<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\LogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// مجموعة routes للواجهة الإدارية
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    // الداشبورد الرئيسي
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // إدارة المشاريع
    Route::resource('projects', ProjectController::class);

    // إنشاء توكن API للمشروع
    Route::post('projects/{project}/tokens', [ProjectController::class, 'createToken'])->name('projects.tokens.create');
    Route::delete('tokens/{token}', [ProjectController::class, 'revokeToken'])->name( 'projects.tokens.revoke');



    // إدارة السجلات
    Route::resource('logs', LogController::class)->only(['index', 'show', 'destroy']);
    Route::get('logs-export', [LogController::class, 'export'])->name('logs.export');
});

// routes الملف الشخصي
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
