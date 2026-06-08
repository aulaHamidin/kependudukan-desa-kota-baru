{{-- Data Inti - Penduduk Edit --}}
<x-app-layout>
    <x-slot name="title">Lengkapi Data - {{ $penduduk->nama_lengkap }}</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Inti', 'url' => '#'],
            ['label' => 'Penduduk', 'url' => route('penduduk.index')],
            ['label' => $penduduk->nama_lengkap, 'url' => route('penduduk.show', $penduduk)],
            ['label' => 'Lengkapi Data'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Edit Data Penduduk" subtitle="Perbarui data penduduk {{ $penduduk->nama_lengkap }}" />
    </x-slot>

    <x-alert />

    {{-- Required field legend --}}
    <div class="mb-4 flex items-center gap-1.5 text-sm text-gray-500">
        <span class="text-red-500 font-bold">*</span> Menandakan field wajib diisi
    </div>

    <form method="POST" action="{{ route('penduduk.update', $penduduk) }}">
        @csrf
        @method('PUT')

        {{-- Data Identitas --}}
        <x-card class="mb-5">
            <x-slot name="header">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded bg-blue-100 text-blue-600">
                        <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Data Identitas</h3>
                        <p class="text-xs text-gray-500">Informasi identitas utama penduduk</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <x-form-input name="nik" label="NIK" type="text" placeholder="16 digit NIK" :value="old('nik', $penduduk->nik)"
                    required maxlength="16" helper="Nomor Induk Kependudukan (16 digit)" />

                <x-form-input name="nama_lengkap" label="Nama Lengkap" type="text"
                    placeholder="Nama lengkap sesuai KTP" :value="old('nama_lengkap', $penduduk->nama_lengkap)" required
                    helper="Nama lengkap tanpa gelar" />

                <x-form-input name="tempat_lahir" label="Tempat Lahir" type="text"
                    placeholder="Kota/Kabupaten kelahiran" :value="old('tempat_lahir', $penduduk->tempat_lahir)" required />

                <x-form-input name="tgl_lahir" label="Tanggal Lahir" type="date" :value="old('tgl_lahir', $penduduk->tgl_lahir?->format('Y-m-d'))" required />

                <x-form-select name="jenis_kelamin" label="Jenis Kelamin" :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" :value="old('jenis_kelamin', $penduduk->jenis_kelamin)"
                    required />

                <x-form-select name="agama_id" label="Agama" :options="\App\Models\Agama::where('is_active', true)->pluck('nama', 'kode')->toArray()" :value="old('agama_id', $penduduk->agama_id)" required />
            </div>
        </x-card>

        {{-- Data Keluarga --}}
        <x-card class="mb-5">
            <x-slot name="header">
                <div class="flex items-center gap-2.5">
                    <span
                        class="inline-flex items-center justify-center w-8 h-8 rounded bg-emerald-100 text-emerald-600">
                        <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Data Keluarga</h3>
                        <p class="text-xs text-gray-500">Informasi orang tua dan status perkawinan</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <x-form-input name="nama_ayah" label="Nama Ayah" type="text" placeholder="Nama lengkap ayah"
                    :value="old('nama_ayah', $penduduk->nama_ayah)" />

                <x-form-input name="nama_ibu" label="Nama Ibu" type="text" placeholder="Nama lengkap ibu"
                    :value="old('nama_ibu', $penduduk->nama_ibu)" />

                <x-form-select name="status_perkawinan" label="Status Perkawinan" :options="[
                    'BELUM_KAWIN' => 'Belum Kawin',
                    'KAWIN' => 'Kawin',
                    'CERAI_HIDUP' => 'Cerai Hidup',
                    'CERAI_MATI' => 'Cerai Mati',
                ]" :value="old('status_perkawinan', $penduduk->status_perkawinan)"
                    placeholder="Pilih status perkawinan..." />

                <x-form-input name="kewarganegaraan" label="Kewarganegaraan" type="text" placeholder="WNI / WNA"
                    :value="old('kewarganegaraan', $penduduk->kewarganegaraan ?? 'WNI')" />
            </div>
        </x-card>

        {{-- Data Pendidikan & Pekerjaan --}}
        <x-card class="mb-5">
            <x-slot name="header">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded bg-amber-100 text-amber-600">
                        <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Pendidikan & Pekerjaan</h3>
                        <p class="text-xs text-gray-500">Riwayat pendidikan dan informasi pekerjaan</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <x-form-select name="pendidikan_id" label="Pendidikan Terakhir" :options="$pendidikans->pluck('nama', 'kode')->toArray()" :value="old('pendidikan_id', $penduduk->pendidikan_id)"
                    placeholder="Pilih pendidikan..." />

                <x-form-select name="pekerjaan_id" label="Pekerjaan" :options="$pekerjaans->pluck('nama', 'kode')->toArray()" :value="old('pekerjaan_id', $penduduk->pekerjaan_id)"
                    placeholder="Pilih pekerjaan..." />

                <x-form-select name="pendapatan_range_id" label="Pendapatan" :options="$pendapatanRanges->pluck('label', 'id')->toArray()" :value="old('pendapatan_range_id', $penduduk->pendapatan_range_id)"
                    placeholder="Pilih range pendapatan..." />

                <x-form-select name="golongan_darah_id" label="Golongan Darah" :options="$golonganDarahs
                    ->mapWithKeys(fn($g) => [$g->kode => $g->nama . ($g->rhesus ? ' (' . $g->rhesus . ')' : '')])
                    ->toArray()" :value="old('golongan_darah_id', $penduduk->golongan_darah_id)"
                    placeholder="Pilih golongan darah..." />
            </div>
        </x-card>

        {{-- Data Kontak --}}
        <x-card class="mb-5">
            <x-slot name="header">
                <div class="flex items-center gap-2.5">
                    <span
                        class="inline-flex items-center justify-center w-8 h-8 rounded bg-purple-100 text-purple-600">
                        <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Data Kontak</h3>
                        <p class="text-xs text-gray-500">Nomor telepon dan alamat email</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <x-form-input name="no_hp" label="No. HP / WhatsApp" type="tel" placeholder="08xxxxxxxxxx"
                    :value="old('no_hp', $penduduk->no_hp)" helper="Format: 10-20 digit angka (e.g., 081234567890)" />

                <x-form-input name="email" label="Email" type="email" placeholder="contoh@email.com"
                    :value="old('email', $penduduk->email)" helper="Email aktif penduduk" />
            </div>
        </x-card>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between py-2">
            <x-button type="button" variant="secondary" :href="route('penduduk.show', $penduduk)">
                Batal
            </x-button>

            <x-button type="submit" variant="primary" icon="save">
                Simpan Perubahan
            </x-button>
        </div>
    </form>
</x-app-layout>
