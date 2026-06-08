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

    // Singkirkan prefiks "DESA" agar jabatan tidak ganda
    $desaNamaRaw = $data['desa_info']['nama'] ?? config('app.desa.nama', 'Desa');
    $desaNama = trim(preg_replace('/^desa\s+/i', '', $desaNamaRaw));
@endphp

<div class="signature-block right">
    <p class="tempat-tanggal">
        {{ $desaNama }}, {{ $tanggalSurat->translatedFormat('d F Y') }}
    </p>
    <p class="jabatan">SEKRETARIS DESA {{ strtoupper($desaNama) }}</p>
    <p class="nama">{{ strtoupper($sekdes['nama']) }}</p>
    @if (!empty($sekdes['nip']))
        <p class="nip">NIP. {{ $sekdes['nip'] }}</p>
    @endif
</div>
