{{--
    Signature: Kepala Seksi
    Tanda tangan tunggal di kanan: Kepala Seksi terkait
--}}
@php
    $kasi = $data['kasi'] ?? [
        'nama' => config('app.desa.kasi.nama', 'Nama Kepala Seksi'),
        'jabatan' => $data['kasi_jabatan'] ?? 'Kepala Seksi Pemerintahan',
        'nip' => config('app.desa.kasi.nip', null),
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
            <p class="jabatan">{{ strtoupper($kasi['jabatan']) }}</p>
            <div class="ruang-ttd"></div>
            <p class="nama">{{ strtoupper($kasi['nama']) }}</p>
            @if (!empty($kasi['nip']))
                <p class="nip">NIP. {{ $kasi['nip'] }}</p>
            @endif
        </td>
    </tr>
</table>
