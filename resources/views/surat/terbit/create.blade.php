{{-- Surat - Terbit Create --}}
<x-app-layout>
    <x-slot name="title">Terbitkan Surat</x-slot>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Terbit', 'url' => route('surat.terbit.index')],
            ['label' => 'Terbitkan'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Terbitkan Surat" subtitle="Pilih jenis surat dan penduduk untuk menerbitkan surat baru." />
    </x-slot>

    <x-alert />

    <div x-data="suratCreateForm()" x-init="init()">
        <form method="POST" action="{{ route('surat.terbit.store') }}">
            @csrf

            {{-- Section 1: Detail Surat (Jenis & Penduduk) --}}
            <x-card class="mb-6">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Pemohon & Surat</h3>
                </x-slot>

                @php
                    $jenisOptions =
                        $jenisSuratOptions instanceof \Illuminate\Support\Collection
                            ? $jenisSuratOptions->pluck('nama', 'kode')->toArray()
                            : $jenisSuratOptions;
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="space-y-4">
                        {{-- Jenis Surat Selection --}}
                        <div>
                            <x-form-select name="jenis_surat_kode" label="Jenis Surat" :options="$jenisOptions"
                                :value="old('jenis_surat_kode')" x-model="selectedJenis" @change="fetchDetails()" required
                                placeholder="Pilih jenis surat..." />
                        </div>

                        {{-- Dynamic Template Details --}}
                        <div x-show="isLoading" class="text-sm text-gray-500 animate-pulse">Memuat detail template...
                        </div>
                        <div x-show="!isLoading && details" x-cloak
                            class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <div class="flex items-start gap-3">
                                <x-sidebar-icon name="info" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
                                <div>
                                    <h4 class="text-sm font-semibold text-blue-900"
                                        x-text="details?.deskripsi || 'Detail Surat'"></h4>
                                    <p class="text-xs text-blue-700 mt-1">
                                        Masa berlaku default: <span class="font-bold"
                                            x-text="details?.masa_berlaku_hari ? details.masa_berlaku_hari + ' hari' : 'Tidak ditentukan'"></span>
                                    </p>
                                    <template x-if="details?.keterangan">
                                        <p class="text-xs text-blue-700 mt-2 italic"
                                            x-text="details.keterangan"></p>
                                    </template>

                                    <template
                                        x-if="details && details.required_fields && details.required_fields.length > 0">
                                        <div class="mt-3">
                                            <p class="text-xs font-semibold text-blue-900">Field
                                                Wajib
                                                Template Ini:</p>
                                            <ul
                                                class="list-disc pl-5 mt-1 text-xs text-blue-800 space-y-0.5">
                                                <template x-for="field in details.required_fields"
                                                    :key="field">
                                                    <li x-text="field"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Penduduk Selection (Searchable) --}}
                    <div>
                        <x-form-select-searchable name="penduduk_id" label="Penduduk (Pemohon)"
                            remote-url="{{ route('search.penduduk') }}" search-field="q" value-field="id"
                            label-field="label" placeholder="Ketik nama atau NIK penduduk..." :min-chars="2"
                            required />
                        <div
                            class="mt-3 p-3 bg-gray-50 rounded-lg text-xs text-gray-600 border border-gray-200">
                            Pencarian hanya menampilkan data penduduk <strong>Aktif</strong> yang berada di dalam
                            wilayah
                            Anda.
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Section 1A: Dynamic Template Fields --}}
            <x-card class="mb-6" x-show="dynamicFields().length > 0" x-cloak>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Data Tambahan Template</h3>
                    <p class="text-sm text-gray-500">Isi field khusus agar detail surat ikut tersimpan dan tercetak di PDF.</p>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="field in dynamicFields()" :key="field.name">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span x-text="field.label"></span>
                                <span x-show="field.required" class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                class="form-input-custom w-full"
                                :name="`data_surat[${field.name}]`"
                                :required="field.required"
                                :value="oldDataSurat[field.name] || ''"
                                :placeholder="`Isi ${field.label}`" />
                        </div>
                    </template>
                </div>
            </x-card>

            {{-- Section 1B: Detail Surat Internal (SBALASAN, Undangan, dll) --}}
            <x-card class="mb-6" x-show="isInternal()" x-cloak>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Surat Internal</h3>
                    <p class="text-sm text-gray-500">Isi penerima, alamat tujuan, dan perihal agar
                        muncul di surat.</p>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input name="kepada" label="Kepada Yth." :value="old('kepada')"
                        placeholder="Nama/instansi penerima" x-bind:required="isInternal()" />

                    <x-form-input name="alamat_tujuan" label="Alamat Tujuan" :value="old('alamat_tujuan')"
                        placeholder="Alamat penerima" x-bind:required="isInternal()" />

                    <x-form-input name="perihal" label="Perihal / Subject" :value="old('perihal')"
                        placeholder="Subjek surat (mis. Balasan undangan...)" x-bind:required="isInternal()" />

                    <x-form-input name="lampiran" label="Lampiran" :value="old('lampiran')" placeholder="Contoh: 1 berkas" />

                    <div x-show="isBalasan()" x-cloak class="md:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form-input name="nomor_surat_masuk" label="Nomor Surat Masuk" :value="old('nomor_surat_masuk')"
                                placeholder="Nomor surat yang dibalas" />
                            <x-form-date name="tanggal_surat_masuk" label="Tanggal Surat Masuk" :value="old('tanggal_surat_masuk')" />
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Warning: Event Required After Surat --}}
            <div x-show="selectedJenis && ['SKMT','SKPD'].includes(selectedJenis)" x-cloak
                class="mb-6 rounded-lg border-l-4 border-amber-400 bg-amber-50 p-5 shadow-sm">
                <div class="flex gap-4">
                    <div class="shrink-0 mt-0.5">
                        {{-- Warning icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-amber-800">
                            Perhatian: Jangan Lupa Input Event Setelah Surat Diterbitkan
                        </h4>
                        <div x-show="selectedJenis && selectedJenis === 'SKMT'">
                            <p class="text-sm text-amber-700 mt-1">
                                Surat Keterangan Kematian hanya dokumen administrasi awal. Setelah surat diterbitkan,
                                Anda <strong>wajib</strong> menginput <strong>Event Kematian</strong> agar:
                            </p>
                            <ul class="list-disc pl-5 mt-2 text-sm text-amber-700 space-y-1">
                                <li>Status kependudukan penduduk berubah menjadi <strong>Non-Aktif</strong></li>
                                <li>Data populasi desa terupdate secara akurat</li>
                                <li>Riwayat kejadian tercatat di sistem</li>
                            </ul>
                            <a href="{{ route('events.kematian.create') }}"
                                class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-amber-800 underline underline-offset-2 hover:text-amber-900">
                                Buka Form Input Event Kematian
                            </a>
                        </div>
                        <div x-show="selectedJenis && selectedJenis === 'SKPD'">
                            <p class="text-sm text-amber-700 mt-1">
                                Surat Keterangan Pindah/Datang hanya dokumen administrasi awal. Setelah surat
                                diterbitkan,
                                Anda <strong>wajib</strong> menginput <strong>Event Pindah</strong> agar:
                            </p>
                            <ul class="list-disc pl-5 mt-2 text-sm text-amber-700 space-y-1">
                                <li>Status kependudukan penduduk berubah menjadi <strong>Pindah</strong></li>
                                <li>Data domisili penduduk terupdate</li>
                                <li>Riwayat mutasi tercatat di sistem</li>
                            </ul>
                            <a href="{{ route('events.pindah.create') }}"
                                class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold text-amber-800 underline underline-offset-2 hover:text-amber-900">
                                Buka Form Input Event Pindah
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Keterangan & Keperluan --}}
            <x-card class="mb-6">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Deskripsi & Keperluan</h3>
                </x-slot>

                <div class="space-y-6">
                    <x-form-textarea name="keperluan" label="Keperluan Pembuatan Surat" rows="3" required
                        placeholder="Contoh: Persyaratan administrasi perbankan" :value="old('keperluan')" />

                    <div>
                        <label for="keterangan_tambahan" class="form-label">
                            <span x-text="isBalasan() ? 'Isi Balasan Surat' : 'Keterangan Tambahan (Opsional)'"></span>
                            <span x-show="isBalasan()" class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan_tambahan" id="keterangan_tambahan" rows="5"
                            x-bind:required="isBalasan()"
                            x-bind:placeholder="isBalasan() ? 'Tulis isi/jawaban utama yang akan dicetak di badan surat balasan.' : 'Catatan tambahan bila diperlukan...'"
                            class="form-input-custom resize-none{{ $errors->has('keterangan_tambahan') ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : '' }}">{{ old('keterangan_tambahan') }}</textarea>
                        <p class="form-hint" x-show="isBalasan()" x-cloak>
                            Isi ini akan dicetak sebagai badan utama Surat Balasan Desa.
                        </p>
                        @error('keterangan_tambahan')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            {{-- Section 3: Opsi Penerbitan --}}
            <x-card class="mb-6">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-gray-900">Opsi Penerbitan</h3>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-form-date name="tanggal_terbit" label="Tanggal Terbit" required :value="old('tanggal_terbit', now()->format('Y-m-d'))" />
                    </div>
                    <div>
                        <x-form-input name="masa_berlaku_khusus" type="number" label="Masa Berlaku Khusus (Hari)"
                            placeholder="Kosongkan jika mengikuti default Jenis Surat" min="1" max="3650"
                            helper="Abaikan jika ingin menggunakan masa berlaku bawaan dari template."
                            :value="old('masa_berlaku_khusus')" />
                    </div>
                </div>
            </x-card>

            {{-- Form Actions --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <x-button type="button" variant="secondary" :href="route('surat.terbit.index')">
                        Batal
                    </x-button>

                    <div class="flex items-center gap-3">
                        <x-button type="submit" variant="primary" icon="save">
                            Terbitkan Surat
                        </x-button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
        <script>
            window.suratCreateFormDefaults = @js([
                'selectedJenis' => old('jenis_surat_kode', ''),
                'detailsUrl' => url('surat/jenis-surat'),
                'csrfToken' => csrf_token(),
                'oldDataSurat' => old('data_surat', []),
            ]);

            window.suratCreateForm = function () {
                const defaults = window.suratCreateFormDefaults || {};

                return {
                    selectedJenis: defaults.selectedJenis || '',
                    details: null,
                    isLoading: false,
                    detailsUrl: defaults.detailsUrl || '',
                    csrfToken: defaults.csrfToken || '',
                    oldDataSurat: defaults.oldDataSurat || {},

                    init() {
                        if (this.selectedJenis) {
                            this.fetchDetails();
                        }
                    },

                    async fetchDetails() {
                        if (!this.selectedJenis) {
                            this.details = null;
                            return;
                        }

                        this.isLoading = true;
                        const url = `${this.detailsUrl}/${this.selectedJenis}`;

                        try {
                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': this.csrfToken
                                },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                throw new Error(`Failed to fetch details: ${response.status}`);
                            }

                            this.details = await response.json();
                        } catch (error) {
                            this.details = null;
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    isInternal() {
                        return (this.details?.template_category || '').toLowerCase() === 'internal';
                    },

                    isBalasan() {
                        return (this.details?.kode || '').toUpperCase() === 'SBALASAN';
                    },

                    dynamicFields() {
                        const internalFields = ['kepada', 'alamat_tujuan', 'perihal', 'lampiran', 'nomor_rujukan', 'nomor_surat_masuk', 'tanggal_surat_masuk', 'isi_balasan'];
                        const fields = this.details?.dynamic_fields || [];

                        return this.isInternal()
                            ? fields.filter((field) => !internalFields.includes(field.name))
                            : fields;
                    }
                };
            };
        </script>
    @endpush
</x-app-layout>
