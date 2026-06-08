{{-- Surat - Terbit Batalkan --}}
<x-app-layout>
    <x-slot name="title">Batalkan Surat: {{ $suratTerbit->nomor_surat ?? 'Terbit' }}</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Terbit', 'url' => route('surat.terbit.index')],
            ['label' => 'Detail', 'url' => route('surat.terbit.show', $suratTerbit)],
            ['label' => 'Batalkan'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Batalkan Surat Terbit" subtitle="Konfirmasi pembatalan surat yang sudah diterbitkan.">
            <x-slot name="actions">
                <x-button href="{{ route('surat.terbit.show', $suratTerbit) }}" variant="secondary" icon="arrow-left">
                    Kembali ke Detail
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Danger Banner --}}
        <div
            class="relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 via-red-600 to-rose-700 p-6 shadow-lg">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 400 150" fill="none">
                    <circle cx="350" cy="20" r="60" fill="white" opacity="0.3" />
                    <circle cx="50" cy="130" r="50" fill="white" opacity="0.2" />
                </svg>
            </div>
            <div class="relative flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Peringatan: Tindakan Tidak Dapat Dibatalkan</h3>
                    <p class="mt-1 text-sm text-red-100 leading-relaxed">
                        Anda akan membatalkan surat ini secara permanen. Surat akan dinyatakan
                        <strong class="text-white">TIDAK BERLAKU</strong> dan file PDF akan dihapus dari server.
                    </p>
                </div>
            </div>
        </div>

        {{-- Surat Info Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Rincian Surat yang Akan Dibatalkan</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Nomor Surat</p>
                        <p class="text-sm font-bold text-gray-800 font-mono">
                            {{ $suratTerbit->nomor_surat ?? 'Belum ada nomor' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Jenis Surat</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $suratTerbit->jenisSurat->nama ?? $suratTerbit->jenis_surat_kode }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Pemohon</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $suratTerbit->penduduk->nama_lengkap ?? 'Tidak diketahui' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Formulir Pembatalan</h3>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('surat.terbit.batalkan', $suratTerbit) }}" class="space-y-5">
                    @csrf

                    {{-- Alasan --}}
                    <div>
                        <x-form-textarea name="alasan_batal" label="Alasan Pembatalan" rows="3" required
                            placeholder="Misal: Terdapat kesalahan penulisan identitas pemohon, atau pergantian pemohon..."
                            :value="old('alasan_batal')"
                            helper="Alasan ini wajib diisi dan akan dicatat di database sebagai rekam jejak riwayat dokumen." />
                    </div>

                    {{-- Checkbox Konfirmasi --}}
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <label for="konfirmasi_batal" class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="konfirmasi_batal" id="konfirmasi_batal" value="1"
                                class="mt-0.5 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                {{ old('konfirmasi_batal') ? 'checked' : '' }} required>
                            <span class="text-sm text-red-800 leading-relaxed">
                                <strong>Saya memahami dan mengkonfirmasi</strong> bahwa pembatalan surat ini
                                akan membuat dokumen tidak berlaku dan menghapus file PDF secara permanen.
                                Tindakan ini tidak dapat dibatalkan.
                            </span>
                        </label>
                        @error('konfirmasi_batal')
                            <p class="mt-2 text-xs text-red-600 ml-7">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dampak info --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                        <div class="text-xs text-amber-800 space-y-1">
                            <p class="font-semibold">Dampak pembatalan:</p>
                            <ul class="list-disc pl-4 space-y-0.5 text-amber-700">
                                <li>Status surat berubah menjadi <strong>BATAL</strong></li>
                                <li>File PDF dihapus permanen dari server</li>
                                <li>Surat tidak dapat diunduh lagi</li>
                                <li>Alasan pembatalan dicatat di audit log</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <x-button variant="secondary" :href="route('surat.terbit.show', $suratTerbit)">
                            Batal
                        </x-button>
                        <x-button type="submit" variant="danger" icon="trash">
                            Ya, Batalkan Surat Ini
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
