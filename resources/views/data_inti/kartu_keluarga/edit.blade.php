{{-- Data Inti - Kartu Keluarga Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Inti', 'url' => '#'],
            ['label' => 'Kartu Keluarga', 'url' => route('kartu-keluarga.index')],
            ['label' => \App\Support\Masking::nik($kartuKeluarga->no_kk), 'url' => route('kartu-keluarga.show', $kartuKeluarga)],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Edit Kartu Keluarga" subtitle="Perbarui data kartu keluarga {{ \App\Support\Masking::nik($kartuKeluarga->no_kk) }}.">
        </x-page-header>
    </x-slot>

    <x-card>
        <form method="POST" action="{{ route('kartu-keluarga.update', $kartuKeluarga) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('data_inti.kartu_keluarga.partials.form-fields', [
                'item' => $kartuKeluarga,
                'rtOptions' => $rtOptions,
                'statusKkOptions' => $statusKkOptions,
                'useOld' => true,
            ])

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <x-button type="button" variant="secondary" :href="route('kartu-keluarga.show', $kartuKeluarga)">
                    Batal
                </x-button>
                <x-button type="submit" icon="save">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
