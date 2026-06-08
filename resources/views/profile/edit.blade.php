<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Profile']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Profil Saya" subtitle="Kelola informasi akun dan keamanan Anda." />
    </x-slot>

    <x-alert />

    @push('styles')
        <style>
            .profile-avatar {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                font-weight: 700;
                color: #fff;
                background: linear-gradient(135deg, #1e40af, #3b82f6);
                box-shadow: 0 4px 14px rgba(30, 64, 175, 0.3);
                flex-shrink: 0;
            }

            .profile-header-card {
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                border: 1px solid #bfdbfe;
            }

            .profile-meta-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 0.75rem;
                background: #fff;
                border-radius: 0.5rem;
                border: 1px solid #e5e7eb;
                font-size: 0.8125rem;
            }

            .profile-meta-item svg {
                width: 16px;
                height: 16px;
                color: #6b7280;
                flex-shrink: 0;
            }

            .profile-section-icon {
                width: 36px;
                height: 36px;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .profile-section-icon svg {
                width: 20px;
                height: 20px;
            }

            .profile-section-icon.blue {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .profile-section-icon.amber {
                background: #fef3c7;
                color: #b45309;
            }
        </style>
    @endpush

    {{-- Profile Header Card --}}
    <div class="profile-header-card rounded-lg p-5 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-gray-900 truncate">{{ $user->name }}</h2>
                <p class="text-sm text-gray-600 mt-0.5">{{ $user->email ?? '-' }}</p>
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="badge {{ $user->is_active ? 'badge-aktif' : 'badge-pending' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span class="badge badge-village">{{ $user->role_label }}</span>
                </div>
            </div>
        </div>

        {{-- Meta info row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mt-4">
            <div class="profile-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span class="text-gray-500">Username:</span>
                <span class="font-medium text-gray-800">{{ $user->username }}</span>
            </div>
            @php
                $wilayahParts = [];
                if ($user->desa) {
                    $wilayahParts[] = $user->desa->nama;
                }
                if ($user->rw) {
                    $wilayahParts[] = 'RW ' . $user->rw->nomor_rw;
                }
                if ($user->rt) {
                    $wilayahParts[] = 'RT ' . $user->rt->nomor_rt;
                }
                $wilayahLabel = $wilayahParts ? implode(', ', $wilayahParts) : '-';
            @endphp
            <div class="profile-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span class="text-gray-500">Wilayah:</span>
                <span class="font-medium text-gray-800 truncate">{{ $wilayahLabel }}</span>
            </div>
            <div class="profile-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-gray-500">Login terakhir:</span>
                <span class="font-medium text-gray-800">{{ $user->last_login_at_label }}</span>
            </div>
            <div class="profile-meta-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <span class="text-gray-500">Bergabung:</span>
                <span
                    class="font-medium text-gray-800">{{ $user->created_at ? $user->created_at->translatedFormat('d M Y') : '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Two-column layout for forms --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Update Profile Information --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="profile-section-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Informasi Akun</h3>
                        <p class="text-xs text-gray-500">Perbarui nama dan email Anda</p>
                    </div>
                </div>
            </x-slot>
            @include('profile.partials.update-profile-information-form')
        </x-card>

        {{-- Update Password --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="profile-section-icon amber">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Ubah Password</h3>
                        <p class="text-xs text-gray-500">Gunakan password yang kuat dan unik</p>
                    </div>
                </div>
            </x-slot>
            @include('profile.partials.update-password-form')
        </x-card>
    </div>
</x-app-layout>
