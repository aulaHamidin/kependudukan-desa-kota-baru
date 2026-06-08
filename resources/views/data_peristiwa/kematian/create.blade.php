{{-- Data Peristiwa - Kematian Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Kematian', 'url' => route('events.kematian.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Tambah Event Kematian" subtitle="Catat peristiwa kematian penduduk" />
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
                    <li>Pastikan data penduduk dan KK yang dipilih sudah benar</li>
                    <li>Jika almarhum adalah kepala keluarga, pilih pengganti dari anggota KK</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.kematian.store') }}" class="space-y-6" x-data="{
        pendudukId: '{{ old('penduduk_id') }}',
        kkId: '{{ old('kk_id') }}',
        isKepalaKeluarga: false,
        kkMembers: [],
        loadingMembers: false,
    
        pelaporMode: '{{ old('pelapor_id') ? 'penduduk' : (old('nama_pelapor') ? 'manual' : 'penduduk') }}',
    
        async checkKepala() {
            if (!this.pendudukId || !this.kkId) {
                this.isKepalaKeluarga = false;
                this.kkMembers = [];
                return;
            }
            try {
                this.loadingMembers = true;
                const response = await fetch(
                    `{{ route('events.kematian.kk-members') }}?penduduk_id=${this.pendudukId}&kk_id=${this.kkId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    }
                );
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

        {{-- ============================================================ --}}
        {{-- Section 1: Informasi Event --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">1</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Event</h3>
                        <p class="text-sm text-gray-500">Lokasi dan tanggal peristiwa kematian</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select-searchable name="rt_id" label="RT" placeholder="Pilih RT..." :options="$rtOptions"
                    :min-chars="1" :value="old('rt_id')" required />

                <x-form-input name="event_date" type="date" label="Tanggal Meninggal" :value="old('event_date')"
                    max="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan" label="Keterangan" rows="2"
                    placeholder="Keterangan tambahan (opsional)" :value="old('keterangan')" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 2: Data Almarhum --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Almarhum/ah</h3>
                        <p class="text-sm text-gray-500">Penduduk yang meninggal dunia</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select-searchable name="penduduk_id" label="Penduduk"
                    placeholder="Ketik nama atau NIK penduduk..."
                    remote-url="{{ route('search.penduduk') }}?status=AKTIF" :min-chars="2" :value="old('penduduk_id')"
                    required x-on:change="pendudukId = $event.detail?.value || $event.target.value; checkKepala()" />

                <x-form-select-searchable name="kk_id" label="Kartu Keluarga"
                    placeholder="Ketik No. KK atau nama kepala keluarga..."
                    remote-url="{{ route('search.kartu-keluarga') }}" :min-chars="2" :value="old('kk_id')" required
                    x-on:change="kkId = $event.detail?.value || $event.target.value; checkKepala()" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 3: Detail Kematian --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">3</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Detail Kematian</h3>
                        <p class="text-sm text-gray-500">Rincian waktu, tempat, dan penyebab kematian</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input name="tempat_meninggal" label="Tempat Meninggal" placeholder="Contoh: RSUD, Rumah, dll."
                    :value="old('tempat_meninggal')" required />

                <x-form-input name="jam_meninggal" type="text" label="Jam Meninggal"
                    placeholder="Contoh: 09:15 atau 14:30" :value="old('jam_meninggal')" pattern="([0-1]?[0-9]|2[0-3]):[0-5][0-9]"
                    helper="Format 24 jam (00:00 - 23:59). Contoh: 09:15 (pagi) atau 14:30 (siang)" />

                <x-form-input name="sebab_kematian" label="Sebab Kematian" placeholder="Contoh: Sakit, Kecelakaan, dll."
                    :value="old('sebab_kematian')" />

                <x-form-input name="penyakit" label="Penyakit" placeholder="Nama penyakit jika sebab sakit"
                    :value="old('penyakit')" />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan_kematian" label="Keterangan Kematian" rows="2"
                    placeholder="Keterangan tambahan tentang kematian (opsional)" :value="old('keterangan_kematian')" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 4: Data Pelapor --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">4</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Pelapor</h3>
                        <p class="text-sm text-gray-500">Pilih dari daftar penduduk atau isi manual jika bukan penduduk
                            desa</p>
                    </div>
                </div>
            </x-slot>

            {{-- Toggle Buttons --}}
            <div class="flex gap-2 mb-4">
                <x-button type="button" size="sm" variant="secondary"
                    x-bind:class="pelaporMode === 'penduduk' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                    @click="pelaporMode = 'penduduk'">
                    <i class="fas fa-user mr-1.5"></i>
                    Dari Penduduk
                </x-button>
                <x-button type="button" size="sm" variant="secondary"
                    x-bind:class="pelaporMode === 'manual' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                    @click="pelaporMode = 'manual'">
                    <i class="fas fa-keyboard mr-1.5"></i>
                    Input Manual
                </x-button>
            </div>

            {{-- Pilih dari Penduduk --}}
            <div x-show="pelaporMode === 'penduduk'" x-cloak>
                <x-form-select-searchable name="pelapor_id" label="Pelapor (Penduduk)"
                    placeholder="Ketik nama atau NIK pelapor..."
                    remote-url="{{ route('search.penduduk') }}?status=AKTIF" :min-chars="2" :value="old('pelapor_id')"
                    x-bind:disabled="pelaporMode !== 'penduduk'" />
                <p class="form-hint">
                    Pelapor harus penduduk aktif di RT yang sama dengan almarhum.
                </p>
            </div>

            {{-- Input Manual --}}
            <div x-show="pelaporMode === 'manual'" x-cloak>
                <x-form-input name="nama_pelapor" label="Nama Pelapor (Manual)" placeholder="Nama lengkap pelapor"
                    :value="old('nama_pelapor')" x-bind:disabled="pelaporMode !== 'manual'" />
                <p class="form-hint">
                    Isi jika pelapor bukan penduduk desa atau tidak terdaftar.
                </p>
            </div>

            {{-- Hubungan Pelapor (selalu tampil) --}}
            <div class="mt-4">
                <x-form-select-searchable name="hubungan_pelapor_code" label="Hubungan dengan Almarhum"
                    placeholder="Pilih hubungan keluarga..." :options="$hubunganOptions" :min-chars="1"
                    :value="old('hubungan_pelapor_code')" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 5: Pengganti Kepala Keluarga (Conditional) --}}
        {{-- ============================================================ --}}
        <x-card x-show="isKepalaKeluarga" x-cloak>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">5</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Pengganti Kepala Keluarga</h3>
                        <p class="text-sm text-amber-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Almarhum adalah kepala keluarga. Pilih pengganti dari anggota KK yang tersisa.
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

            <div x-show="!loadingMembers && kkMembers.length === 0"
                class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-sm text-amber-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tidak ada anggota keluarga lain. KK akan dinonaktifkan setelah event ini disimpan.
                </p>
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
                    <x-button type="button" variant="secondary" :href="route('events.kematian.index')">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Data Kematian
                    </x-button>
                </div>
            </div>
        </x-card>
    </form>
</x-app-layout>
