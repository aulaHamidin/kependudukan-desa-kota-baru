{{--
    Signature: Kepala Desa (default)
    Tanda tangan tunggal di kanan: Kepala Desa
    Uses blank wet-signature space to avoid broken PDF image placeholders
--}}
@php
    $kepalaDesa = $data['kepala_desa'] ?? [
        'nama' => config('app.desa.kepala_desa.nama', 'Nama Kepala Desa'),
        'nip' => config('app.desa.kepala_desa.nip', null),
        'jabatan' => config('app.desa.kepala_desa.jabatan', 'Kepala Desa'),
    ];

    $tanggalSurat = $suratTerbit->tanggal_terbit ?? now();
    $tanggalSuratText = \Carbon\Carbon::parse($tanggalSurat)->locale(config('app.locale', 'id'))->translatedFormat('d F Y');

    // Singkirkan prefiks "DESA" agar jabatan tidak ganda
    $desaNamaRaw = $data['desa_info']['nama'] ?? config('app.desa.nama', 'Desa');
    $desaNama = trim(preg_replace('/^(desa|kelurahan)\s+/i', '', $desaNamaRaw));
@endphp

<table class="signature-table">
    <tr>
        <td class="signature-spacer"></td>
        <td class="signature-cell">
            <p class="tempat-tanggal">{{ $desaNama }}, {{ $tanggalSuratText }}</p>
            <p class="jabatan">{{ $kepalaDesa['jabatan'] ?? 'Kepala Desa' }},</p>

            <div class="ruang-ttd"></div>

            <p class="nama">{{ strtoupper($kepalaDesa['nama']) }}</p>
            @if (!empty($kepalaDesa['nip']))
                <p class="nip">NIP. {{ $kepalaDesa['nip'] }}</p>
            @endif
        </td>
    </tr>
</table>
