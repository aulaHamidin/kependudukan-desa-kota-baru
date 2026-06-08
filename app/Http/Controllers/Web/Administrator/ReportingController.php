<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\ReportFilterRequest;
use App\Models\EventType;
use App\Models\Rt;
use App\Models\User;
use App\Services\ReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Reports\PendudukAktifExport;
use App\Exports\Reports\KkWithMembersExport;
use App\Exports\Reports\DataInconsistencyExport;
use App\Exports\Reports\EventsExport;

class ReportingController extends Controller
{
    public function __construct(private ReportingService $service)
    {
    }

    public function index(ReportFilterRequest $request)
    {
        $type = $request->input('type', 'penduduk');
        $filters = $request->validated();
        $perPage = (int) ($request->input('per_page', 15));
        /** @var User $user */
        $user = $request->user();

        [$query, $viewName] = $this->resolveQuery($type, $filters, $user);

        $data = $query->paginate($perPage)->withQueryString();

        $rts = $this->rtOptions($user);
        $eventTypes = EventType::where('is_active', true)->orderBy('nama')->get();

        return view('administrator.reporting.index', [
            'type' => $type,
            'data' => $data,
            'rts' => $rts,
            'eventTypes' => $eventTypes,
            'filters' => $filters,
            'viewName' => $viewName,
        ]);
    }

    public function export(ReportFilterRequest $request, string $type, string $format)
    {
        abort_unless(in_array($type, ['penduduk', 'kk', 'inconsistency', 'events'], true), 404);

        $filters = $request->validated();
        $user = $request->user();

        [$query, $viewName] = $this->resolveQuery($type, $filters, $user);

        $filename = sprintf('%s-report-%s.%s', $type, now()->format('Ymd_His'), $format);

        if ($format === 'xlsx') {
            return match ($type) {
                'penduduk' => Excel::download(new PendudukAktifExport($query), $filename),
                'kk' => Excel::download(new KkWithMembersExport($query), $filename),
                'inconsistency' => Excel::download(new DataInconsistencyExport($query), $filename),
                'events' => Excel::download(new EventsExport($query), $filename),
            };
        }

        // PDF
        $collection = $query->limit(2000)->get(); // hard limit to avoid OOM
        
        $payload = [
            'data' => $collection,
            'printedAt' => now(),
            'user' => $user,
            'filters' => $filters,
        ];

        return Pdf::loadView("administrator.reporting.pdf.{$viewName}", $payload)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:string}
     */
    private function resolveQuery(string $type, array $filters, User $user): array
    {
        return match ($type) {
            'kk' => [$this->service->kkQuery($filters, $user), 'kk'],
            'inconsistency' => [$this->service->inconsistencyQuery($filters, $user), 'inconsistency'],
            'events' => [$this->service->eventsQuery($filters, $user), 'events'],
            default => [$this->service->pendudukQuery($filters, $user), 'penduduk'],
        };
    }

    private function rtOptions(User $user): array
    {
        return Rt::query()
            ->whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id))
            ->with('rw')
            ->orderBy('nomor_rt')
            ->get()
            ->mapWithKeys(fn($rt) => [
                $rt->id => 'RT ' . $rt->nomor_rt . ' / RW ' . ($rt->rw?->nomor_rw ?? '-'),
            ])
            ->all();
    }
}
