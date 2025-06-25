<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المشروع
            $table->string('slug')->unique(); // slug فريد للمشروع
            $table->text('description')->nullable(); // وصف المشروع
            $table->string('api_url')->nullable(); // رابط API الخاص بالمشروع لجلب البيانات
            $table->enum('status', ['active', 'inactive'])->default('active'); // حالة المشروع
            $table->json('metadata')->nullable(); // معلومات إضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
