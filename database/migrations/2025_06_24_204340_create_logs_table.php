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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_log_id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();  // ✅ مبسط
            $table->unsignedBigInteger('subject_id')->nullable();  // ✅ مبسط
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();  // ✅ مبسط
            $table->unsignedBigInteger('causer_id')->nullable();  // ✅ مبسط
            $table->string('batch_uuid')->nullable();
            $table->json('properties')->nullable();
            $table->string('source_system');
            $table->string('project_name');  // ✅ إضافة جديدة
            $table->timestamp('occurred_at');
            $table->timestamps();

            // Indexes
            $table->index(['external_log_id', 'source_system']);
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
