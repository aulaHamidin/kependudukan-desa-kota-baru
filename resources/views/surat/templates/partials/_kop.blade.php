{{--
    Kop Surat Desa
    Partial yang digunakan di semua surat

    Variables yang diharapkan (dari parent):
    - $data['desa'] atau config yang sesuai
--}}
@php
    // Ambil data desa dari user yang login atau dari data surat
    $desa = $data['desa_info'] ?? [
        'nama' => config('app.desa.nama', 'DESA CONTOH'),
        'kecamatan' => config('app.desa.kecamatan', 'KECAMATAN CONTOH'),
        'kabupaten' => config('app.desa.kabupaten', 'KABUPATEN CONTOH'),
        'provinsi' => config('app.desa.provinsi', 'PROVINSI CONTOH'),
        'alamat' => config('app.desa.alamat', 'Jl. Contoh No. 1'),
        'kode_pos' => config('app.desa.kode_pos', '12345'),
        'telepon' => config('app.desa.telepon', '(021) 123-4567'),
        'email' => config('app.desa.email', 'desa@example.com'),
    ];
@endphp

<div class="kop-surat">
    <table style="width: 100%;">
        <tr>
            {{-- Logo Kabupaten (kiri) --}}
            <td style="width: 15%; vertical-align: middle; text-align: center;">
                @if (file_exists(public_path('images/logo-kabupaten.png')))
                    <img src="{{ public_path('images/logo-kabupaten.png') }}" alt="Logo" style="height: 80px;">
                @else
                    <div style="width: 80px; height: 80px; border: 1px dashed #999; margin: auto;"></div>
                @endif
            </td>

            {{-- Teks KOP --}}
            <td style="width: 70%; vertical-align: middle; text-align: center;">
                <p class="instansi">PEMERINTAH {{ strtoupper($desa['kabupaten'] ?? 'KABUPATEN') }}</p>
                <p class="instansi">KECAMATAN {{ strtoupper($desa['kecamatan'] ?? 'KECAMATAN') }}</p>
                <p class="nama-desa">{{ strtoupper($desa['nama'] ?? 'DESA') }}</p>
                <p class="alamat">
                    Alamat: {{ $desa['alamat'] ?? 'Alamat Desa' }}
                    @if (!empty($desa['kode_pos']))
                        , Kode Pos {{ $desa['kode_pos'] }}
                    @endif
                </p>
                @if (!empty($desa['telepon']) || !empty($desa['email']))
                    <p class="alamat">
                        @if (!empty($desa['telepon']))
                            Telp: {{ $desa['telepon'] }}
                            @if (!empty($desa['email']))
                                |
                            @endif
                        @endif
                        @if (!empty($desa['email']))
                            Email: {{ $desa['email'] }}
                        @endif
                    </p>
                @endif
            </td>

            {{-- Logo Desa (kanan) --}}
            <td style="width: 15%; vertical-align: middle; text-align: center;">
                @if (file_exists(public_path('images/logo-desa.png')))
                    <img src="{{ public_path('images/logo-desa.png') }}" alt="Logo Desa" style="height: 80px;">
                @else
                    <div style="width: 80px; height: 80px; border: 1px dashed #999; margin: auto;"></div>
                @endif
            </td>
        </tr>
    </table>
</div>
{{-- Garis tebal + tipis (standar surat pemerintah) --}}
<div class="kop-border"></div>
<div class="kop-border-thin"></div>
