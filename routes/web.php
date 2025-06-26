<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
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
    Route::get('projects/{project}/tokens', [ProjectController::class, 'tokens'])->name('projects.tokens.index');
    Route::delete('tokens/{token}', [ProjectController::class, 'revokeToken'])->name('projects.tokens.revoke');

    // إدارة IP Whitelist
    Route::resource('projects.ip-whitelist', \App\Http\Controllers\Admin\IpWhitelistController::class)
        ->names([
            'index' => 'projects.ip-whitelist.index',
            'create' => 'projects.ip-whitelist.create',
            'store' => 'projects.ip-whitelist.store',
            'show' => 'projects.ip-whitelist.show',
            'edit' => 'projects.ip-whitelist.edit',
            'update' => 'projects.ip-whitelist.update',
            'destroy' => 'projects.ip-whitelist.destroy',
        ]);

    // روابط إضافية لـ IP Whitelist
    Route::post('ip-whitelist/{ipWhitelist}/toggle-status', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'toggleStatus'])->name('ip-whitelist.toggle-status');
    Route::post('projects/{project}/ip-whitelist/test', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'testIp'])->name('projects.ip-whitelist.test');
    Route::get('projects/{project}/ip-whitelist/statistics', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'statistics'])->name('projects.ip-whitelist.statistics');
    Route::post('projects/{project}/ip-whitelist/import', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'import'])->name('projects.ip-whitelist.import');
    Route::get('projects/{project}/ip-whitelist/export', [\App\Http\Controllers\Admin\IpWhitelistController::class, 'export'])->name('projects.ip-whitelist.export');

    // إدارة السجلات
    Route::resource('logs', \App\Http\Controllers\Admin\LogController::class)->only(['index', 'show', 'destroy']);
    Route::get('logs-export', [\App\Http\Controllers\Admin\LogController::class, 'export'])->name('logs.export');
});

// routes الملف الشخصي
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
