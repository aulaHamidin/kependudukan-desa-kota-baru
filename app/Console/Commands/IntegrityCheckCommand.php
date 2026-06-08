<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penduduk;
use App\Models\KkMember;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\EventDatang;

class IntegrityCheckCommand extends Command
{
    protected $signature = 'integrity:check';
    protected $description = 'Check data integrity for penduduk, KK, event, and membership';

    public function handle()
    {
        $this->checkUniqueKepala();
        $this->checkMembershipOrphan();
        $this->checkEventWithoutPenduduk();
        $this->checkRestoredFromIdConsistency();
        $this->checkChronologicalViolation();
        $this->info('Integrity check completed.');
    }

    private function checkUniqueKepala()
    {
        $kkIds = KartuKeluarga::pluck('id');
        foreach ($kkIds as $kkId) {
            $kepalaCount = KkMember::where('kartu_keluarga_id', $kkId)
                ->where('is_kepala_keluarga', true)
                ->where('status', 'AKTIF')
                ->count();
            if ($kepalaCount > 1) {
                $this->error("KK $kkId has $kepalaCount active kepala keluarga!");
            }
        }
    }

    private function checkMembershipOrphan()
    {
        $orphans = KkMember::whereDoesntHave('penduduk')->get();
        foreach ($orphans as $member) {
            $this->error("KKMember ID {$member->id} orphan (no penduduk)");
        }
    }

    private function checkEventWithoutPenduduk()
    {
        $events = Event::whereNull('penduduk_id')->get();
        foreach ($events as $event) {
            $this->error("Event ID {$event->id} has no penduduk");
        }
    }

    private function checkRestoredFromIdConsistency()
    {
        $datangs = EventDatang::whereNotNull('restored_from_id')->get();
        foreach ($datangs as $datang) {
            $restored = Penduduk::withTrashed()->find($datang->restored_from_id);
            if (!$restored) {
                $this->error("EventDatang ID {$datang->id} restored_from_id {$datang->restored_from_id} not found");
            }
        }
    }

    private function checkChronologicalViolation()
    {
        $penduduks = Penduduk::all();
        foreach ($penduduks as $penduduk) {
            $events = Event::where('penduduk_id', $penduduk->id)
                ->where('status_data', 'VERIFIED')
                ->orderBy('event_date')
                ->get();
            $lastDate = null;
            foreach ($events as $event) {
                if ($lastDate && $event->event_date < $lastDate) {
                    $this->error("Penduduk ID {$penduduk->id} has chronological violation: event {$event->id} date {$event->event_date} < previous {$lastDate}");
                }
                $lastDate = $event->event_date;
            }
        }
    }
}
