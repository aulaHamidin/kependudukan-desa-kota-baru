{{-- Data Peristiwa - Datang Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Datang', 'url' => route('events.datang.index')],
            ['label' => 'Detail', 'url' => route('events.datang.show', $event)],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Edit Penduduk Datang" subtitle="Perbarui data penduduk yang datang ke desa" />
    </x-slot>

    @php
        $jenisKedatangan = old('jenis_kedatangan', $event->eventDatang->jenis_kedatangan);
    @endphp

    {{-- Info Banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-semibold text-blue-800">Informasi Edit</h4>
                <ul class="text-sm text-blue-700 mt-1 list-disc list-inside space-y-0.5">
                    <li>Jenis kedatangan dan NIK tidak dapat diubah setelah event dibuat</li>
                    <li>Perubahan data akan langsung memperbarui record event</li>
                    <li>Kolom bertanda <span class="text-rose-500 font-bold">*</span> wajib diisi</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.datang.update', $event) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ============================================================ --}}
        {{-- Section 1: Informasi Kedatangan --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">1</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Kedatangan</h3>
                        <p class="text-sm text-gray-500">Jenis kedatangan, tanggal, dan asal penduduk</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Jenis Kedatangan: readonly, tidak bisa diubah --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                        Jenis Kedatangan
                    </label>
                    <p class="text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        {{ $jenisKedatangan === 'pindah_masuk' ? 'Pindah Masuk' : 'Pendatang Baru' }}
                    </p>
                    {{-- Hidden input agar tetap terkirim ke server --}}
                    <input type="hidden" name="jenis_kedatangan" value="{{ $jenisKedatangan }}" />
                </div>

                <x-form-input name="event_date" type="date" label="Tanggal Datang" :value="old('event_date', $event->event_date?->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="alamat_asal" label="Alamat Asal" rows="2" placeholder="Alamat sebelumnya"
                    :value="old('alamat_asal', $event->eventDatang->alamat_asal)" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <x-form-select-searchable name="kk_tujuan_id" label="Kartu Keluarga Tujuan"
                    placeholder="Ketik No. KK atau nama kepala keluarga..."
                    remote-url="{{ route('search.kartu-keluarga') }}" :min-chars="2" :value="old('kk_tujuan_id', $event->eventDatang->kk_tujuan_id)" />

                <x-form-input name="alasan_datang" label="Alasan Datang" placeholder="Contoh: Pekerjaan, Keluarga"
                    :value="old('alasan_datang', $event->eventDatang->alasan_datang)" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan_alasan" label="Keterangan Alasan" rows="2"
                    placeholder="Keterangan tambahan (opsional)" :value="old('keterangan_alasan', $event->eventDatang->keterangan_alasan)" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 2: Data Surat Pindah (hanya jika pindah_masuk) --}}
        {{-- ============================================================ --}}
        @if ($jenisKedatangan === 'pindah_masuk')
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">2</span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Data Surat Pindah</h3>
                            <p class="text-sm text-gray-500">Wajib diisi untuk jenis kedatangan "Pindah Masuk"</p>
                        </div>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-input name="no_surat_pindah" label="Nomor Surat Pindah"
                        placeholder="Nomor surat keterangan pindah" :value="old('no_surat_pindah', $event->eventDatang->no_surat_pindah)" required />

                    <x-form-input name="tanggal_surat_pindah" type="date" label="Tanggal Surat Pindah"
                        :value="old(
                            'tanggal_surat_pindah',
                            $event->eventDatang->tanggal_surat_pindah?->format('Y-m-d'),
                        )" max="{{ date('Y-m-d') }}" required />
                </div>
            </x-card>
        @endif

        {{-- ============================================================ --}}
        {{-- Section 3: Data Penduduk - Informasi Wajib --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">3</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Penduduk</h3>
                        <p class="text-sm text-gray-500">NIK tidak dapat diubah.</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- NIK: selalu readonly --}}
                <div>
                    <label class="form-label">NIK</label>
                    <p class="text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk->nik ?? '') }}</span>
                    </p>
                </div>

                <x-form-input name="nama_lengkap" label="Nama Lengkap" placeholder="Nama sesuai KTP" :value="old('nama_lengkap', $event->penduduk->nama_lengkap)"
                    required />

                <x-form-select name="jenis_kelamin" label="Jenis Kelamin" :value="old('jenis_kelamin', $event->penduduk->jenis_kelamin)" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L"
                        {{ old('jenis_kelamin', $event->penduduk->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki
                    </option>
                    <option value="P"
                        {{ old('jenis_kelamin', $event->penduduk->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan
                    </option>
                </x-form-select>

                <x-form-input name="tempat_lahir" label="Tempat Lahir" placeholder="Kota/Kabupaten lahir"
                    :value="old('tempat_lahir', $event->penduduk->tempat_lahir)" required />

                <x-form-input name="tgl_lahir" type="date" label="Tanggal Lahir" :value="old('tgl_lahir', $event->penduduk->tgl_lahir?->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}" required />

                <x-form-select-searchable name="agama_id" label="Agama" placeholder="Pilih agama..." :options="$agamas"
                    :min-chars="1" :value="old('agama_id', $event->penduduk->agama_id)" required />

                <x-form-select name="status_perkawinan" label="Status Perkawinan" :value="old('status_perkawinan', $event->penduduk->status_perkawinan)" required>
                    <option value="">Pilih Status</option>
                    <option value="Belum Kawin"
                        {{ old('status_perkawinan', $event->penduduk->status_perkawinan) === 'Belum Kawin' ? 'selected' : '' }}>
                        Belum Kawin</option>
                    <option value="Kawin"
                        {{ old('status_perkawinan', $event->penduduk->status_perkawinan) === 'Kawin' ? 'selected' : '' }}>
                        Kawin</option>
                    <option value="Cerai Hidup"
                        {{ old('status_perkawinan', $event->penduduk->status_perkawinan) === 'Cerai Hidup' ? 'selected' : '' }}>
                        Cerai Hidup</option>
                    <option value="Cerai Mati"
                        {{ old('status_perkawinan', $event->penduduk->status_perkawinan) === 'Cerai Mati' ? 'selected' : '' }}>
                        Cerai Mati</option>
                </x-form-select>

                <x-form-select-searchable name="rt_id" label="RT Tujuan" required :options="$rtOptions"
                    placeholder="Pilih RT..." :value="old('rt_id', $event->penduduk->rt_id)" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 4: Informasi Tambahan --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">4</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Tambahan</h3>
                        <p class="text-sm text-gray-500">Data opsional untuk melengkapi profil penduduk</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input name="nama_ayah" label="Nama Ayah" placeholder="Nama lengkap ayah"
                    :value="old('nama_ayah', $event->penduduk->nama_ayah)" />

                <x-form-input name="nama_ibu" label="Nama Ibu" placeholder="Nama lengkap ibu" :value="old('nama_ibu', $event->penduduk->nama_ibu)" />

                <x-form-select-searchable name="pendidikan_id" label="Pendidikan Terakhir"
                    placeholder="Pilih pendidikan..." :options="$pendidikans" :min-chars="1" :value="old('pendidikan_id', $event->penduduk->pendidikan_id)" />

                <x-form-select-searchable name="pekerjaan_id" label="Pekerjaan" placeholder="Pilih pekerjaan..."
                    :options="$pekerjaans" :min-chars="1" :value="old('pekerjaan_id', $event->penduduk->pekerjaan_id)" />

                <x-form-select-searchable name="pendapatan_range_id" label="Pendapatan"
                    placeholder="Pilih range pendapatan..." :options="$pendapatanRanges" :min-chars="1" :value="old('pendapatan_range_id', $event->penduduk->pendapatan_range_id)" />

                <x-form-select-searchable name="golongan_darah_id" label="Golongan Darah"
                    placeholder="Pilih golongan darah..." :options="$golonganDarahs" :min-chars="1" :value="old('golongan_darah_id', $event->penduduk->golongan_darah_id)" />

                <x-form-input name="no_hp" type="tel" label="Nomor HP" placeholder="Contoh: 081234567890"
                    :value="old('no_hp', $event->penduduk->no_hp)" />

                <x-form-input name="email" type="email" label="Email" placeholder="contoh@email.com"
                    :value="old('email', $event->penduduk->email)" />
            </div>
        </x-card>

        {{-- Form Actions --}}
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pastikan semua data sudah benar sebelum menyimpan
                </p>
                <div class="flex gap-3">
                    <x-button type="button" variant="secondary" :href="route('events.datang.show', $event)">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </x-button>
                </div>
            </div>
        </x-card>
    </form>
</x-app-layout>

{{-- ============================================================ --}}
{{-- Catatan: --}}
{{-- - Pada form create, semua field data penduduk kosong dan dapat diisi manual atau otomatis jika jenis kedatangan adalah pindah masuk. --}}
{{-- - Pada form edit, field data penduduk sudah terisi dengan data yang ada dan hanya bisa diubah jika jenis kedatangan adalah pendatang baru. --}}
{{-- - Bagian pelapor dan pengganti kepala keluarga sama antara create dan edit, karena selalu harus diisi saat menyimpan peristiwa kematian. --}}
