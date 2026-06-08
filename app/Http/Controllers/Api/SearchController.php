<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\KartuKeluarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SearchController - AJAX endpoints for searchable dropdowns
 * 
 * Used by Tom Select components for remote search.
 * All endpoints require authentication via web session.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class SearchController extends Controller
{
    /**
     * Search penduduk by nama or NIK
     * 
     * @queryParam q string Search query (nama or NIK)
     * @queryParam id int Get specific penduduk by ID (for pre-loading selected value)
     * @queryParam limit int Max results (default: 20)
     * @queryParam exclude_has_kk bool Exclude penduduk yang sudah punya KK aktif (default: false)
     * 
     * @return JsonResponse
     */
    public function penduduk(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $id = $request->get('id');
        $limit = min((int) $request->get('limit', 20), 50);
        $statusFilter = $request->get('status') === 'pindah' ? 'PINDAH' : 'AKTIF';
        $excludeHasKk = filter_var($request->get('exclude_has_kk', false), FILTER_VALIDATE_BOOLEAN);

        $jenisKelamin = $request->get('jenis_kelamin'); // L atau P

        $builder = Penduduk::query()
            ->with(['rt:id,nomor_rt,rw_id', 'rt.rw:id,nomor_rw'])
            ->forTerritory(Auth::user())
            ->where('status_kependudukan_code', $statusFilter);

        // Filter berdasarkan jenis kelamin (digunakan oleh form kelahiran)
        if ($jenisKelamin) {
            $builder->where('jenis_kelamin', $jenisKelamin);
        }

        // Exclude penduduk yang sudah punya KK aktif
        if ($excludeHasKk) {
            $builder->whereDoesntHave('kkMembers', function ($q) {
                $q->where('status', 'AKTIF');
            });
        }

        // If ID is provided, return that specific penduduk
        if ($id) {
            $penduduk = $builder->find($id);

            if (!$penduduk) {
                return response()->json(['data' => []]);
            }

            return response()->json([
                'data' => [
                    $this->formatPendudukResult($penduduk),
                ]
            ]);
        }

        // Search by nama or NIK
        if (strlen($query) >= 2) {
            $builder->where(function ($q) use ($query) {
                $q->where('nama_lengkap', 'LIKE', "%{$query}%")
                    ->orWhere('nik', 'LIKE', "%{$query}%");
            });
        }

        $results = $builder
            ->orderBy('nama_lengkap')
            ->limit($limit)
            ->get()
            ->map(fn($p) => $this->formatPendudukResult($p))
            ->toArray();

        return response()->json(['data' => $results]);
    }

    /**
     * Search kartu keluarga by no_kk or kepala keluarga name
     */
    public function kartuKeluarga(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $id = $request->get('id');
        $rtId = $request->get('rt_id'); // Filter by RT
        $limit = min((int) $request->get('limit', 20), 50);

        $builder = KartuKeluarga::query()
            ->with(['kepalaKeluarga.penduduk:id,nama_lengkap,nik', 'rt:id,nomor_rt,rw_id', 'rt.rw:id,nomor_rw'])
            ->forTerritory(Auth::user())
            ->where('status_kk', 'AKTIF'); // Only show active KK

        // Filter by RT if provided (for consistency with penduduk RT)
        if ($rtId) {
            $builder->where('rt_id', $rtId);
        }

        if ($id) {
            $kk = $builder->find($id);

            if (!$kk) {
                return response()->json(['data' => []]);
            }

            return response()->json([
                'data' => [
                    $this->formatKKResult($kk),
                ]
            ]);
        }

        if (strlen($query) >= 2) {
            $builder->where(function ($q) use ($query) {
                // Search by no_kk
                $q->where('kartu_keluargas.no_kk', 'LIKE', "%{$query}%")
                    // Search by kepala keluarga name using join (more efficient than whereHas)
                    ->orWhereExists(function ($subquery) use ($query) {
                        $subquery->select(DB::raw(1))
                            ->from('kk_members')
                            ->join('penduduks', 'kk_members.penduduk_id', '=', 'penduduks.id')
                            ->whereColumn('kk_members.kartu_keluarga_id', 'kartu_keluargas.id')
                            ->where('kk_members.is_kepala_keluarga', true)
                            ->where('kk_members.status', 'AKTIF')
                            ->where('penduduks.nama_lengkap', 'LIKE', "%{$query}%");
                    });
            });
        }

        $results = $builder
            ->orderBy('no_kk')
            ->limit($limit)
            ->get()
            ->map(fn($kk) => $this->formatKKResult($kk))
            ->toArray();

        return response()->json(['data' => $results]);
    }

    /**
     * Search jenis surat by nama or kode
     */
    public function jenisSurat(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $id = $request->get('id'); // kode for JenisSurat
        $limit = min((int) $request->get('limit', 50), 100);

        $builder = JenisSurat::query()
            ->where('is_active', true);

        if ($id) {
            $jenisSurat = $builder->find($id);

            if (!$jenisSurat) {
                return response()->json(['data' => []]);
            }

            return response()->json([
                'data' => [
                    $this->formatJenisSuratResult($jenisSurat),
                ]
            ]);
        }

        if (strlen($query) >= 1) {
            $builder->where(function ($q) use ($query) {
                $q->where('nama', 'LIKE', "%{$query}%")
                    ->orWhere('kode', 'LIKE', "%{$query}%");
            });
        }

        $results = $builder
            ->orderBy('nama')
            ->limit($limit)
            ->get()
            ->map(fn($js) => $this->formatJenisSuratResult($js))
            ->toArray();

        return response()->json(['data' => $results]);
    }

    /**
     * Format penduduk for dropdown display
     */
    private function formatPendudukResult(Penduduk $penduduk): array
    {
        $rtRw = '';
        if ($penduduk->rt) {
            $rtRw = "RT {$penduduk->rt->nomor_rt}";
            if ($penduduk->rt->rw) {
                $rtRw .= "/RW {$penduduk->rt->rw->nomor_rw}";
            }
        }

        return [
            // Tom Select required fields
            'id'                 => $penduduk->id,
            'label'              => $penduduk->nama_lengkap,
            'subtitle'           => "NIK: {$penduduk->nik}" . ($rtRw ? " • {$rtRw}" : ''),

            // Autofill: text inputs
            'nik'                => $penduduk->nik,
            'nama'               => $penduduk->nama_lengkap,
            'tempat_lahir'       => $penduduk->tempat_lahir,
            'tgl_lahir'          => $penduduk->tgl_lahir->format('Y-m-d'),
            'nama_ayah'          => $penduduk->nama_ayah,
            'nama_ibu'           => $penduduk->nama_ibu,
            'no_hp'              => $penduduk->no_hp,
            'email'              => $penduduk->email,

            // Autofill: native selects
            'jenis_kelamin'      => $penduduk->jenis_kelamin,
            'status_perkawinan'  => $penduduk->status_perkawinan,

            // Autofill: Tom Select fields (foreign keys)
            'agama_id'           => $penduduk->agama_id,
            'pendidikan_id'      => $penduduk->pendidikan_id,
            'pekerjaan_id'       => $penduduk->pekerjaan_id,
            'golongan_darah_id'  => $penduduk->golongan_darah_id,
            'pendapatan_range_id' => $penduduk->pendapatan_range_id,
        ];
    }

    /**
     * Format kartu keluarga for dropdown display
     */
    private function formatKKResult(KartuKeluarga $kk): array
    {
        // Access through kepalaKeluarga (KkMember) -> penduduk (Penduduk)
        $kepala = 'Belum ada kepala';
        if ($kk->kepalaKeluarga && $kk->kepalaKeluarga->penduduk) {
            $kepala = $kk->kepalaKeluarga->penduduk->nama_lengkap;
        }

        $rtRw = '';
        if ($kk->rt) {
            $rtRw = "RT {$kk->rt->nomor_rt}";
            if ($kk->rt->rw) {
                $rtRw .= "/RW {$kk->rt->rw->nomor_rw}";
            }
        }

        return [
            'id' => $kk->id,
            'label' => $kk->no_kk,
            'subtitle' => "Kepala: {$kepala}" . ($rtRw ? " • {$rtRw}" : ''),
            'no_kk' => $kk->no_kk,
            'kepala' => $kepala,
        ];
    }

    /**
     * Format jenis surat for dropdown display
     */
    private function formatJenisSuratResult(JenisSurat $jenisSurat): array
    {
        return [
            'id' => $jenisSurat->kode,
            'label' => $jenisSurat->nama,
            'subtitle' => $jenisSurat->deskripsi ? substr($jenisSurat->deskripsi, 0, 50) . '...' : null,
            'kode' => $jenisSurat->kode,
            'masa_berlaku' => $jenisSurat->masa_berlaku_hari,
        ];
    }
}
