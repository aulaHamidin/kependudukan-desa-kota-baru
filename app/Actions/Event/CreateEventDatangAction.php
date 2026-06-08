<?php

declare(strict_types = 1)
;

namespace App\Actions\Event;

use App\DTOs\Event\DatangDTO;
use App\Models\Event;
use App\Models\EventDatang;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Repositories\PendudukRepository;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEventDatangAction
{
    private $pendudukRepo;

    public function __construct(PendudukRepository $pendudukRepo)
    {
        $this->pendudukRepo = $pendudukRepo;
    }

    /**
     * Execute event datang creation
     *
     * CRITICAL: Wrapped in transaction with pessimistic locking
     * Handles both new penduduk and soft-deleted restore
     *
     * Territory resolution (Option B - same pattern as CreateEventKelahiranAction):
     * rwId & desaId di-resolve di sini dari rtId, bukan di DTO
     */
    public function execute(DatangDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            // Resolve territory dari RT (same pattern as KelahiranAction)
            $rt = Rt::with('rw')->findOrFail($dto->rtId);

            // 1. Create Event record
            $event = Event::create([
                'event_type_code' => 'DATANG',
                'penduduk_id' => null, // Set setelah penduduk created/restored
                'event_date' => $dto->tanggalDatang->format('Y-m-d'),
                'keterangan' => $dto->keterangan,
                'rt_id' => $dto->rtId,
                'rw_id' => $rt->rw_id, // Resolved dari RT
                'desa_id' => $rt->rw->desa_id, // Resolved dari RT → RW
                'kk_id' => $dto->kkTujuanId > 0 ? $dto->kkTujuanId : null,
                'status_data' => 'DRAFT',
                'created_by' => $dto->createdBy,
            ]);

            // 2. Create EventDatang details
            EventDatang::create([
                'event_id' => $event->id,
                'alamat_asal' => $dto->alamatAsal,
                'rt_asal' => $dto->rtAsal,
                'rw_asal' => $dto->rwAsal,
                'desa_asal' => $dto->desaAsal,
                'kecamatan_asal' => $dto->kecamatanAsal,
                'kabupaten_asal' => $dto->kabupatenAsal,
                'provinsi_asal' => $dto->provinsiAsal,
                'alasan_datang' => $dto->alasanDatang,
                'keterangan_alasan' => $dto->keteranganAlasan,
                'jenis_kedatangan' => $dto->jenisKedatangan,
                'kk_tujuan_id' => $dto->kkTujuanId > 0 ? $dto->kkTujuanId : null,
                'no_surat_pindah' => $dto->noSuratPindah,
                'tanggal_surat_pindah' => $dto->tanggalSuratPindah ? $dto->tanggalSuratPindah->format('Y-m-d') : null,
                // Track restore untuk void rollback
                'restored_from_id' => $dto->payload['_restored_from_id'] ?? null,
            ]);

            // 3. Handle Penduduk (create new OR restore soft-deleted)
            $penduduk = $this->handlePenduduk($dto, $event);

            // 4. Update event dengan penduduk_id
            $event->update(['penduduk_id' => $penduduk->id]);

            // 5. Add to KK if specified
            if ($dto->kkTujuanId > 0) {
                $this->addToKartuKeluarga($penduduk, $dto, $event);
            }

            return $event->fresh(['eventDatang', 'penduduk', 'rt.rw.desa', 'kartuKeluarga']);
        });
    }

    /**
     * Handle Penduduk creation or restoration
     */
    private function handlePenduduk(DatangDTO $dto, Event $event): Penduduk
    {
        if ($dto->isPendatangBaru()) {
            // RESTORE PATTERN: Service set flag jika NIK ada di deleted records
            if (isset($dto->payload['_restore_penduduk_id'])) {
                return $this->restorePenduduk(
                    $dto->payload['_restore_penduduk_id'],
                    $dto,
                    $event
                );
            }

            return $this->createNewPenduduk($dto, $event);
        }

        // KEMBALI / PINDAH_MASUK: reactivate existing
        return $this->reactivatePenduduk($dto, $event);
    }

    /**
     * CRITICAL: Restore soft-deleted penduduk
     * Prevents "Duplicate entry for key 'nik'" error
     */
    private function restorePenduduk(int $id, DatangDTO $dto, Event $event): Penduduk
    {
        $penduduk = Penduduk::withTrashed()
            ->lockForUpdate()
            ->findOrFail($id);

        Log::info('Restoring soft-deleted penduduk', [
            'penduduk_id' => $id,
            'nik' => $penduduk->nik,
            'event_id' => $event->id,
            'deleted_at' => $penduduk->deleted_at,
        ]);

        $penduduk->restore();

        $this->pendudukRepo->update($penduduk, [
            'nama_lengkap' => $dto->namaLengkap,
            'jenis_kelamin' => $dto->jenisKelamin,
            'tempat_lahir' => $dto->tempatLahir,
            // FIXED: null check sebelum format()
            'tgl_lahir' => $dto->tglLahir ? $dto->tglLahir->format('Y-m-d') : null,
            'agama_id' => $dto->agamaId,
            'pendidikan_id' => $dto->pendidikanId,
            'pekerjaan_id' => $dto->pekerjaanId,
            'rt_id' => $dto->rtId,
            'nama_ayah' => $dto->namaAyah,
            'nama_ibu' => $dto->namaIbu,
            'pendapatan_range_id' => $dto->pendapatanRangeId,
            'golongan_darah_id' => $dto->golonganDarahId,
            'no_hp' => $dto->noHp,
            'email' => $dto->email,
            // Set status langsung aktif dengan event terkait
            'status_kependudukan_code' => 'AKTIF',
            'current_event_id' => $event->id,
            'tanggal_status' => $dto->tanggalDatang->format('Y-m-d'),
            'updated_by' => $dto->createdBy,
        ]);

        return $penduduk->fresh();
    }

    /**
     * Create new penduduk untuk PENDATANG_BARU
     */
    private function createNewPenduduk(DatangDTO $dto, Event $event): Penduduk
    {
        return $this->pendudukRepo->create([
            'nik' => $dto->nik,
            'nama_lengkap' => $dto->namaLengkap,
            'jenis_kelamin' => $dto->jenisKelamin,
            'tempat_lahir' => $dto->tempatLahir,
            // FIXED: null check sebelum format()
            'tgl_lahir' => $dto->tglLahir ? $dto->tglLahir->format('Y-m-d') : null,
            'agama_id' => $dto->agamaId,
            'pendidikan_id' => $dto->pendidikanId,
            'pekerjaan_id' => $dto->pekerjaanId,
            'rt_id' => $dto->rtId,
            'nama_ayah' => $dto->namaAyah,
            'nama_ibu' => $dto->namaIbu,
            'pendapatan_range_id' => $dto->pendapatanRangeId,
            'golongan_darah_id' => $dto->golonganDarahId,
            'no_hp' => $dto->noHp,
            'email' => $dto->email,
            'status_kependudukan_code' => 'AKTIF',
            'current_event_id' => $event->id,
            'tanggal_status' => $dto->tanggalDatang->format('Y-m-d'),
            'created_by' => $dto->createdBy,
        ]);
    }

    /**
     * Reactivate existing penduduk untuk KEMBALI
     */
    /**
     * Reaktivasi penduduk yang berstatus PINDAH menjadi AKTIF.
     *
     * D3: Fungsi ini HANYA mengizinkan reaktivasi dari status PINDAH → AKTIF.
     * Penduduk dengan status lain (MENINGGAL, AKTIF, dll) akan di-reject.
     * Ini by design karena flow KEMBALI hanya berlaku untuk penduduk yang sebelumnya
     * pindah keluar dan kembali ke desa.
     *
     * Jika ada kebutuhan reaktivasi dari status lain (misal data lama), perlu
     * dibuat flow terpisah atau perbaikan data manual.
     */
    private function reactivatePenduduk(DatangDTO $dto, Event $event): Penduduk
    {
        $penduduk = Penduduk::lockForUpdate()->findOrFail($dto->pendudukId);

        if ($penduduk->status_kependudukan_code !== 'PINDAH') {
            throw new DomainException(
                "Hanya penduduk berstatus PINDAH yang dapat dibuatkan event DATANG KEMBALI. "
                . "Status saat ini: {$penduduk->status_kependudukan_code}."
                );
        }

        // NEW-5: Check active event SETELAH lock — atomic & aman dari race condition.
        // Sebelumnya check ini ada di DatangService::validateBusinessRules() (luar transaksi)
        // yang rentan terhadap race condition concurrent request.
        $hasActiveEvent = Event::where('penduduk_id', $penduduk->id)
            ->whereIn('status_data', Event::ACTIVE_STATUSES)
            ->exists();

        if ($hasActiveEvent) {
            throw new DomainException('Penduduk masih memiliki event aktif (DRAFT) yang belum diverifikasi.');
        }

        // UC-F1: Validasi chronological integrity.
        // Tanggal datang tidak boleh sebelum event VERIFIED terakhir (biasanya event PINDAH).
        // inferPreviousStatus() pakai urutan ID, bukan event_date — backdated event mengacaukan rollback.
        $lastVerifiedEvent = Event::where('penduduk_id', $penduduk->id)
            ->where('status_data', 'VERIFIED')
            ->orderByDesc('id')
            ->first();

        if ($lastVerifiedEvent && $dto->tanggalDatang->lt($lastVerifiedEvent->event_date)) {
            throw new DomainException(
                'Tanggal datang (' . $dto->tanggalDatang->format('d/m/Y') . ') '
                . 'tidak boleh sebelum event terverifikasi terakhir penduduk '
                . '(' . $lastVerifiedEvent->event_date->format('d/m/Y') . ').',
                );
        }

        $this->pendudukRepo->update($penduduk, [
            'rt_id' => $dto->rtId,
            'status_kependudukan_code' => 'AKTIF',
            'current_event_id' => $event->id,
            'tanggal_status' => $dto->tanggalDatang->format('Y-m-d'),
            'updated_by' => $dto->createdBy,
        ]);

        return $penduduk->fresh();
    }

    /**
     * Add penduduk ke Kartu Keluarga
     */
    private function addToKartuKeluarga(Penduduk $penduduk, DatangDTO $dto, Event $event): void
    {
        // D2: Validasi KK tujuan punya kepala aktif sebelum tambah anggota
        $hasKepala = KkMember::where('kartu_keluarga_id', $dto->kkTujuanId)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->exists();

        if (!$hasKepala) {
            throw new DomainException(
                'KK tujuan tidak memiliki kepala keluarga aktif. Pastikan KK memiliki kepala sebelum menambahkan anggota.'
                );
        }

        $existingMember = KkMember::where('kartu_keluarga_id', $dto->kkTujuanId)
            ->where('penduduk_id', $penduduk->id)
            ->where('status', 'AKTIF')
            ->exists();

        if ($existingMember) {
            Log::warning('Penduduk already member of KK', [
                'penduduk_id' => $penduduk->id,
                'kk_id' => $dto->kkTujuanId,
            ]);
            return;
        }

        KkMember::create([
            'kartu_keluarga_id' => $dto->kkTujuanId,
            'penduduk_id' => $penduduk->id,
            // FIXED: sekarang dari DTO, fallback ke LAINNYA
            'hubungan_keluarga_code' => $dto->hubunganKeluargaCode ?? 'LAINNYA',
            'is_kepala_keluarga' => false,
            'tanggal_masuk' => $dto->tanggalDatang->format('Y-m-d'),
            'status' => 'AKTIF',
            'created_by' => $dto->createdBy,
        ]);
    }
}