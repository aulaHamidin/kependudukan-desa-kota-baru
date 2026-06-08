<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('actor_type', 20)->nullable()->after('user_id');
            $table->unsignedBigInteger('actor_id')->nullable()->after('actor_type');
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE audit_logs MODIFY user_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE audit_logs ALTER COLUMN user_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE audit_logs MODIFY user_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE audit_logs ALTER COLUMN user_id SET NOT NULL');
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['actor_type', 'actor_id']);
        });
    }
};
