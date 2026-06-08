{{-- Shared: Approval Action Buttons & Reject Modal --}}
{{-- Usage: @include('data_peristiwa.partials.approval-actions', ['event' => $event]) --}}
{{-- Ditampilkan pada halaman show event yang berstatus DRAFT --}}

@can('verify', $event)
    @if ($event->status_data === 'DRAFT')
        <div class="bg-white rounded-xl border border-indigo-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Tindakan Persetujuan</h3>
                        <p class="text-xs text-gray-500">Data ini menunggu verifikasi dari Anda</p>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="flex items-center gap-3">
                    {{-- Approve Button --}}
                    <form method="POST" action="{{ route('approvals.approve', $event) }}" class="inline"
                        x-data="swalConfirm(@js([
    'title' => 'Setujui Peristiwa?',
    'text' => 'Data peristiwa ' . ($event->eventType->nama ?? $event->event_type_code) . ' atas nama ' . ($event->penduduk?->nama_lengkap ?? '-') . ' akan disetujui dan diverifikasi.',
    'confirmText' => 'Ya, Setujui',
    'cancelText' => 'Batal',
]))" @submit.prevent="submit($event)">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Setujui
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <x-modal name="reject-event-{{ $event->id }}" title="Tolak Data Peristiwa" max-width="md">
            <form method="POST" action="{{ route('approvals.reject', $event) }}">
                @csrf

                <div class="space-y-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800">Penolakan Data</h4>
                                <p class="text-sm text-red-700 mt-1">
                                    Berikan alasan penolakan agar petugas dapat memperbaiki data ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    <x-form-textarea name="rejection_reason" label="Alasan Penolakan" rows="4"
                        placeholder="Jelaskan alasan penolakan data ini..." required :value="old('rejection_reason')" />
                    @error('rejection_reason')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <x-button type="button" variant="secondary"
                        x-on:click="$dispatch('close-modal', 'reject-event-{{ $event->id }}')">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="danger">
                        <i class="fas fa-times-circle mr-2"></i>
                        Tolak Data
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endif
@endcan
