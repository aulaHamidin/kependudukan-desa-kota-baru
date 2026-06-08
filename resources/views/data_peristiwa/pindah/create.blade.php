{{-- Data Peristiwa - Pindah Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Pindah', 'url' => route('events.pindah.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Tambah Data Pindah" subtitle="Catat peristiwa pindah domisili baru" />
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
                    <li>Pastikan alamat tujuan pindah diisi dengan lengkap</li>
                    <li>Jika yang pindah adalah kepala keluarga, pilih pengganti dari anggota KK</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.pindah.store') }}" class="space-y-6" x-data="{
        pendudukId: '{{ old('penduduk_id') }}',
        kkId: '{{ old('kk_id') }}',
        isKepalaKeluarga: false,
        kkMembers: [],
        loadingMembers: false,
        async checkKepala() {
            if (!this.pendudukId || !this.kkId) {
                this.isKepalaKeluarga = false;
                this.kkMembers = [];
                return;
            }
            try {
                this.loadingMembers = true;
                const response = await fetch(`{{ route('events.pindah.kk-members') }}?penduduk_id=${this.pendudukId}&kk_id=${this.kkId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json();
                this.isKepalaKeluarga = data.is_kepala_keluarga;
                this.kkMembers = data.members || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMembers = false;
            }
        }
    }"
        x-init="checkKepala()">
        @csrf

        {{-- Section 1: Informasi Event --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">1</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Event</h3>
                        <p class="text-sm text-gray-500">Lokasi dan tanggal peristiwa pindah</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select-searchable name="rt_id" label="RT" placeholder="Pilih RT..." :options="$rtOptions"
                    :min-chars="1" :value="old('rt_id')" required />

                <x-form-input name="event_date" type="date" label="Tanggal Pindah" :value="old('event_date')"
                    max="{{ date('Y-m-d') }}" required />
            </div>
        </x-card>

        {{-- Section 2: Data Penduduk yang Pindah --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Penduduk yang Pindah</h3>
                        <p class="text-sm text-gray-500">Pilih penduduk dan kartu keluarga terkait</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select-searchable name="penduduk_id" label="Penduduk"
                    placeholder="Ketik nama atau NIK penduduk..." remote-url="{{ route('search.penduduk') }}"
                    :min-chars="2" :value="old('penduduk_id')" required
                    x-on:change="pendudukId = $event.detail?.value || $event.target.value; checkKepala()" />

                <x-form-select-searchable name="kk_id" label="Kartu Keluarga"
                    placeholder="Ketik No. KK atau nama kepala keluarga..."
                    remote-url="{{ route('search.kartu-keluarga') }}" :min-chars="2" :value="old('kk_id')" required
                    x-on:change="kkId = $event.detail?.value || $event.target.value; checkKepala()" />

            </div>
        </x-card>

        {{-- Section 3: Tujuan Pindah --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">3</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Tujuan Pindah</h3>
                        <p class="text-sm text-gray-500">Alamat lengkap tujuan kepindahan</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <x-form-textarea name="alamat_tujuan" label="Alamat Tujuan" rows="2"
                    placeholder="Alamat lengkap tujuan pindah" :value="old('alamat_tujuan')" required />

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-form-input name="rt_tujuan" label="RT Tujuan" placeholder="Contoh: 001" :value="old('rt_tujuan')" />

                    <x-form-input name="rw_tujuan" label="RW Tujuan" placeholder="Contoh: 002" :value="old('rw_tujuan')" />

                    <x-form-input name="desa_tujuan" label="Desa/Kelurahan" placeholder="Nama desa/kelurahan"
                        :value="old('desa_tujuan')" />

                    <x-form-input name="kode_pos_tujuan" label="Kode Pos" placeholder="Contoh: 12345"
                        :value="old('kode_pos_tujuan')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-form-input name="kecamatan_tujuan" label="Kecamatan" placeholder="Nama kecamatan"
                        :value="old('kecamatan_tujuan')" required />

                    <x-form-input name="kabupaten_tujuan" label="Kabupaten/Kota" placeholder="Nama kabupaten/kota"
                        :value="old('kabupaten_tujuan')" required />

                    <x-form-input name="provinsi_tujuan" label="Provinsi" placeholder="Nama provinsi" :value="old('provinsi_tujuan')"
                        required />
                </div>
            </div>
        </x-card>

        {{-- Section 4: Alasan Pindah --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">4</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Alasan Pindah</h3>
                        <p class="text-sm text-gray-500">Alasan dan jenis kepindahan penduduk</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select name="alasan_pindah" label="Alasan Pindah" :value="old('alasan_pindah')" required>
                    <option value="">Pilih Alasan</option>
                    <option value="PEKERJAAN" {{ old('alasan_pindah') === 'PEKERJAAN' ? 'selected' : '' }}>Pekerjaan
                    </option>
                    <option value="PENDIDIKAN" {{ old('alasan_pindah') === 'PENDIDIKAN' ? 'selected' : '' }}>Pendidikan
                    </option>
                    <option value="KEAMANAN" {{ old('alasan_pindah') === 'KEAMANAN' ? 'selected' : '' }}>Keamanan
                    </option>
                    <option value="KESEHATAN" {{ old('alasan_pindah') === 'KESEHATAN' ? 'selected' : '' }}>Kesehatan
                    </option>
                    <option value="PERKAWINAN" {{ old('alasan_pindah') === 'PERKAWINAN' ? 'selected' : '' }}>Perkawinan
                    </option>
                    <option value="LAINNYA" {{ old('alasan_pindah') === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                </x-form-select>

                <x-form-select name="jenis_kepindahan" label="Jenis Kepindahan" :value="old('jenis_kepindahan', 'INDIVIDU')" required>
                    <option value="INDIVIDU"
                        {{ old('jenis_kepindahan', 'INDIVIDU') === 'INDIVIDU' ? 'selected' : '' }}>Individu</option>
                </x-form-select>
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan_alasan" label="Keterangan Alasan" rows="2"
                    placeholder="Keterangan tambahan tentang alasan pindah (opsional)" :value="old('keterangan_alasan')" />
            </div>
        </x-card>

        {{-- Section 5: Pengganti Kepala Keluarga (Conditional) --}}
        <x-card x-show="isKepalaKeluarga" x-cloak>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">5</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Pengganti Kepala Keluarga</h3>
                        <p class="text-sm text-amber-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Penduduk yang pindah adalah kepala keluarga. Pilih pengganti dari anggota KK.
                        </p>
                    </div>
                </div>
            </x-slot>

            <div x-show="loadingMembers" class="text-gray-500 text-sm">
                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat anggota keluarga...
            </div>

            <div x-show="!loadingMembers && kkMembers.length > 0">
                <x-form-select name="pengganti_kepala_id" label="Pengganti Kepala Keluarga" :required="false"
                    x-bind:required="isKepalaKeluarga && kkMembers.length > 0" :value="old('pengganti_kepala_id')">
                    <option value="">Pilih Pengganti</option>
                    <template x-for="member in kkMembers" :key="member.id">
                        <option :value="member.id" x-text="member.text"></option>
                    </template>
                </x-form-select>
            </div>

            <div x-show="!loadingMembers && kkMembers.length === 0" class="text-amber-600 text-sm">
                <i class="fas fa-info-circle mr-1"></i>
                Tidak ada anggota keluarga lain yang dapat menjadi pengganti.
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
                    <x-button type="button" variant="secondary" :href="route('events.pindah.index')">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Data Pindah
                    </x-button>
                </div>
            </div>
        </x-card>
    </form>
</x-app-layout>
