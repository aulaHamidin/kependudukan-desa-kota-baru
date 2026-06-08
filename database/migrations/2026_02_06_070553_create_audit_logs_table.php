<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->string('aksi', 50);
            $table->string('model', 100);
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('role_snapshot', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for audit trail queries
            $table->index('user_id');
            $table->index('aksi');
            $table->index(['model', 'model_id'], 'idx_audit_model');
            $table->index('created_at');

            // Composite indexes for complex queries
            $table->index(['user_id', 'created_at'], 'idx_audit_user_date');
            $table->index(['model', 'model_id', 'created_at'], 'idx_audit_model_date');
            $table->index(['aksi', 'created_at'], 'idx_audit_aksi_date');

            // Search/filter index
            $table->index(['user_id', 'aksi', 'model', 'created_at'], 'idx_audit_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
