<?php

declare(strict_types=1);

namespace App\Actions\Event;

use App\DTOs\Event\KelahiranDTO;
use App\Models\Event;
use App\Models\EventKelahiran;
use App\Models\Penduduk;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\PendudukRepositoryInterface;
use App\Repositories\KkMemberRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEventKelahiranAction
{
    public function __construct(
        private EventRepositoryInterface $eventRepo,
        private PendudukRepositoryInterface $pendudukRepo,
        private KkMemberRepository $kkMemberRepo
    ) {}

    public function execute(KelahiranDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            // 1. Validate business rules
            $this->validateBusinessRules($dto);

            // 2. Get RT's RW and Desa
            $rt = \App\Models\Rt::with('rw')->findOrFail($dto->rtId);

            // 3. Create Event parent
            $eventData = $dto->toEventArray();
            $eventData['rw_id'] = $rt->rw_id;
            $eventData['desa_id'] = $rt->rw->desa_id;
            $eventData['kk_id'] = $dto->kkTujuanId;

            $event = $this->eventRepo->create($eventData);

            // 4. Create Event Kelahiran detail
            EventKelahiran::create($dto->toEventKelahiranArray($event->id));

            // 5. Create Penduduk for the baby
            $bayi = $this->createBayi($dto, $event, $rt->rw_id, $rt->rw->desa_id);

            // 6. Update event dengan penduduk_id
            $this->eventRepo->update($event, ['penduduk_id' => $bayi->id]);

            // 7. Add bayi to KK Members ONLY if bayi HIDUP
            if ($dto->statusKelahiran === \App\Enums\StatusKelahiran::HIDUP) {
                $this->addBayiToKk($bayi, $dto, $event);
            } else {
                Log::info('Bayi lahir mati - skip KK membership', [
                    'event_id' => $event->id,
                    'bayi_id' => $bayi->id,
                ]);
            }

            return $event->fresh();
        });
    }

    private function validateBusinessRules(KelahiranDTO $dto): void
    {
        // CRITICAL VALIDATION: Parent field consistency
        // This is DTO-level validation (can't be in FormRequest because need both fields)

        if ($dto->ayahId && $dto->namaAyah) {
            throw new \DomainException(
                'Tidak boleh mengisi nama_ayah jika ayah_id sudah dipilih. Nama akan diambil otomatis dari data penduduk.'
            );
        }

        if ($dto->ibuId && $dto->namaIbu) {
            throw new \DomainException(
                'Tidak boleh mengisi nama_ibu jika ibu_id sudah dipilih. Nama akan diambil otomatis dari data penduduk.'
            );
        }

        if (!$dto->ayahId && !$dto->namaAyah) {
            throw new \DomainException(
                'Data ayah wajib diisi. Pilih dari daftar penduduk atau isi nama ayah secara manual.'
            );
        }

        if (!$dto->ibuId && !$dto->namaIbu) {
            throw new \DomainException(
                'Data ibu wajib diisi. Pilih dari daftar penduduk atau isi nama ibu secara manual.'
            );
        }

        // Ayah/ibu (if selected by ID) must be AKTIF
        if ($dto->ayahId) {
            $ayah = $this->pendudukRepo->findById($dto->ayahId);
            if (!$ayah || $ayah->status_kependudukan_code !== 'AKTIF') {
                throw new \DomainException('Ayah yang dipilih harus berstatus AKTIF.');
            }

            // Validasi: RT ayah harus sama dengan RT event
            if ($ayah->rt_id != $dto->rtId) {
                throw new \DomainException('RT ayah harus sama dengan RT kelahiran.');
            }
        }

        if ($dto->ibuId) {
            $ibu = $this->pendudukRepo->findById($dto->ibuId);
            if (!$ibu || $ibu->status_kependudukan_code !== 'AKTIF') {
                throw new \DomainException('Ibu yang dipilih harus berstatus AKTIF.');
            }

            // Validasi: RT ibu harus sama dengan RT event
            if ($ibu->rt_id != $dto->rtId) {
                throw new \DomainException('RT ibu harus sama dengan RT kelahiran.');
            }
        }

        // KK validation
        $kk = \App\Models\KartuKeluarga::find($dto->kkTujuanId);
        if (!$kk || $kk->status_kk !== 'AKTIF') {
            throw new \DomainException('KK tujuan tidak ditemukan atau tidak aktif.');
        }

        // CRITICAL: Validasi KK harus di RT yang sama dengan event
        if ($kk->rt_id != $dto->rtId) {
            throw new \DomainException('KK tujuan harus berada di RT yang sama dengan event kelahiran.');
        }

        // CRITICAL: Validasi KK harus punya kepala keluarga aktif
        $hasKepala = $this->kkMemberRepo->hasKepalaKeluargaWithLock($dto->kkTujuanId);
        if (!$hasKepala) {
            throw new \DomainException(
                'KK yang dipilih tidak memiliki kepala keluarga aktif. ' .
                'Silakan perbaiki data KK terlebih dahulu sebelum mencatat kelahiran.'
            );
        }

        // Validasi: Ayah harus di KK yang sama dengan KK tujuan bayi
        if ($dto->ayahId) {
            $kkAyah = \App\Models\KkMember::where('penduduk_id', $dto->ayahId)
                ->where('status', 'AKTIF')
                ->first();

            if ($kkAyah && $kkAyah->kartu_keluarga_id != $dto->kkTujuanId) {
                throw new \DomainException('Ayah harus berada di KK yang sama dengan KK tujuan bayi.');
            }
        }

        // Validasi: Ibu harus di KK yang sama dengan KK tujuan bayi
        if ($dto->ibuId) {
            $kkIbu = \App\Models\KkMember::where('penduduk_id', $dto->ibuId)
                ->where('status', 'AKTIF')
                ->first();

            if ($kkIbu && $kkIbu->kartu_keluarga_id != $dto->kkTujuanId) {
                throw new \DomainException('Ibu harus berada di KK yang sama dengan KK tujuan bayi.');
            }
        }
    }

    private function createBayi(KelahiranDTO $dto, Event $event, int $rwId, int $desaId): Penduduk
    {
        return $this->pendudukRepo->create($dto->toPendudukBayiArray($event->id, $rwId, $desaId));
    }

    /**
     * Add bayi ke KK Members
     * 
     * FIXED LOGIC:
     * - Bayi SELALU jadi ANAK (bukan kepala keluarga)
     * - KK sudah divalidasi punya kepala aktif
     * - Skip kalau bayi lahir mati (dipanggil dari execute() dengan conditional)
     */
    private function addBayiToKk(Penduduk $bayi, KelahiranDTO $dto, Event $event): void
    {
        // Bayi selalu ANAK, never KEPALA_KELUARGA
        // Validasi KK punya kepala sudah dilakukan di validateBusinessRules()
        $this->kkMemberRepo->create([
            'kartu_keluarga_id' => $dto->kkTujuanId,
            'penduduk_id' => $bayi->id,
            'hubungan_keluarga_code' => 'ANAK',
            'is_kepala_keluarga' => false,
            'tanggal_masuk' => $dto->eventDate->format('Y-m-d'),
            'status' => 'AKTIF',
            'created_by' => $dto->createdBy,
        ]);

        Log::info('Bayi ditambahkan ke KK sebagai anak', [
            'event_id' => $event->id,
            'bayi_id' => $bayi->id,
            'kk_id' => $dto->kkTujuanId,
        ]);
    }
}
