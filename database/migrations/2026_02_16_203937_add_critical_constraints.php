<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ========================================
        // 1. UNIQUE CONSTRAINT: NIK Penduduk
        // ========================================
        if (!$this->indexExists('penduduks', 'penduduks_nik_unique')) {
            Schema::table('penduduks', function (Blueprint $table) {
                $table->unique('nik', 'penduduks_nik_unique');
            });
        }

        // ========================================
        // 2. KEPALA KELUARGA CONSTRAINT: 1 per KK
        // ========================================
        // MariaDB tidak support partial index, jadi kita pakai trigger
        
        // Drop trigger dulu jika sudah ada
        DB::unprepared('DROP TRIGGER IF EXISTS before_kk_member_insert_check_kepala');
        DB::unprepared('DROP TRIGGER IF EXISTS before_kk_member_update_check_kepala');

        // Trigger BEFORE INSERT
        DB::unprepared('
            CREATE TRIGGER before_kk_member_insert_check_kepala
            BEFORE INSERT ON kk_members
            FOR EACH ROW
            BEGIN
                DECLARE existing_kepala INT;
                
                IF NEW.is_kepala_keluarga = TRUE AND NEW.status = "AKTIF" THEN
                    SELECT COUNT(*) INTO existing_kepala
                    FROM kk_members
                    WHERE kartu_keluarga_id = NEW.kartu_keluarga_id
                      AND is_kepala_keluarga = TRUE
                      AND status = "AKTIF"
                      AND id != NEW.id;
                    
                    IF existing_kepala > 0 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "KK ini sudah memiliki kepala keluarga aktif";
                    END IF;
                END IF;
            END
        ');

        // Trigger BEFORE UPDATE
        DB::unprepared('
            CREATE TRIGGER before_kk_member_update_check_kepala
            BEFORE UPDATE ON kk_members
            FOR EACH ROW
            BEGIN
                DECLARE existing_kepala INT;
                
                IF NEW.is_kepala_keluarga = TRUE AND NEW.status = "AKTIF" THEN
                    SELECT COUNT(*) INTO existing_kepala
                    FROM kk_members
                    WHERE kartu_keluarga_id = NEW.kartu_keluarga_id
                      AND is_kepala_keluarga = TRUE
                      AND status = "AKTIF"
                      AND id != NEW.id;
                    
                    IF existing_kepala > 0 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "KK ini sudah memiliki kepala keluarga aktif";
                    END IF;
                END IF;
            END
        ');

        // ========================================
        // 3. CHECK CONSTRAINT: Surat Terbit Cancellation
        // ========================================
        try {
            $versionResult = DB::select('SELECT VERSION() as version');
            $mysqlVersion = $versionResult[0]->version ?? '10.0.0';
            
            // MariaDB 10.2.1+ support check constraint
            if (version_compare($mysqlVersion, '10.2.1', '>=')) {
                DB::statement('
                    ALTER TABLE surat_terbit 
                    ADD CONSTRAINT check_surat_cancellation 
                    CHECK (
                        (status = "BATAL" AND cancelled_by IS NOT NULL AND cancelled_at IS NOT NULL)
                        OR 
                        (status != "BATAL" AND cancelled_by IS NULL AND cancelled_at IS NULL)
                    )
                ');
            }
        } catch (\Exception $e) {
            // Skip if version check fails
        }

        // ========================================
        // 4. PERFORMANCE INDEXES
        // ========================================
        Schema::table('penduduks', function (Blueprint $table) {
            if (!$this->indexExists('penduduks', 'idx_penduduks_status')) {
                $table->index('status_kependudukan_code', 'idx_penduduks_status');
            }
            if (!$this->indexExists('penduduks', 'idx_penduduks_rt')) {
                $table->index('rt_id', 'idx_penduduks_rt');
            }
            if (!$this->indexExists('penduduks', 'idx_penduduks_rt_status')) {
                $table->index(['rt_id', 'status_kependudukan_code'], 'idx_penduduks_rt_status');
            }
        });

        Schema::table('kk_members', function (Blueprint $table) {
            if (!$this->indexExists('kk_members', 'idx_kk_members_kk_status')) {
                $table->index(['kartu_keluarga_id', 'status'], 'idx_kk_members_kk_status');
            }
            if (!$this->indexExists('kk_members', 'idx_kk_members_penduduk_status')) {
                $table->index(['penduduk_id', 'status'], 'idx_kk_members_penduduk_status');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (!$this->indexExists('events', 'idx_events_type_status')) {
                $table->index(['event_type_code', 'status_data'], 'idx_events_type_status');
            }
            if (!$this->indexExists('events', 'idx_events_penduduk_date')) {
                $table->index(['penduduk_id', 'event_date'], 'idx_events_penduduk_date');
            }
            if (!$this->indexExists('events', 'idx_events_rt_type')) {
                $table->index(['rt_id', 'event_type_code'], 'idx_events_rt_type');
            }
        });

        Schema::table('surat_terbit', function (Blueprint $table) {
            if (!$this->indexExists('surat_terbit', 'idx_surat_status_expired')) {
                $table->index(['status', 'tanggal_kadaluarsa'], 'idx_surat_status_expired');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penduduks', function (Blueprint $table) {
            $table->dropUnique('penduduks_nik_unique');
        });

        // Drop triggers instead of index
        DB::unprepared('DROP TRIGGER IF EXISTS before_kk_member_insert_check_kepala');
        DB::unprepared('DROP TRIGGER IF EXISTS before_kk_member_update_check_kepala');

        try {
            $versionResult = DB::select('SELECT VERSION() as version');
            $mysqlVersion = $versionResult[0]->version ?? '10.0.0';
            
            if (version_compare($mysqlVersion, '10.2.1', '>=')) {
                DB::statement('ALTER TABLE surat_terbit DROP CONSTRAINT IF EXISTS check_surat_cancellation');
            }
        } catch (\Exception $e) {
            // Skip
        }

        Schema::table('penduduks', function (Blueprint $table) {
            $table->dropIndex('idx_penduduks_status');
            $table->dropIndex('idx_penduduks_rt');
            $table->dropIndex('idx_penduduks_rt_status');
        });

        Schema::table('kk_members', function (Blueprint $table) {
            $table->dropIndex('idx_kk_members_kk_status');
            $table->dropIndex('idx_kk_members_penduduk_status');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_events_type_status');
            $table->dropIndex('idx_events_penduduk_date');
            $table->dropIndex('idx_events_rt_type');
        });

        Schema::table('surat_terbit', function (Blueprint $table) {
            $table->dropIndex('idx_surat_status_expired');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};