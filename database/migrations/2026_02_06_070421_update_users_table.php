<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns for desa, rw, rt hierarchy
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->unique();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['super_admin', 'admin_desa', 'admin_rw', 'admin_rt', 'viewer'])
                    ->default('viewer');
            }
            if (!Schema::hasColumn('users', 'desa_id')) {
                $table->foreignId('desa_id')->nullable()->constrained('desas')->onDelete('set null')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('users', 'rw_id')) {
                $table->foreignId('rw_id')->nullable()->constrained('rws')->onDelete('set null')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('users', 'rt_id')) {
                $table->foreignId('rt_id')->nullable()->constrained('rts')->onDelete('set null')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable();
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Indexes
            $table->index('desa_id');
            $table->index('rw_id');
            $table->index('rt_id');
            $table->index('is_active');
            $table->index('deleted_at');
            $table->index('last_login_at');

            // Composite index for scope filtering
            $table->index(['desa_id', 'rw_id', 'rt_id', 'is_active'], 'idx_user_scope');
        });

        // Note: Unique indexes and validation moved to application level
        // MariaDB doesn't support WHERE clause in indexes or REGEXP in CHECK constraints
        // Using application-level validation instead
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropConstrainedForeignId('desa_id');
            $table->dropConstrainedForeignId('rw_id');
            $table->dropConstrainedForeignId('rt_id');
            $table->dropColumn(['is_active', 'last_login_at', 'last_login_ip', 'password_changed_at', 'deleted_at']);
        });
    }
};
