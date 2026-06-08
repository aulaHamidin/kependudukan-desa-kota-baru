{{-- Data Peristiwa - Kelahiran Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Kelahiran', 'url' => route('events.kelahiran.index')],
            ['label' => 'Detail', 'url' => route('events.kelahiran.show', $event)],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Edit Event Kelahiran" subtitle="Perbarui data kelahiran bayi" />
    </x-slot>

    @php
        $kelahiran = $event->eventKelahiran;
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
                    <li>RT tidak dapat diubah setelah event dibuat</li>
                    <li>Perubahan data akan langsung memperbarui record event</li>
                    <li>Kolom bertanda <span class="text-rose-500 font-bold">*</span> wajib diisi</li>
                </ul>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('events.kelahiran.update', $event) }}" class="space-y-6"
        x-data="{
            statusKelahiran: '{{ old('status_kelahiran', $kelahiran->status_kelahiran->value ?? $kelahiran->status_kelahiran) }}',
            penolongKelahiran: '{{ old('penolong_kelahiran', $kelahiran->penolong_kelahiran) }}',
            ayahMode: '{{ old('ayah_id', $kelahiran->ayah_id) ? 'penduduk' : (old('nama_ayah', $kelahiran->nama_ayah) ? 'manual' : 'penduduk') }}',
            ibuMode: '{{ old('ibu_id', $kelahiran->ibu_id) ? 'penduduk' : (old('nama_ibu', $kelahiran->nama_ibu) ? 'manual' : 'penduduk') }}',
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
                        <p class="text-sm text-gray-500">Lokasi dan tanggal peristiwa kelahiran</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- RT: Read-only, tidak bisa diubah --}}
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

                <x-form-input name="event_date" type="date" label="Tanggal Kelahiran" :value="old('event_date', $event->event_date?->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}" required />
            </div>

            <div class="mt-4">
                <x-form-textarea name="keterangan" label="Keterangan" rows="2"
                    placeholder="Keterangan tambahan (opsional)" :value="old('keterangan', $event->keterangan)" />
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 2: KK Tujuan --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">2</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Kartu Keluarga Tujuan</h3>
                        <p class="text-sm text-gray-500">KK tempat bayi didaftarkan (dapat diubah, harus di RT yang
                            sama)</p>
                    </div>
                </div>
            </x-slot>

            <x-form-select-searchable name="kk_tujuan_id" label="Kartu Keluarga Tujuan"
                placeholder="Ketik No. KK atau nama kepala keluarga..."
                remote-url="{{ route('search.kartu-keluarga') }}" :min-chars="2" :value="old('kk_tujuan_id', $kelahiran->kk_tujuan_id)" required />
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 3: Data Bayi --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">3</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Bayi</h3>
                        <p class="text-sm text-gray-500">Identitas dan kondisi bayi saat lahir</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input name="nama_bayi" label="Nama Bayi" placeholder="Nama lengkap bayi" :value="old('nama_bayi', $kelahiran->nama_bayi)"
                    required />

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="form-label">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex gap-6 mt-1">
                        <label class="inline-flex items-center cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="jenis_kelamin" value="L"
                                {{ old('jenis_kelamin', $kelahiran->jenis_kelamin) === 'L' ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500" required>
                            <span class="ml-2">Laki-laki</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="jenis_kelamin" value="P"
                                {{ old('jenis_kelamin', $kelahiran->jenis_kelamin) === 'P' ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2">Perempuan</span>
                        </label>
                    </div>
                    @error('jenis_kelamin')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Kelahiran --}}
                <div>
                    <label class="form-label">
                        Status Kelahiran <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex gap-6 mt-1">
                        <label class="inline-flex items-center cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status_kelahiran" value="HIDUP" x-model="statusKelahiran"
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500" required>
                            <span class="ml-2">Hidup</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="status_kelahiran" value="MATI" x-model="statusKelahiran"
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2">Lahir Mati</span>
                        </label>
                    </div>
                    @error('status_kelahiran')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <x-form-select-searchable name="agama_id" label="Agama" placeholder="Pilih agama..."
                    :options="$agamaOptions" :min-chars="1" :value="old('agama_id', $event->penduduk->agama_id ?? null)" required />
            </div>

            {{-- Alert untuk bayi lahir mati --}}
            <div x-show="statusKelahiran === 'MATI'" x-cloak
                class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-amber-500"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-amber-800">Perhatian: Bayi Lahir Mati</h4>
                        <p class="text-sm text-amber-700 mt-1">
                            Bayi yang lahir dalam keadaan meninggal <strong>tidak akan ditambahkan ke dalam Kartu
                                Keluarga</strong>.
                            Data hanya akan tercatat sebagai peristiwa kelahiran.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Detail Kelahiran --}}
            <div class="border-t border-gray-100 mt-5 pt-5">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-notes-medical text-gray-400 mr-1.5"></i>
                    Detail Kelahiran
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-input name="tempat_lahir" label="Tempat Lahir" placeholder="Nama kota/kabupaten"
                        :value="old('tempat_lahir', $kelahiran->tempat_lahir)" required />

                    <x-form-input name="jam_lahir" type="text" label="Jam Lahir"
                        placeholder="Contoh: 09:15 atau 14:30" :value="old('jam_lahir', \Illuminate\Support\Str::substr($kelahiran->jam_lahir, 0, 5))"
                        pattern="([0-1]?[0-9]|2[0-3]):[0-5][0-9]" helper="Format 24 jam (00:00 - 23:59)" />

                    <x-form-input name="anak_ke" type="number" label="Anak Ke" placeholder="Contoh: 1"
                        :value="old('anak_ke', $kelahiran->anak_ke)" min="1" max="20" helper="Urutan kelahiran anak (opsional)" />

                    <x-form-input name="berat_badan_kg" type="number" label="Berat Badan (kg)"
                        placeholder="Contoh: 3.2" :value="old('berat_badan_kg', $kelahiran->berat_badan_kg)" step="0.01" min="0" max="10"
                        helper="Berat bayi dalam kilogram (opsional)" />

                    <x-form-input name="panjang_badan_cm" type="number" label="Panjang Badan (cm)"
                        placeholder="Contoh: 48" :value="old('panjang_badan_cm', $kelahiran->panjang_badan_cm)" step="0.1" min="0" max="100"
                        helper="Panjang bayi dalam sentimeter (opsional)" />
                </div>
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 4: Data Orang Tua --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">4</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Orang Tua</h3>
                        <p class="text-sm text-gray-500">Pilih dari daftar penduduk atau isi manual jika bukan penduduk
                            desa</p>
                    </div>
                </div>
            </x-slot>

            {{-- Data Ayah --}}
            <div class="mb-6 pb-6 border-b border-gray-100">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                    <i class="fas fa-male text-blue-400 mr-1"></i>
                    Data Ayah <span class="text-rose-500">*</span>
                </label>

                {{-- Toggle Buttons --}}
                <div class="flex gap-2 mb-3">
                    <x-button type="button" size="sm" variant="secondary"
                        x-bind:class="ayahMode === 'penduduk' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                        @click="ayahMode = 'penduduk'">
                        <i class="fas fa-user mr-1.5"></i>
                        Dari Penduduk
                    </x-button>
                    <x-button type="button" size="sm" variant="secondary"
                        x-bind:class="ayahMode === 'manual' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                        @click="ayahMode = 'manual'">
                        <i class="fas fa-keyboard mr-1.5"></i>
                        Input Manual
                    </x-button>
                </div>

                {{-- Pilih dari Penduduk --}}
                <div x-show="ayahMode === 'penduduk'" x-cloak>
                    <x-form-select-searchable name="ayah_id" label="Ayah (Penduduk)"
                        placeholder="Ketik nama atau NIK ayah..."
                        remote-url="{{ route('search.penduduk') }}?jenis_kelamin=L&status=AKTIF" :min-chars="2"
                        :value="old('ayah_id', $kelahiran->ayah_id)" x-bind:required="ayahMode === 'penduduk'"
                        x-bind:disabled="ayahMode !== 'penduduk'" />
                </div>

                {{-- Input Manual --}}
                <div x-show="ayahMode === 'manual'" x-cloak>
                    <x-form-input name="nama_ayah" label="Nama Ayah (Manual)" placeholder="Nama lengkap ayah"
                        :value="old('nama_ayah', $kelahiran->nama_ayah)" x-bind:required="ayahMode === 'manual'"
                        x-bind:disabled="ayahMode !== 'manual'" />
                    <p class="form-hint">Isi jika ayah bukan penduduk desa atau tidak terdaftar.</p>
                </div>
            </div>

            {{-- Data Ibu --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                    <i class="fas fa-female text-pink-400 mr-1"></i>
                    Data Ibu <span class="text-rose-500">*</span>
                </label>

                {{-- Toggle Buttons --}}
                <div class="flex gap-2 mb-3">
                    <x-button type="button" size="sm" variant="secondary"
                        x-bind:class="ibuMode === 'penduduk' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                        @click="ibuMode = 'penduduk'">
                        <i class="fas fa-user mr-1.5"></i>
                        Dari Penduduk
                    </x-button>
                    <x-button type="button" size="sm" variant="secondary"
                        x-bind:class="ibuMode === 'manual' ? '!bg-primary-600 !text-white !border-primary-600' : ''"
                        @click="ibuMode = 'manual'">
                        <i class="fas fa-keyboard mr-1.5"></i>
                        Input Manual
                    </x-button>
                </div>

                {{-- Pilih dari Penduduk --}}
                <div x-show="ibuMode === 'penduduk'" x-cloak>
                    <x-form-select-searchable name="ibu_id" label="Ibu (Penduduk)"
                        placeholder="Ketik nama atau NIK ibu..."
                        remote-url="{{ route('search.penduduk') }}?jenis_kelamin=P&status=AKTIF" :min-chars="2"
                        :value="old('ibu_id', $kelahiran->ibu_id)" x-bind:required="ibuMode === 'penduduk'"
                        x-bind:disabled="ibuMode !== 'penduduk'" />
                </div>

                {{-- Input Manual --}}
                <div x-show="ibuMode === 'manual'" x-cloak>
                    <x-form-input name="nama_ibu" label="Nama Ibu (Manual)" placeholder="Nama lengkap ibu"
                        :value="old('nama_ibu', $kelahiran->nama_ibu)" x-bind:required="ibuMode === 'manual'"
                        x-bind:disabled="ibuMode !== 'manual'" />
                    <p class="form-hint">Isi jika ibu bukan penduduk desa atau tidak terdaftar.</p>
                </div>
            </div>
        </x-card>

        {{-- ============================================================ --}}
        {{-- Section 5: Data Kelahiran --}}
        {{-- ============================================================ --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">5</span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Penolong Kelahiran</h3>
                        <p class="text-sm text-gray-500">Informasi tentang tenaga penolong saat proses kelahiran
                            (opsional)</p>
                    </div>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-select name="penolong_kelahiran" label="Penolong Kelahiran" :value="old('penolong_kelahiran', $kelahiran->penolong_kelahiran)"
                    x-model="penolongKelahiran">
                    <option value="DOKTER"
                        {{ old('penolong_kelahiran', $kelahiran->penolong_kelahiran) === 'DOKTER' ? 'selected' : '' }}>
                        Dokter</option>
                    <option value="BIDAN"
                        {{ old('penolong_kelahiran', $kelahiran->penolong_kelahiran) === 'BIDAN' ? 'selected' : '' }}>
                        Bidan</option>
                    <option value="DUKUN"
                        {{ old('penolong_kelahiran', $kelahiran->penolong_kelahiran) === 'DUKUN' ? 'selected' : '' }}>
                        Dukun</option>
                    <option value="LAINNYA"
                        {{ old('penolong_kelahiran', $kelahiran->penolong_kelahiran) === 'LAINNYA' ? 'selected' : '' }}>
                        Lainnya</option>
                </x-form-select>

                <x-form-input name="nama_penolong" label="Nama Penolong"
                    placeholder="Nama lengkap penolong kelahiran" :value="old('nama_penolong', $kelahiran->nama_penolong)"
                    x-bind:disabled="!penolongKelahiran"
                    x-bind:class="!penolongKelahiran ? 'bg-gray-100 cursor-not-allowed' : ''"
                    helper="Akan aktif setelah memilih penolong kelahiran" />
            </div>
        </x-card>

        {{-- Form Actions --}}
        <x-card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pastikan semua data sudah benar sebelum menyimpan perubahan
                </p>
                <div class="flex gap-3">
                    <x-button type="button" variant="secondary" :href="route('events.kelahiran.show', $event)">
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
