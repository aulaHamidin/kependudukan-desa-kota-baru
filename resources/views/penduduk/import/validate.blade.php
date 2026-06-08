{{-- Import Penduduk - Validation Results --}}
<x-app-layout>
    <x-slot name="title">Hasil Validasi Import</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb
            :items="[
                ['label' => 'Kependudukan', 'url' => '#'],
                ['label' => 'Import Penduduk', 'url' => route('penduduk.import.index')],
                ['label' => 'Hasil Validasi'],
            ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Hasil Validasi Import"
            subtitle="Periksa hasil validasi data sebelum mengimpor ke database.">
        </x-page-header>
    </x-slot>

    @php
        $isValid = $result['valid'];
        $errors = $result['errors'];
        $summary = $result['summary'];
    @endphp

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card :value="$summary['total_rows'] ?? 0" label="Total Baris" icon="users" color="primary" />
        <x-stat-card :value="$summary['new_kk_count'] ?? 0" label="KK Baru" icon="plus" color="primary" />
        <x-stat-card :value="$summary['existing_kk_count'] ?? 0" label="KK Existing" icon="check" color="neutral" />
        <x-stat-card :value="$summary['error_count'] ?? 0" label="Error"
            icon="{{ ($summary['error_count'] ?? 0) > 0 ? 'alert' : 'check' }}"
            color="{{ ($summary['error_count'] ?? 0) > 0 ? 'danger' : 'success' }}" />
    </div>

    @if ($isValid)
        {{-- SUCCESS Banner --}}
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 shrink-0">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-800">Validasi Berhasil</p>
                    <p class="text-sm text-green-700">Semua {{ $summary['total_rows'] }} baris data valid dan siap
                        diimpor ke database.</p>
                </div>
            </div>
        </div>

        {{-- Preview Data Table --}}
        <x-card class="mb-6">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-800">Preview Data Penduduk Yang Akan Disimpan ke
                            Database</h3>
                    </div>
                    <span
                        class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">{{ min(10, $rows->count()) }}
                        dari {{ $rows->count() }} baris</span>
                </div>
            </x-slot>

            <div class="-m-4 sm:-m-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No KK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Lengkap</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    JK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hubungan</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    RT</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Agama</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($rows->take(10) as $index => $row)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-800 font-mono">
                                        {{ $row['no_kk'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-800 font-mono">
                                        {{ $row['nik'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                        {{ $row['nama_lengkap'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if (($row['jenis_kelamin'] ?? '') === 'L')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">L</span>
                                        @elseif(($row['jenis_kelamin'] ?? '') === 'P')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-pink-50 text-pink-700">P</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $row['hubungan_keluarga'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row['rt_id'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $row['agama'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($rows->count() > 10)
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500">
                            ... dan {{ $rows->count() - 10 }} baris lainnya
                        </p>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('penduduk.import.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batal
            </a>

            <form action="{{ route('penduduk.import.execute') }}" method="POST" x-data="{ submitting: false }">
                @csrf
                <button type="submit" :disabled="submitting" @click="submitting = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <svg x-show="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Menyimpan data...' : 'Simpan Ke Database'"></span>
                </button>
            </form>
        </div>
    @else
        {{-- ERROR Banner --}}
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 shrink-0">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-red-800">Validasi Gagal</p>
                    <p class="text-sm text-red-700">Ditemukan <strong>{{ $summary['error_count'] }}</strong> baris
                        dengan error. Perbaiki data di file Excel lalu upload ulang.</p>
                </div>
            </div>
        </div>

        {{-- Error Detail Table --}}
        <x-card class="mb-6">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-800">Detail Error Per Baris</h3>
                </div>
            </x-slot>

            <div class="-m-4 sm:-m-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Baris</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                    Kolom</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pesan Error</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($errors as $rowNum => $rowErrors)
                                @foreach ($rowErrors as $errorIdx => $error)
                                    <tr class="hover:bg-red-50/30 transition-colors">
                                        @if ($errorIdx === 0)
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-800"
                                                rowspan="{{ count($rowErrors) }}">
                                                <span
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-700 text-xs font-bold">{{ $rowNum }}</span>
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-sm">
                                            <code
                                                class="px-2 py-0.5 bg-gray-100 rounded text-xs text-gray-700 font-semibold">{{ $error['column'] }}</code>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-red-700">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('penduduk.import.template') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Template
            </a>

            <a href="{{ route('penduduk.import.index') }}"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Upload Ulang
            </a>
        </div>
    @endif
</x-app-layout>
