{{--
    Signature: Dual (Kepala Desa + Pemohon)
    Tanda tangan ganda: Pemohon di kiri, Kepala Desa di kanan
    Uses blank wet-signature space to avoid broken PDF image placeholders
--}}
@php
    $kepalaDesa = $data['kepala_desa'] ?? [
        'nama' => config('app.desa.kepala_desa.nama', 'Nama Kepala Desa'),
        'nip' => config('app.desa.kepala_desa.nip', null),
    ];
    $pemohon = [
        'nama' => $data['nama_lengkap'] ?? 'Nama Pemohon',
    ];
    $tanggalSurat = $suratTerbit->tanggal_terbit ?? now();
    $tanggalSuratText = \Carbon\Carbon::parse($tanggalSurat)->locale(config('app.locale', 'id'))->translatedFormat('d F Y');

    // Singkirkan prefiks "DESA" agar jabatan tidak ganda
    $desaNamaRaw = $data['desa_info']['nama'] ?? config('app.desa.nama', 'Desa');
    $desaNama = trim(preg_replace('/^(desa|kelurahan)\s+/i', '', $desaNamaRaw));
@endphp

<table style="width:100%; margin-top:10px; page-break-inside:avoid;">
    <tr>
        {{-- Pemohon (kiri) --}}
        <td style="width:45%; text-align:center; vertical-align:top;">
            <p style="margin-bottom:2px;">&nbsp;</p>
            <p style="font-weight:bold; margin-bottom:2px;">PEMOHON</p>
            <div style="height:48px;"></div>
            <p style="margin-top:2px; font-weight:bold; text-decoration:underline;">
                {{ strtoupper($pemohon['nama']) }}
            </p>
        </td>

        {{-- Spacer --}}
        <td style="width:10%;"></td>

        {{-- Kepala Desa (kanan) --}}
        <td style="width:45%; text-align:center; vertical-align:top;">
            <p style="margin-bottom:2px;">
                {{ $desaNama }}, {{ $tanggalSuratText }}
            </p>
            <p style="font-weight:bold; margin-bottom:2px;">KEPALA DESA {{ strtoupper($desaNama) }}</p>

            <div style="height:48px;"></div>

            <p style="margin-top:2px; font-weight:bold; text-decoration:underline;">
                {{ strtoupper($kepalaDesa['nama']) }}
            </p>
            @if (!empty($kepalaDesa['nip']))
                <p style="margin-top:2px; font-size:10pt;">NIP. {{ $kepalaDesa['nip'] }}</p>
            @endif
        </td>
    </tr>
</table>
