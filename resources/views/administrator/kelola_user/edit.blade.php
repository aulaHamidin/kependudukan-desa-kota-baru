{{-- Administrator - Kelola User Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Administrator', 'url' => '#'],
            ['label' => 'Kelola User', 'url' => route('kelola-user.index')],
            ['label' => $user->name ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit User" subtitle="Perbarui data pengguna." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir edit user sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
