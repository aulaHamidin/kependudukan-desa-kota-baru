{{--
    Signature: Dual (Kepala Desa + Pemohon)
    Tanda tangan ganda: Pemohon di kiri, Kepala Desa di kanan
    Supports TTD digital via config('app.desa.ttd_digital')
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

    // Singkirkan prefiks "DESA" agar jabatan tidak ganda
    $desaNamaRaw = $data['desa_info']['nama'] ?? config('app.desa.nama', 'Desa');
    $desaNama = trim(preg_replace('/^desa\s+/i', '', $desaNamaRaw));

    // TTD Digital config
    $ttdDigital = config('app.desa.ttd_digital');
    $ttdEnabled = $ttdDigital['enabled'] ?? false;
    $ttdPath = $ttdDigital['kepala_desa_path'] ?? null;
    $stempelPath = $ttdDigital['stempel_path'] ?? null;
@endphp

<table style="width:100%; margin-top:40px;">
    <tr>
        {{-- Pemohon (kiri) --}}
        <td style="width:45%; text-align:center; vertical-align:top;">
            <p style="margin-bottom:5px;">&nbsp;</p>
            <p style="font-weight:bold; margin-bottom:5px;">PEMOHON</p>
            <div style="height:80px;"></div>
            <p style="margin-top:5px; font-weight:bold; text-decoration:underline;">
                {{ strtoupper($pemohon['nama']) }}
            </p>
        </td>

        {{-- Spacer --}}
        <td style="width:10%;"></td>

        {{-- Kepala Desa (kanan) --}}
        <td style="width:45%; text-align:center; vertical-align:top;">
            <p style="margin-bottom:5px;">
                {{ $desaNama }}, {{ $tanggalSurat->translatedFormat('d F Y') }}
            </p>
            <p style="font-weight:bold; margin-bottom:5px;">KEPALA DESA {{ strtoupper($desaNama) }}</p>

            {{-- TTD Digital / Ruang TTD Basah --}}
            @if ($ttdEnabled && $ttdPath && file_exists(public_path($ttdPath)))
                <div style="position:relative; height:80px; margin:10px 0;">
                    <img src="{{ asset($ttdPath) }}" alt="Tanda Tangan Digital" style="height:60px;">
                    @if ($stempelPath && file_exists(public_path($stempelPath)))
                        <img src="{{ asset($stempelPath) }}" alt="Stempel Desa"
                            style="height:70px; margin-left:-30px; opacity:0.8;">
                    @endif
                </div>
            @else
                <div style="height:80px;"></div>
            @endif

            <p style="margin-top:5px; font-weight:bold; text-decoration:underline;">
                {{ strtoupper($kepalaDesa['nama']) }}
            </p>
            @if (!empty($kepalaDesa['nip']))
                <p style="margin-top:2px; font-size:10pt;">NIP. {{ $kepalaDesa['nip'] }}</p>
            @endif
        </td>
    </tr>
</table>
