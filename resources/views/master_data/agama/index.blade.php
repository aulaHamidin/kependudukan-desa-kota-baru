<x-app-layout>
    <x-slot name="title">Data Agama</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Referensi'], ['label' => 'Agama']]" />
    </x-slot>
        <x-page-header title="Data Agama" subtitle="Kelola data master agama dalam sistem">
        </x-page-header>

    <x-alert />

    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5">
        <div class="flex items-start gap-4">
            <div
                class="w-11 h-11 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.083.81l-.012.02a.75.75 0 01-1.083-.81z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 18.75h.008v.008H12v-.008zM12 6.75h.008v.008H12v-.008z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.75a5.25 5.25 0 00-5.25 5.25v3a5.25 5.25 0 0010.5 0v-3A5.25 5.25 0 0012 6.75z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Data agama bersifat tetap</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Data agama berasal dari seed dan tidak bisa ditambah, diubah, atau dihapus. Silakan hubungi
                    admin jika ada kebutuhan khusus.
                </p>
            </div>
        </div>
    </div>

    @include('master_data.agama.partials.table', ['items' => $items])
</x-app-layout>
