{{-- Data Peristiwa - Datang Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Datang', 'url' => route('events.datang.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Tambah Penduduk Datang" subtitle="Catat penduduk baru yang datang ke desa" />
    </x-slot>

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
                <h4 class="text-sm font-semibold text-blue-800">Panduan Pengisian</h4>
                <ul class="text-sm text-blue-700 mt-1 list-disc list-inside space-y-0.5">
                    <li>Isi semua kolom bertanda <span class="text-rose-500 font-bold">*</span> (wajib)</li>
                    <li>Untuk <strong>Pindah Masuk</strong>, pilih penduduk berstatus pindah dan data akan terisi
                        otomatis</li>
                    <li>Untuk <strong>Pendatang Baru</strong>, isi semua data penduduk secara manual</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.datang.store') }}" class="space-y-6" x-data="{
        jenisKedatangan: '{{ old('jenis_kedatangan', 'pendatang_baru') }}',
        pendudukSelected: null,
    
        onPendudukSelected(id) {
            if (!id) {
                this.pendudukSelected = null;
                this.clearPendudukFields();
                return;
            }
            fetch(`{{ route('search.penduduk') }}?id=${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(json => {
                    const p = json.data?.[0];
                    if (!p) return;
                    this.pendudukSelected = p;
                    this.fillPendudukFields(p);
                });
        },
    
        fillPendudukFields(p) {
            this.$refs.nik.value = p.nik || '';
            this.$refs.nama_lengkap.value = p.nama || '';
            this.$refs.tempat_lahir.value = p.tempat_lahir || '';
            this.$refs.tgl_lahir.value = p.tgl_lahir || '';
            this.$refs.nama_ayah.value = p.nama_ayah || '';
            this.$refs.nama_ibu.value = p.nama_ibu || '';
            this.$refs.no_hp.value = p.no_hp || '';
            this.$refs.email.value = p.email || '';
            this.$refs.jenis_kelamin.value = p.jenis_kelamin || '';
            this.$refs.status_perkawinan.value = p.status_perkawinan || '';
    
            this.$nextTick(() => {
                if (p.agama_id) window.dispatchEvent(new CustomEvent('ts:set:agama_id', { detail: p.agama_id }));
                if (p.pendidikan_id) window.dispatchEvent(new CustomEvent('ts:set:pendidikan_id', { detail: p.pendidikan_id }));
                if (p.pekerjaan_id) window.dispatchEvent(new CustomEvent('ts:set:pekerjaan_id', { detail: p.pekerjaan_id }));
                if (p.golongan_darah_id) window.dispatchEvent(new CustomEvent('ts:set:golongan_darah_id', { detail: p.golongan_darah_id }));
                if (p.pendapatan_range_id) window.dispatchEvent(new CustomEvent('ts:set:pendapatan_range_id', { detail: p.pendapatan_range_id }));
            });
        },
    
        clearPendudukFields() {
            ['nik', 'nama_lengkap', 'tempat_lahir', 'tgl_lahir', 'nama_ayah', 'nama_ibu', 'no_hp', 'email'].forEach(ref => {
                if (this.$refs[ref]) this.$refs[ref].value = '';
            });
            this.$refs.jenis_kelamin.value = '';
            this.$refs.status_perkawinan.value = '';
            this.$nextTick(() => {
                ['agama_id', 'pendidikan_id', 'pekerjaan_id', 'golongan_darah_id', 'pendapatan_range_id'].forEach(name => {
                    window.dispatchEvent(new CustomEvent('ts:set:' + name, { detail: '' }));
                });
            });
        },
    }">
        @csrf

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
                <x-form-select name="jenis_kedatangan" label="Jenis Kedatangan" :value="old('jenis_kedatangan', 'pendatang_baru')" required
                    x-model="jenisKedatangan">
                    <option value="pendatang_baru"
                        {{ old('jenis_kedatangan', 'pendatang_baru') === 'pendatang_baru' ? 'selected' : '' }}>
                        Pendatang Baru
                    </option>
                    <option value="pindah_masuk" {{ old('jenis_kedatangan') === 'pindah_masuk' ? 'selected' : '' }}>
                        Pindah Masuk
                    </option>
                </x-form-select>

                <x-form-input name="event_date" type="date" label="Tanggal Datang" :value="old('event_date')"
                    max="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="alamat_asal" label="Alamat Asal" rows="2" placeholder="Alamat sebelumnya"
                    :value="old('alamat_asal')" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <x-form-select-searchable name="kk_tujuan_id" label="Kartu Keluarga Tujuan"
                    placeholder="Ketik No. KK atau nama kepala keluarga..."
                    remote-url="{{ route('search.kartu-keluarga') }}" :min-chars="2" :value="old('kk_tujuan_id')" />

                <x-form-input name="alasan_datang" label="Alasan Datang" placeholder="Contoh: Pekerjaan, Keluarga"
                    :value="old('alasan_datang')" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan_alasan" label="Keterangan Alasan" rows="2"
                    placeholder="Keterangan tambahan (opsional)" :value="old('keterangan_alasan')" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 2: Data Surat Pindah (hanya jika pindah_masuk) --}}
        {{-- ============================================================ --}}
        <x-card x-show="jenisKedatangan === 'pindah_masuk'" x-cloak>
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
                    placeholder="Nomor surat keterangan pindah" :value="old('no_surat_pindah')"
                    x-bind:required="jenisKedatangan === 'pindah_masuk'" />

                <x-form-input name="tanggal_surat_pindah" type="date" label="Tanggal Surat Pindah" :value="old('tanggal_surat_pindah')"
                    max="{{ date('Y-m-d') }}" x-bind:required="jenisKedatangan === 'pindah_masuk'" />
            </div>

            {{-- Wajib: Cari penduduk berstatus pindah --}}
            <div class="mt-4">
                <x-form-select-searchable name="penduduk_pindah_id" label="Data Penduduk Pindah Masuk"
                    placeholder="Ketik NIK atau nama penduduk yang pindah..."
                    remote-url="{{ route('search.penduduk') }}?status=pindah" :min-chars="2" :clearable="false"
                    x-bind:required="jenisKedatangan === 'pindah_masuk'"
                    x-on:change="onPendudukSelected($event.target.value)" />
                <p class="form-hint">
                    Pilih penduduk berstatus pindah dari desa asal. Data penduduk akan terisi otomatis.
                </p>
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 3: Data Penduduk - Informasi Wajib --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">3</span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Data Penduduk</h3>
                                <p class="text-sm text-gray-500">
                                    <span x-show="jenisKedatangan === 'pendatang_baru'">
                                        Isi data penduduk baru secara manual.
                                    </span>
                                    <span x-show="jenisKedatangan === 'pindah_masuk'" x-cloak>
                                        Data terisi otomatis dari penduduk yang dipilih. NIK tidak dapat diubah.
                                    </span>
                                </p>
                            </div>
                            <span x-show="jenisKedatangan === 'pindah_masuk' && pendudukSelected !== null" x-cloak
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <i class="fas fa-check-circle mr-1"></i> Data terisi otomatis
                            </span>
                        </div>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- NIK: readonly jika pindah masuk --}}
                <div>
                    <label class="form-label">
                        NIK <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nik" x-ref="nik" value="{{ old('nik') }}" maxlength="16"
                        pattern="[0-9]{16}" placeholder="16 digit NIK"
                        x-bind:readonly="jenisKedatangan === 'pindah_masuk'" class="form-input-custom"
                        x-bind:class="jenisKedatangan === 'pindah_masuk'
                            ?
                            'bg-gray-100 cursor-not-allowed border-gray-200 text-gray-500' :
                            ''"
                        required />
                    <p class="form-hint" x-show="jenisKedatangan === 'pindah_masuk'" x-cloak>
                        NIK diisi otomatis dan tidak dapat diubah.
                    </p>
                    @error('nik')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input name="nama_lengkap" label="Nama Lengkap" placeholder="Nama sesuai KTP"
                    :value="old('nama_lengkap')" x-ref="nama_lengkap" required />

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="form-label">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_kelamin" x-ref="jenis_kelamin" required class="form-select-custom">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-input name="tempat_lahir" label="Tempat Lahir" placeholder="Kota/Kabupaten lahir"
                    :value="old('tempat_lahir')" x-ref="tempat_lahir" required />

                <x-form-input name="tgl_lahir" type="date" label="Tanggal Lahir" :value="old('tgl_lahir')"
                    max="{{ date('Y-m-d') }}" x-ref="tgl_lahir" required />

                <x-form-select-searchable name="agama_id" label="Agama" placeholder="Pilih agama..."
                    :options="$agamas" :min-chars="1" :value="old('agama_id')" required
                    x-on:ts:set:agama_id.window="setValue($event.detail)" />

                {{-- Status Perkawinan --}}
                <div>
                    <label class="form-label">
                        Status Perkawinan <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_perkawinan" x-ref="status_perkawinan" required class="form-select-custom">
                        <option value="">Pilih Status</option>
                        <option value="Belum Kawin"
                            {{ old('status_perkawinan') === 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                        <option value="Kawin" {{ old('status_perkawinan') === 'Kawin' ? 'selected' : '' }}>Kawin
                        </option>
                        <option value="Cerai Hidup"
                            {{ old('status_perkawinan') === 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('status_perkawinan') === 'Cerai Mati' ? 'selected' : '' }}>
                            Cerai Mati</option>
                    </select>
                    @error('status_perkawinan')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-select-searchable name="rt_id" label="RT Tujuan" required :options="$rtOptions"
                    placeholder="Pilih RT..." value="{{ old('rt_id') }}" />
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
                <x-form-input name="nama_ayah" label="Nama Ayah" placeholder="Nama lengkap ayah" :value="old('nama_ayah')"
                    x-ref="nama_ayah" />

                <x-form-input name="nama_ibu" label="Nama Ibu" placeholder="Nama lengkap ibu" :value="old('nama_ibu')"
                    x-ref="nama_ibu" />

                <x-form-select-searchable name="pendidikan_id" label="Pendidikan Terakhir"
                    placeholder="Pilih pendidikan..." :options="$pendidikans" :min-chars="1" :value="old('pendidikan_id')"
                    x-on:ts:set:pendidikan_id.window="setValue($event.detail)" />

                <x-form-select-searchable name="pekerjaan_id" label="Pekerjaan" placeholder="Pilih pekerjaan..."
                    :options="$pekerjaans" :min-chars="1" :value="old('pekerjaan_id')"
                    x-on:ts:set:pekerjaan_id.window="setValue($event.detail)" />

                <x-form-select-searchable name="pendapatan_range_id" label="Pendapatan"
                    placeholder="Pilih range pendapatan..." :options="$pendapatanRanges" :min-chars="1" :value="old('pendapatan_range_id')"
                    x-on:ts:set:pendapatan_range_id.window="setValue($event.detail)" />

                <x-form-select-searchable name="golongan_darah_id" label="Golongan Darah"
                    placeholder="Pilih golongan darah..." :options="$golonganDarahs" :min-chars="1" :value="old('golongan_darah_id')"
                    x-on:ts:set:golongan_darah_id.window="setValue($event.detail)" />

                <x-form-input name="no_hp" type="tel" label="Nomor HP" placeholder="Contoh: 081234567890"
                    :value="old('no_hp')" x-ref="no_hp" />

                <x-form-input name="email" type="email" label="Email" placeholder="contoh@email.com"
                    :value="old('email')" x-ref="email" />
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
                    <x-button type="button" variant="secondary" :href="route('events.datang.index')">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Data Penduduk
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
