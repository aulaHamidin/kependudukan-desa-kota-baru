{{--
    Signature: Kepala Desa (default)
    Tanda tangan tunggal di kanan: Kepala Desa
    Supports TTD digital via config('app.desa.ttd_digital')
--}}
@php
    $kepalaDesa = $data['kepala_desa'] ?? [
        'nama' => config('app.desa.kepala_desa.nama', 'Nama Kepala Desa'),
        'nip' => config('app.desa.kepala_desa.nip', null),
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

<div class="signature-block right" style="text-align:right; margin-top:40px;">
    <p class="tempat-tanggal" style="margin-bottom:5px;">
        {{ $desaNama }}, {{ $tanggalSurat->translatedFormat('d F Y') }}
    </p>
    <p class="jabatan" style="margin-bottom:5px;">KEPALA DESA {{ strtoupper($desaNama) }}</p>

    {{-- TTD Digital / Ruang TTD Basah --}}
    @if ($ttdEnabled && $ttdPath && file_exists(public_path($ttdPath)))
        <div class="ttd-digital" style="position:relative; height:80px; margin:10px 0;">
            <img src="{{ asset($ttdPath) }}" alt="Tanda Tangan Digital"
                style="height:60px; position:absolute; right:20px;">
            @if ($stempelPath && file_exists(public_path($stempelPath)))
                <img src="{{ asset($stempelPath) }}" alt="Stempel Desa"
                    style="height:70px; position:absolute; right:60px; opacity:0.8;">
            @endif
        </div>
    @else
        <div class="ruang-ttd" style="height:80px;"></div>
    @endif

    <p class="nama" style="margin-top:5px; font-weight:bold; text-decoration:underline;">
        {{ strtoupper($kepalaDesa['nama']) }}
    </p>
    @if (!empty($kepalaDesa['nip']))
        <p class="nip" style="margin-top:2px;">NIP. {{ $kepalaDesa['nip'] }}</p>
    @endif
</div>
