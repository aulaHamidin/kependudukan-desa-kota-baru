{{-- 
    This partial handles modals for KK member management.
    Works in two contexts:
    - Index page: loops through $kartuKeluargas collection
    - Show page: works with single $kartuKeluarga variable
--}}

@php
    // Normalize to always work with collection
    $items = isset($kartuKeluarga) ? [$kartuKeluarga] : $kartuKeluargas ?? [];
@endphp

{{-- Add Member & Leave Member Modals (per KK) --}}
@foreach ($items as $item)
    @php
        $useOldAdd = old('_modal') === 'add-member-' . $item->id;
    @endphp

    @can('create', \App\Models\KkMember::class)
        <x-modal.form :name="'add-member-' . $item->id" title="Tambah Anggota KK" subtitle="Tambahkan anggota ke KK {{ \App\Support\Masking::nik($item->no_kk) }}.">
            <form id="add-member-{{ $item->id }}-form" method="POST" action="{{ route('kk-member.store') }}"
                class="space-y-6">
                @csrf
                <input type="hidden" name="_modal" value="add-member-{{ $item->id }}">
                <input type="hidden" name="kartu_keluarga_id" value="{{ $item->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- <x-form-select-searchable name="penduduk_id" label="Penduduk" required remote-url="route('')"
                        value-field="id" label-field="label" placeholder="Ketik nama atau NIK penduduk..." :min-chars="2"
                        :value="$useOldAdd ? old('penduduk_id') : null" /> --}}
                    <x-form-select-searchable name="penduduk_id" label="Penduduk"
                        placeholder="Ketik nama atau NIK penduduk..."
                        remote-url="{{ route('search.penduduk') }}?exclude_has_kk=true" :min-chars="2" :value="$useOldAdd ? old('penduduk_id') : null"
                        required />
                    <x-form-select name="hubungan_keluarga_code" label="Hubungan" required :options="$hubunganOptions"
                        :value="$useOldAdd ? old('hubungan_keluarga_code') : null" />

                    <x-form-select name="is_kepala_keluarga" label="Kepala Keluarga" :options="[0 => 'Tidak', 1 => 'Ya']" :value="$useOldAdd ? old('is_kepala_keluarga') : 0" />
                    <x-form-date name="tanggal_masuk" label="Tanggal Masuk" required :value="$useOldAdd ? old('tanggal_masuk') : now()->format('Y-m-d')" />

                    <x-form-select name="status" label="Status" :options="['AKTIF' => 'Aktif', 'KELUAR' => 'Keluar']" :value="$useOldAdd ? old('status') : 'AKTIF'" />
                </div>
            </form>

            <x-slot name="footer">
                <x-button type="button" variant="secondary"
                    x-on:click="$dispatch('close-modal', 'add-member-{{ $item->id }}')">
                    Batal
                </x-button>
                <x-button type="submit" icon="save" form="add-member-{{ $item->id }}-form">
                    Simpan Anggota
                </x-button>
            </x-slot>
        </x-modal.form>
    @endcan

    @foreach ($item->kkMembers as $member)
        @php
            $useOldLeave = old('_modal') === 'leave-member-' . $member->id;
            $useOldSetKepala = old('_modal') === 'set-kepala-' . $member->id;
        @endphp
        @can('update', $member)
            {{-- Set Kepala Keluarga Modal --}}
            @if (!$member->is_kepala_keluarga && $member->status === 'AKTIF')
                <x-modal.form :name="'set-kepala-' . $member->id" title="Set Kepala Keluarga"
                    subtitle="Jadikan {{ $member->penduduk?->nama_lengkap ?? 'anggota ini' }} sebagai kepala keluarga.">
                    <form id="set-kepala-{{ $member->id }}-form" method="POST"
                        action="{{ route('kk-member.set-kepala', $member) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_modal" value="set-kepala-{{ $member->id }}">

                        <div
                            class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <p class="text-sm text-amber-800">
                                Jika KK ini sudah memiliki kepala keluarga, maka kepala keluarga lama akan diganti dengan
                                anggota ini.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">Nama: <span
                                    class="font-medium text-gray-900">{{ $member->penduduk?->nama_lengkap ?? '-' }}</span>
                            </p>
                            <p class="text-sm text-gray-600">Hubungan Saat Ini: <span
                                    class="font-medium text-gray-900">{{ $member->hubunganKeluarga?->nama ?? '-' }}</span>
                            </p>
                        </div>
                    </form>

                    <x-slot name="footer">
                        <x-button type="button" variant="secondary"
                            x-on:click="$dispatch('close-modal', 'set-kepala-{{ $member->id }}')">
                            Batal
                        </x-button>
                        <x-button type="submit" icon="user-check" form="set-kepala-{{ $member->id }}-form">
                            Set sebagai Kepala KK
                        </x-button>
                    </x-slot>
                </x-modal.form>
            @endif

            {{-- Leave Member Modal --}}
            <x-modal.form :name="'leave-member-' . $member->id" title="Keluarkan Anggota" subtitle="Catat keluarnya anggota dari KK.">
                <form id="leave-member-{{ $member->id }}-form" method="POST"
                    action="{{ route('kk-member.leave', $member) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_modal" value="leave-member-{{ $member->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-date name="tanggal_keluar" label="Tanggal Keluar" required :value="$useOldLeave ? old('tanggal_keluar') : now()->format('Y-m-d')" />
                        <x-form-select name="status" label="Status" :options="['KELUAR' => 'Keluar']" :value="$useOldLeave ? old('status') : 'KELUAR'" />
                    </div>

                    <x-form-textarea name="alasan_keluar" label="Alasan" rows="3" :value="$useOldLeave ? old('alasan_keluar') : null" />
                </form>

                <x-slot name="footer">
                    <x-button type="button" variant="secondary"
                        x-on:click="$dispatch('close-modal', 'leave-member-{{ $member->id }}')">
                        Batal
                    </x-button>
                    <x-button type="submit" icon="save" form="leave-member-{{ $member->id }}-form">
                        Simpan
                    </x-button>
                </x-slot>
            </x-modal.form>
        @endcan
    @endforeach
@endforeach

{{-- Create KK Modal (only in index context) --}}
@if (isset($kartuKeluargas))
    @can('create', \App\Models\KartuKeluarga::class)
        <x-modal.form name="create-kk" title="Tambah Kartu Keluarga" subtitle="Lengkapi data KK baru.">
            <form id="create-kk-form" method="POST" action="{{ route('kartu-keluarga.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="_modal" value="create-kk">

                @include('data_inti.kartu_keluarga.partials.form-fields', [
                    'item' => null,
                    'rtOptions' => $rtOptions,
                    'statusKkOptions' => $statusKkOptions,
                    'useOld' => true,
                ])
            </form>

            <x-slot name="footer">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-kk')">
                    Batal
                </x-button>
                <x-button type="submit" icon="save" form="create-kk-form">
                    Simpan KK
                </x-button>
            </x-slot>
        </x-modal.form>
    @endcan
@endif
