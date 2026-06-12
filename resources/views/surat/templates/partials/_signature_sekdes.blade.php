{{--
    Signature: Sekretaris Desa
    Tanda tangan tunggal di kanan: Sekretaris Desa
--}}
@php
    $sekdes = $data['sekdes'] ?? [
        'nama' => config('app.desa.sekdes.nama', 'Nama Sekretaris Desa'),
        'nip' => config('app.desa.sekdes.nip', null),
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
            <p class="jabatan">SEKRETARIS DESA {{ strtoupper($desaNama) }}</p>
            <div class="ruang-ttd"></div>
            <p class="nama">{{ strtoupper($sekdes['nama']) }}</p>
            @if (!empty($sekdes['nip']))
                <p class="nip">NIP. {{ $sekdes['nip'] }}</p>
            @endif
        </td>
    </tr>
</table>
