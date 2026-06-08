Audit Report

FILE: app/Http/Controllers/Web/PendudukController.php:124-145
LINE: 124
SEVERITY: Critical
ISSUE: Update action performs no authorization; relies on FormRequest that returns true, so any authenticated role (even viewer) can update penduduk data.
IMPACT: Unauthorized users can tamper with resident records across the village if the route is reachable; multi-tenancy and role model are bypassed.
FIX: Add policy check before service call:

$penduduk->load(['rt.rw.desa']);
$this->authorize('update', $penduduk);
and keep the FormRequest authorization aligned with the policy.

FILE: app/Http/Requests/Penduduk/UpdatePendudukRequest.php:21-36
LINE: 21
SEVERITY: High
ISSUE: authorize() always returns true and $penduduk from route remains mixed, leading to the phpstan (object|string)::$id warning and no territory/policy guard.
IMPACT: Request can pass validation without verifying the model or user permission; policy not triggered if controller forgets (as above).
FIX: Enforce policy and narrow type:

public function authorize(): bool {
    $penduduk = $this->route('penduduk');
    if (!$penduduk instanceof \App\Models\Penduduk) {
        return false;
    }
    $penduduk->load('rt.rw');
    return $this->user()?->can('update', $penduduk) ?? false;
}
and keep $pendudukId = $penduduk->getKey(); to satisfy phpstan.

FILE: app/Services/DashboardService.php (e.g., 35-188, 231-255) & app/Http/Controllers/Web/DashboardController.php:24-30
LINE: 35
SEVERITY: Critical
ISSUE: All dashboard queries run without any user/territory scoping; controller calls getDashboardWidgets() without passing the actor.
IMPACT: Admin RT/RW/viewer can see global counts (events, surat, KK, inconsistencies) across all desa, breaking tenancy isolation and exposing sensitive data.
FIX: Accept User $user in service methods (or add forTerritory($user) scopes) and apply territory filters (rt/rw/desa) consistently; update controller to pass the authenticated user.

FILE: app/Services/ReportingService.php:103-138
LINE: 103
SEVERITY: High
ISSUE: eventsQuery() only filters by desa_id; admin_rw/admin_rt receive all events in the desa by default. No reuse of rtIdsForUser() unlike other queries.
IMPACT: RT/RW operators can view events outside their own RT/RW, violating role-based data boundaries.
FIX: Intersect with allowed RTs:

$rtIds = $this->rtIdsForUser($user);
$query = Event::with(['penduduk','rt.rw'])->whereIn('rt_id', $rtIds);
and keep extra filters on top.

FILE: app/Services/DashboardService.php:231-249
LINE: 231
SEVERITY: High
ISSUE: getEventsByMonth() selects MONTH(event_date) as month, COUNT(*) as total then iterates as arrays; phpstan flags Event::$month/$total, and no territory filter.
IMPACT: Static analysis failure and potential wrong casting; plus data leak noted above.
FIX: Both issues at once:

public function getEventsByMonth(User $user): array {
    $stats = Event::query()
        ->applyTerritoryFilter($user) // or whereIn rt/rw/desa
        ->selectRaw('MONTH(event_date) as month, COUNT(*) as total')
        ->where('status_data','VERIFIED')
        ->whereBetween('event_date',[now()->startOfYear(), now()->endOfYear()])
        ->pluck('total','month');
    ...
}
FILE: app/Services/DashboardService.php:175-184
LINE: 175
SEVERITY: Medium
ISSUE: Uses DATE_FORMAT(tanggal_terbit, '%Y-%m') in where clause.
IMPACT: MySQL cannot use index on tanggal_terbit; monthly stats scan the whole table as data grows.
FIX: Replace with range predicate:

$start = now()->startOfMonth();
$end   = now()->endOfMonth();
SuratTerbit::where('status','AKTIF')
    ->whereBetween('tanggal_terbit', [$start, $end])
    ->count();
FILE: app/Traits/ValidatesTerritory.php:100-135, 140-158
LINE: 100
SEVERITY: Medium
ISSUE: Accesses $model->getAttribute('id') and throws RuntimeException if relations aren’t preloaded, causing phpstan Model::$id error and 500s when a controller forgets to eager load.
IMPACT: Production 500 instead of clean 403; brittle to developer misuse.
FIX: Use safe access and lazy load:

$model->loadMissing('rt.rw');
$modelId = $model->getKey();
and return false with a clear authorization message instead of throwing runtime exceptions.

FILE: app/Http/Requests/KartuKeluarga/UpdateKartuKeluargaRequest.php:12-22
LINE: 12
SEVERITY: Low
ISSUE: $this->route('kartu_keluarga') is mixed; phpstan warns (object|string)::load().
IMPACT: Static analysis noise and potential false negatives if route binding fails.
FIX: Guard and type-narrow:

$kk = $this->route('kartu_keluarga');
if (!$kk instanceof \App\Models\KartuKeluarga) return false;
$kk->load(['rt.rw']);
return $this->user()?->can('update', $kk) ?? false;
FILE: app/Http/Controllers/Web/Administrator/ReportingController.php:26-48, 51-84
LINE: 26
SEVERITY: Low
ISSUE: $user = $request->user(); is not asserted as App\Models\User, causing phpstan “Authenticatable::$desa_id” warning; $format is unchecked (any string → PDF).
IMPACT: Static analysis failure; unexpected extensions can be generated and logged.
FIX:

$user = $request->user();
if (!$user instanceof \App\Models\User) abort(401);
abort_unless(in_array($format, ['pdf','xlsx'], true), 422);
FILE: app/Services/DashboardService.php & app/Exports paths (general)
LINE: 71-84
SEVERITY: Low
ISSUE: PDF export caps at 2000 rows but XLSX export streams whole query without chunking.
IMPACT: Large exports can exhaust memory and block workers.
FIX: For Excel, use ->queue() with chunked queries or FromQuery + ->chunkSize(1000) and add maximum allowed rows per role.