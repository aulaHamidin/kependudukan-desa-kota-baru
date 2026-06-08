{{-- Data Peristiwa - Kematian Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Kematian', 'url' => route('events.kematian.index')],
            ['label' => 'Detail', 'url' => route('events.kematian.show', $event)],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Edit Event Kematian" subtitle="Perbarui data kematian penduduk" />
    </x-slot>

    @php
        $kematian = $event->eventKematian;
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
                    <li>RT, data penduduk, dan KK tidak dapat diubah setelah event dibuat</li>
                    <li>Perubahan data akan langsung memperbarui record event</li>
                    <li>Kolom bertanda <span class="text-rose-500 font-bold">*</span> wajib diisi</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.kematian.update', $event) }}" class="space-y-6"
        x-data="{
            pelaporMode: '{{ old('pelapor_id', $kematian?->pelapor_id) ? 'penduduk' : (old('nama_pelapor', $kematian?->nama_pelapor) ? 'manual' : 'penduduk') }}',
        }">
        @csrf
        @method('PUT')

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
                {{-- RT: Read-only --}}
                <div>
                    <label class="form-label">
                        RT <span class="text-gray-400 text-xs font-normal">(tidak dapat diubah)</span>
                    </label>
                    <p
                        class="text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        RT {{ $event->rt->nomor_rt }} / RW {{ $event->rt->rw->nomor_rw }}
                    </p>
                </div>

                <x-form-input name="event_date" type="date" label="Tanggal Meninggal" :value="old('event_date', $event->event_date?->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan" label="Keterangan" rows="2"
                    placeholder="Keterangan tambahan (opsional)" :value="old('keterangan', $event->keterangan)" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 2: Data Almarhum (Read-only) --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Almarhum/ah</h3>
                        <p class="text-sm text-gray-500">Data penduduk dan KK tidak dapat diubah</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Penduduk</label>
                    <p class="text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk?->nik ?? '') }}</span> —
                        {{ $event->penduduk?->nama_lengkap ?? '-' }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Kartu
                        Keluarga</label>
                    <p class="text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 font-mono">
                        {{ \App\Support\Masking::nik($event->kartuKeluarga?->no_kk ?? '') }}
                    </p>
                </div>
            </div>
            <p class="form-hint mt-2">
                Untuk mengubah penduduk atau KK, hapus event ini dan buat ulang.
            </p>
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
                    :value="old('tempat_meninggal', $kematian?->tempat_meninggal)" required />

                <x-form-input name="jam_meninggal" type="text" label="Jam Meninggal"
                    placeholder="Contoh: 09:15 atau 14:30" :value="old('jam_meninggal', $kematian?->jam_meninggal)" pattern="([0-1]?[0-9]|2[0-3]):[0-5][0-9]"
                    helper="Format 24 jam (00:00 - 23:59). Contoh: 09:15 (pagi) atau 14:30 (siang)" />

                <x-form-input name="sebab_kematian" label="Sebab Kematian" placeholder="Contoh: Sakit, Kecelakaan, dll."
                    :value="old('sebab_kematian', $kematian?->sebab_kematian)" />

                <x-form-input name="penyakit" label="Penyakit" placeholder="Nama penyakit jika sebab sakit"
                    :value="old('penyakit', $kematian?->penyakit)" />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan_kematian" label="Keterangan Kematian" rows="2"
                    placeholder="Keterangan tambahan tentang kematian (opsional)" :value="old('keterangan_kematian', $kematian?->keterangan_kematian)" />
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
                    remote-url="{{ route('search.penduduk') }}?status=AKTIF" :min-chars="2" :value="old('pelapor_id', $kematian?->pelapor_id)"
                    x-bind:disabled="pelaporMode !== 'penduduk'" />
                <p class="form-hint">
                    Pelapor harus penduduk aktif di RT yang sama dengan almarhum.
                </p>
            </div>

            {{-- Input Manual --}}
            <div x-show="pelaporMode === 'manual'" x-cloak>
                <x-form-input name="nama_pelapor" label="Nama Pelapor (Manual)" placeholder="Nama lengkap pelapor"
                    :value="old('nama_pelapor', $kematian?->nama_pelapor)" x-bind:disabled="pelaporMode !== 'manual'" />
                <p class="form-hint">
                    Isi jika pelapor bukan penduduk desa atau tidak terdaftar.
                </p>
            </div>

            {{-- Hubungan Pelapor --}}
            <div class="mt-4">
                <x-form-select-searchable name="hubungan_pelapor_code" label="Hubungan dengan Almarhum"
                    placeholder="Pilih hubungan keluarga..." :options="$hubunganOptions" :min-chars="1"
                    :value="old('hubungan_pelapor_code', $kematian?->hubungan_pelapor_code)" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 5: Info Pengganti Kepala (Read-only di edit) --}}
        {{-- ============================================================ --}}
        @if ($kematian?->was_kepala)
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">5</span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Pengganti Kepala Keluarga</h3>
                            <p class="text-sm text-gray-500">Data pengganti tidak dapat diubah setelah event dibuat</p>
                        </div>
                    </div>
                </x-slot>

                @if ($kematian->pengganti)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <p class="text-sm text-gray-900 font-medium">{{ $kematian->pengganti->nama_lengkap }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ \App\Support\Masking::nik($kematian->pengganti->nik) }}</p>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <p class="text-sm text-amber-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tidak ada pengganti kepala yang ditunjuk. KK telah dinonaktifkan.
                        </p>
                    </div>
                @endif

                <p class="form-hint mt-2">
                    Untuk mengubah pengganti kepala, hapus event ini dan buat ulang.
                </p>
            </x-card>
        @endif

        {{-- Form Actions --}}
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pastikan semua data sudah benar sebelum menyimpan
                </p>
                <div class="flex gap-3">
                    <x-button type="button" variant="secondary" :href="route('events.kematian.show', $event)">
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
