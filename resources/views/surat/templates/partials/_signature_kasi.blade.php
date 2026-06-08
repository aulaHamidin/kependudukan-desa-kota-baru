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

    // Singkirkan prefiks "DESA" agar jabatan tidak ganda
    $desaNamaRaw = $data['desa_info']['nama'] ?? config('app.desa.nama', 'Desa');
    $desaNama = trim(preg_replace('/^desa\s+/i', '', $desaNamaRaw));
@endphp

<div class="signature-block right">
    <p class="tempat-tanggal">
        {{ $desaNama }}, {{ $tanggalSurat->translatedFormat('d F Y') }}
    </p>
    <p class="jabatan">{{ strtoupper($kasi['jabatan']) }}</p>
    <p class="nama">{{ strtoupper($kasi['nama']) }}</p>
    @if (!empty($kasi['nip']))
        <p class="nip">NIP. {{ $kasi['nip'] }}</p>
    @endif
</div>
