<?php

declare(strict_types = 1)
;

namespace App\Actions\Event;

use App\DTOs\Event\KematianDTO;
use App\Models\Event;
use App\Models\EventKematian;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Repositories\KkMemberRepository;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEventKematianAction
{
    public function __construct(private
        KkMemberRepository $kkMemberRepo
        )
    {
    }

    public function execute(KematianDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            $rt = Rt::with('rw')->findOrFail($dto->rtId);

            // CRITICAL: Lock penduduk dulu, BARU check active event.
            // Urutan ini mencegah race condition di mana dua request concurrent
            // sama-sama lolos check sebelum salah satu commit.
            $almarhum = Penduduk::lockForUpdate()->findOrFail($dto->pendudukId);

            if ($almarhum->status_kependudukan_code !== 'AKTIF') {
                throw new DomainException('Penduduk tidak dapat dicatat meninggal karena status bukan AKTIF.');
            }

            // Check active event SETELAH lock — atomic & aman dari race condition.
            $hasActiveEvent = Event::where('penduduk_id', $almarhum->id)
                ->whereIn('status_data', Event::ACTIVE_STATUSES)
                ->exists();

            if ($hasActiveEvent) {
                throw new DomainException('Penduduk masih memiliki event aktif (DRAFT) yang belum diverifikasi.');
            }

            // UC-F1: Validasi chronological integrity.
            // event_date tidak boleh sebelum tanggal event VERIFIED terakhir untuk penduduk ini.
            // inferPreviousStatus() menggunakan urutan ID (insertion order), bukan event_date.
            // Backdated event menyebabkan status yang salah saat void.
            $lastVerifiedEvent = Event::where('penduduk_id', $almarhum->id)
                ->where('status_data', 'VERIFIED')
                ->orderByDesc('id')
                ->first();

            if ($lastVerifiedEvent && $dto->eventDate->lt($lastVerifiedEvent->event_date)) {
                throw new DomainException(
                    'Tanggal event tidak valid. Tanggal kematian (' . $dto->eventDate->format('d/m/Y') . ') '
                    . 'tidak boleh sebelum event terverifikasi terakhir penduduk '
                    . '(' . $lastVerifiedEvent->event_date->format('d/m/Y') . ').',
                    );
            }

            // 1. Create Event parent
            $event = Event::create([
                'event_type_code' => 'KEMATIAN',
                'penduduk_id' => $almarhum->id,
                'event_date' => $dto->eventDate->format('Y-m-d'),
                'keterangan' => $dto->keterangan,
                'rt_id' => $dto->rtId,
                'rw_id' => $rt->rw_id,
                'desa_id' => $rt->rw->desa_id,
                'kk_id' => $dto->kkId,
                'status_data' => 'DRAFT',
                'created_by' => $dto->createdBy,
            ]);

            // 2. Close KK membership & ambil snapshot was_kepala SEBELUM di-update
            $wasKepala = false;
            if ($dto->kkId) {
                $wasKepala = $this->closeKkMembership($almarhum, $dto, $event);
            }

            // 3. Create EventKematian detail — simpan was_kepala & pengganti_id
            //    agar deleteEventKematian bisa rollback dengan presisi
            EventKematian::create([
                'event_id' => $event->id,
                'tempat_meninggal' => $dto->tempatMeninggal,
                'jam_meninggal' => $dto->jamMeninggal,
                'sebab_kematian' => $dto->sebabKematian,
                'penyakit' => $dto->penyakit,
                'keterangan_kematian' => $dto->keteranganKematian,
                'was_kepala' => $wasKepala,
                'pengganti_id' => $dto->penggantiKepalaId,
                'pelapor_id' => $dto->pelaporId,
                'nama_pelapor' => $dto->namaPelapor,
                'hubungan_pelapor_code' => $dto->hubunganPelaporCode,
            ]);

            // 4. Update status almarhum → MENINGGAL
            $almarhum->update([
                'status_kependudukan_code' => 'MENINGGAL',
                'current_event_id' => $event->id,
                'tanggal_status' => $dto->eventDate->format('Y-m-d'),
                'updated_by' => $dto->createdBy,
            ]);

            // 5. Handle kepala keluarga & KK activation
            if ($dto->kkId) {
                if ($dto->penggantiKepalaId) {
                    $this->setPenggantiKepala($dto, $event);
                }
                else {
                    $this->deactivateKkIfEmpty($dto->kkId);
                }
            }

            Log::info('Event Kematian created', [
                'event_id' => $event->id,
                'penduduk_id' => $almarhum->id,
                'kk_id' => $dto->kkId,
                'was_kepala' => $wasKepala,
                'pengganti_id' => $dto->penggantiKepalaId,
            ]);

            return $event->fresh(['eventKematian', 'penduduk', 'rt.rw.desa']);
        });
    }

    /**
     * Tutup membership almarhum di KK.
     *
     * @return bool Nilai is_kepala_keluarga almarhum SEBELUM ditutup (snapshot untuk rollback)
     */
    private function closeKkMembership(Penduduk $almarhum, KematianDTO $dto, Event $event): bool
    {
        $membership = KkMember::where('penduduk_id', $almarhum->id)
            ->where('kartu_keluarga_id', $dto->kkId)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->first();

        if (!$membership) {
            Log::warning('closeKkMembership: membership aktif tidak ditemukan', [
                'penduduk_id' => $almarhum->id,
                'kk_id' => $dto->kkId,
                'event_id' => $event->id,
            ]);
            return false;
        }

        // Simpan snapshot SEBELUM di-update
        $wasKepala = (bool)$membership->is_kepala_keluarga;

        $membership->update([
            'status' => 'KELUAR',
            'tanggal_keluar' => $dto->eventDate->format('Y-m-d'),
            'event_keluar_id' => $event->id,
            'alasan_keluar' => 'Meninggal dunia',
            'is_kepala_keluarga' => false,
        ]);

        return $wasKepala;
    }

    private function setPenggantiKepala(KematianDTO $dto, Event $event): void
    {
        $hasKepala = $this->kkMemberRepo->hasKepalaKeluargaWithLock($dto->kkId);

        if ($hasKepala) {
            // M2: Race condition — KK sudah punya kepala aktif setelah lock.
            // Throw exception daripada silent return agar tidak menyimpan
            // pengganti_id yang tidak benar-benar di-set sebagai kepala.
            throw new DomainException(
                'KK sudah memiliki kepala aktif, tidak dapat set pengganti. Kemungkinan race condition.'
                );
        }

        $penggantiMembership = KkMember::where('penduduk_id', $dto->penggantiKepalaId)
            ->where('kartu_keluarga_id', $dto->kkId)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->first();

        if (!$penggantiMembership) {
            throw new DomainException('Pengganti kepala keluarga tidak ditemukan sebagai anggota aktif KK.');
        }

        $penggantiMembership->update(['is_kepala_keluarga' => true]);
    }

    private function deactivateKkIfEmpty(int $kkId): void
    {
        // Lock KK row sebelum check member — cegah race condition dua anggota
        // terakhir meninggal bersamaan yang bisa double-deactivate atau miss update.
        $kk = KartuKeluarga::lockForUpdate()->find($kkId);

        if (!$kk)
            return;

        $hasActiveMembers = KkMember::where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->exists();

        if (!$hasActiveMembers) {
            $kk->update(['status_kk' => 'NON_AKTIF']);
        }
    }
}