<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add composite index (status_data, event_type_code) on events table.
 *
 * The existing idx_event_type_status covers (event_type_code, status_data)
 * which optimises queries filtering by type first.
 *
 * This reverse-order index optimises queries that filter by status_data first,
 * e.g. "all DRAFT events regardless of type" or dashboard widgets counting
 * events by status across multiple types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(
                ['status_data', 'event_type_code'],
                'idx_event_status_type'
            );
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_event_status_type');
        });
    }
};
