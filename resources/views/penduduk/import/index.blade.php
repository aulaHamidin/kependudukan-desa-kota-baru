{{-- Import Penduduk - Upload Page --}}
<x-app-layout>
    <x-slot name="title">Import Penduduk</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Kependudukan', 'url' => '#'], ['label' => 'Import Penduduk']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Import Data Penduduk"
            subtitle="Import data penduduk secara massal melalui file Excel atau CSV.">
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Section 1: Informasi & Download Template (side by side) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Panduan Import --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-800">Panduan Import Penduduk</h3>
                </div>
            </x-slot>

            <ol class="text-sm text-gray-600 space-y-2.5 list-decimal list-inside">
                <li><strong>Download template</strong> Excel terlebih dahulu menggunakan tombol di samping</li>
                <li>Isi data mulai dari <strong>baris 7</strong> ke bawah (hapus contoh baris 7-8)</li>
                <li>Lihat sheet <strong>"Kode Referensi"</strong> di file Excel untuk daftar kode yang valid</li>
                <li>Baris dengan <strong>no_kk</strong> yang sama akan dikelompokkan dalam 1 Kartu Keluarga</li>
                <li>Setiap KK baru <strong>wajib</strong> memiliki tepat 1 anggota
                    <code class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-semibold">KEPALA_KELUARGA</code>
                </li>
                <li>Upload file, lalu klik <strong>"Validasi Data"</strong></li>
                <li>Perbaiki error jika ada, lalu upload ulang</li>
                <li>Jika validasi berhasil, klik <strong>"Simpan Ke Database"</strong> untuk mengimpor data</li>
            </ol>

            {{-- Referensi Kode Cepat --}}
            <div class="mt-5 pt-4 border-t border-gray-100" x-data="{ openRef: '' }">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Referensi Kode</p>

                <div class="space-y-1">
                    {{-- Agama --}}
                    <div class="rounded-lg border border-gray-100 overflow-hidden">
                        <button @click="openRef = openRef === 'agama' ? '' : 'agama'" type="button"
                            class="w-full px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-between transition-colors">
                            <span>Kode Agama</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform"
                                :class="openRef === 'agama' ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openRef === 'agama'" x-collapse class="px-3 pb-2">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($agamaList as $item)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">
                                        <span class="font-semibold">{{ $item->kode }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Hubungan Keluarga --}}
                    <div class="rounded-lg border border-gray-100 overflow-hidden">
                        <button @click="openRef = openRef === 'hubungan' ? '' : 'hubungan'" type="button"
                            class="w-full px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-between transition-colors">
                            <span>Hubungan Keluarga</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform"
                                :class="openRef === 'hubungan' ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openRef === 'hubungan'" x-collapse class="px-3 pb-2">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($hubunganList as $item)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">
                                        <span class="font-semibold">{{ $item->kode }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Status Perkawinan --}}
                    <div class="rounded-lg border border-gray-100 overflow-hidden">
                        <button @click="openRef = openRef === 'perkawinan' ? '' : 'perkawinan'" type="button"
                            class="w-full px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-between transition-colors">
                            <span>Status Perkawinan</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform"
                                :class="openRef === 'perkawinan' ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openRef === 'perkawinan'" x-collapse class="px-3 pb-2">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (['BELUM_KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI'] as $status)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-semibold">
                                        {{ $status }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="rounded-lg border border-gray-100 overflow-hidden">
                        <button @click="openRef = openRef === 'jk' ? '' : 'jk'" type="button"
                            class="w-full px-3 py-2 text-left text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center justify-between transition-colors">
                            <span>Jenis Kelamin</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform"
                                :class="openRef === 'jk' ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openRef === 'jk'" x-collapse class="px-3 pb-2">
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">
                                    <span class="font-semibold">L</span>
                                    <span class="text-blue-500">Laki-laki</span>
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 bg-pink-50 text-pink-700 rounded text-xs">
                                    <span class="font-semibold">P</span>
                                    <span class="text-pink-500">Perempuan</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Download Template --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-800">Template Import</h3>
                </div>
            </x-slot>

            <div class="text-center">
                {{-- Template illustration --}}
                <div class="mb-4">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 text-green-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>

                <h4 class="text-sm font-semibold text-gray-800 mb-1">Download Template Excel</h4>
                <p class="text-xs text-gray-500 mb-5">
                    File template berisi format kolom, contoh pengisian, dan sheet referensi kode yang valid.
                </p>

                <a href="{{ route('penduduk.import.template') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Template (.xlsx)
                </a>
            </div>

            {{-- Template content info --}}
            <div class="mt-6 pt-4 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Isi Template</p>
                <div class="space-y-2">
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 h-5 w-5 rounded bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold">1</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-700">Sheet "Data Penduduk"</p>
                            <p class="text-xs text-gray-500">Header kolom + petunjuk pengisian + 2 baris contoh data</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 h-5 w-5 rounded bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold">2</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-700">Sheet "Kode Referensi"</p>
                            <p class="text-xs text-gray-500">Daftar lengkap kode agama, pendidikan, pekerjaan, golongan darah, hubungan keluarga</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Section 2: Upload File (full width) --}}
    <x-card class="mb-6">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <h3 class="text-sm font-semibold text-gray-800">Upload File Import</h3>
            </div>
        </x-slot>

        <form action="{{ route('penduduk.import.validate') }}" method="POST" enctype="multipart/form-data"
            x-data="{
                fileName: '',
                fileSize: '',
                dragging: false,
                submitting: false,
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                    }
                },
                handleDrop(e) {
                    this.dragging = false;
                    const file = e.dataTransfer.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                        this.$refs.fileInput.files = e.dataTransfer.files;
                    }
                },
                resetFile() {
                    this.fileName = '';
                    this.fileSize = '';
                    this.$refs.fileInput.value = '';
                }
            }"
            @submit="submitting = true">
            @csrf

            {{-- Drag & Drop Area --}}
            <div class="border-2 border-dashed rounded-xl p-10 text-center transition-all duration-200"
                :class="dragging ? 'border-blue-400 bg-blue-50/50 scale-[1.01]' :
                    (fileName ? 'border-green-300 bg-green-50/30' : 'border-gray-300 hover:border-blue-300 hover:bg-gray-50')"
                @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                @drop.prevent="handleDrop($event)">

                <template x-if="!fileName">
                    <div>
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 text-gray-400 mb-3">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">
                            Drag & drop file di sini, atau
                            <label for="file"
                                class="text-blue-600 hover:text-blue-700 font-semibold cursor-pointer underline decoration-blue-300 underline-offset-2">
                                pilih file
                            </label>
                        </p>
                        <p class="text-xs text-gray-400">Format yang diterima: .xlsx, .xls, .csv &mdash; Maksimal 5MB</p>
                    </div>
                </template>

                <template x-if="fileName">
                    <div class="flex items-center justify-center gap-4">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-green-100 text-green-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-gray-800" x-text="fileName"></p>
                            <p class="text-xs text-gray-500" x-text="fileSize"></p>
                        </div>
                        <button type="button" @click="resetFile()"
                            class="ml-2 p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                            title="Hapus file">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>

                <input type="file" name="file" id="file" x-ref="fileInput" accept=".xlsx,.xls,.csv"
                    class="hidden" @change="handleFile($event)">
            </div>

            @error('file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Submit Button --}}
            <div class="mt-5 flex items-center justify-end">
                <button type="submit" :disabled="!fileName || submitting"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg x-show="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Memvalidasi...' : 'Validasi Data'"></span>
                </button>
            </div>
        </form>
    </x-card>
</x-app-layout>
