{{--
    Kop Surat Desa
    Partial yang digunakan di semua surat

    Variables yang diharapkan (dari parent):
    - $data['desa'] atau config yang sesuai
--}}
@php
    // Ambil data desa dari user yang login atau dari data surat
    $desa = array_merge([
        'nama' => config('app.desa.nama', 'DESA CONTOH'),
        'kecamatan' => config('app.desa.kecamatan', 'KECAMATAN CONTOH'),
        'kabupaten' => config('app.desa.kabupaten', 'KABUPATEN CONTOH'),
        'provinsi' => config('app.desa.provinsi', 'PROVINSI CONTOH'),
        'alamat' => config('app.desa.alamat', 'Jl. Contoh No. 1'),
        'kode_pos' => config('app.desa.kode_pos', '12345'),
        'telepon' => config('app.desa.telepon', '(021) 123-4567'),
        'email' => config('app.desa.email', 'desa@example.com'),
    ], $data['desa_info'] ?? []);

    $stripPrefix = fn($value, array $prefixes) => trim(preg_replace('/^(' . implode('|', $prefixes) . ')\s+/i', '', (string) $value));

    $namaDesa = $stripPrefix($desa['nama'] ?? 'DESA', ['desa', 'kelurahan']);
    $kecamatan = $stripPrefix($desa['kecamatan'] ?? 'KECAMATAN', ['kecamatan']);
    $kabupaten = $stripPrefix($desa['kabupaten'] ?? 'KABUPATEN', ['kabupaten', 'kota']);
@endphp

<div class="kop-surat">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            {{-- Logo Desa (kiri) --}}
            <td style="width: 15%; vertical-align: middle; text-align: center;">
                @if (file_exists(public_path('images/logo-desa.png')))
                    <img src="{{ public_path('images/logo-desa.png') }}" alt="Logo Desa" style="height: 62px;">
                @else
                    <div style="width: 62px; height: 62px; border: 1px dashed #999; margin: auto;"></div>
                @endif
            </td>

            {{-- Teks KOP --}}
            <td style="width: 70%; vertical-align: middle; text-align: center;">
                <p class="instansi">PEMERINTAH KABUPATEN {{ strtoupper($kabupaten) }}</p>
                <p class="instansi">KECAMATAN {{ strtoupper($kecamatan) }}</p>
                <p class="nama-desa">{{ strtoupper($namaDesa) }}</p>
                @if (!empty($desa['email']))
                    <p class="alamat">e-mail : {{ $desa['email'] }}</p>
                @endif
                <p class="alamat">
                    Alamat : {{ $desa['alamat'] ?? config('app.desa.alamat', 'Jl. Contoh No. 1') }}
                    @if (!empty($desa['kode_pos']))
                        KP.{{ $desa['kode_pos'] }}
                    @endif
                </p>
            </td>

            {{-- Logo kanan bertumpuk --}}
            <td style="width: 15%; vertical-align: middle; text-align: center;">
                <div style="height: 62px; width: 62px; margin: 0 auto; text-align: center;">
                    @if (file_exists(public_path('images/logo-atas.png')))
                        <img src="{{ public_path('images/logo-atas.png') }}" alt="Logo Atas" style="height: 30px; display: block; margin: 0 auto 2px;">
                    @endif
                    @if (file_exists(public_path('images/logo-bawah.png')))
                        <img src="{{ public_path('images/logo-bawah.png') }}" alt="Logo Bawah" style="height: 30px; display: block; margin: 0 auto;">
                    @endif
                    @if (!file_exists(public_path('images/logo-atas.png')) && !file_exists(public_path('images/logo-bawah.png')))
                        <div style="width: 62px; height: 62px; border: 1px dashed #999; margin: auto;"></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>
{{-- Garis tebal + tipis (standar surat pemerintah) --}}
<div class="kop-border"></div>
<div class="kop-border-thin"></div>
