<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

/**
 * Change model_id from unsignedBigInteger to string.
 * 
 * This is required because some models (like JenisSurat) use string primary keys.
 * Audit logs need to support both numeric and string model IDs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop existing indexes that reference model_id
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_model');
            $table->dropIndex('idx_audit_model_date');
        });

        // Change column type
        DB::statement('ALTER TABLE audit_logs MODIFY model_id VARCHAR(100) NULL');

        // Recreate indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['model', 'model_id'], 'idx_audit_model');
            $table->index(['model', 'model_id', 'created_at'], 'idx_audit_model_date');
        });
    }

    public function down(): void
    {
        // Drop indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_model');
            $table->dropIndex('idx_audit_model_date');
        });

        // Revert to unsignedBigInteger (will fail if string PKs exist)
        DB::statement('ALTER TABLE audit_logs MODIFY model_id BIGINT UNSIGNED NULL');

        // Recreate indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['model', 'model_id'], 'idx_audit_model');
            $table->index(['model', 'model_id', 'created_at'], 'idx_audit_model_date');
        });
    }
};
