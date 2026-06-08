<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SIAK-Desa' }} — {{ config('app.name', 'SIAK-Desa') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Page Specific Styles -->
    @stack('styles')

    <style>
        /* Page entrance animation */
        @keyframes page-fade-in {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-enter {
            animation: page-fade-in 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* Prevent flash of unstyled content */
        body.loading {
            opacity: 0;
        }
    </style>
</head>

<body class="bg-gray-100 antialiased text-gray-800 loading">
    {{-- Toast/Alert Component untuk SweetAlert2 --}}
    <x-alert />

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- Main Content Wrapper --}}
    <div id="main-content" class="min-h-screen flex flex-col page-enter">
        {{-- Navbar --}}
        <x-navbar>
            @if (isset($breadcrumb))
                <x-slot name="breadcrumb">{{ $breadcrumb }}</x-slot>
            @endif
        </x-navbar>

        {{-- Page Content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 pt-4 pb-6 max-w-[1400px] w-full mx-auto">

            {{-- Page Header --}}
            @if (isset($header))
                <div class="mb-4">
                    {{ $header }}
                </div>
            @endif

            {{-- Supports both @extends + @section('content') AND component <x-app-layout> --}}
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
        {{-- Footer --}}
        <x-footer />
    </div>

    {{-- Global Scripts --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const overlay = document.getElementById('sidebar-overlay');
            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                sidebar.classList.toggle('open');
                overlay?.classList.toggle('hidden');
            } else {
                // Use requestAnimationFrame to ensure both animations start together
                requestAnimationFrame(() => {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('sidebar-collapsed');
                });
            }
        }

        // Initialize sidebar state on page load
        window.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.getElementById('main-content');

            if (window.innerWidth < 1024) {
                // Mobile: no margin
                mainContent.classList.add('sidebar-collapsed');
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth >= 1024) {
                // Desktop mode
                overlay?.classList.add('hidden');
                sidebar.classList.remove('open');

                if (!sidebar.classList.contains('collapsed')) {
                    mainContent.classList.remove('sidebar-collapsed');
                } else {
                    mainContent.classList.add('sidebar-collapsed');
                }
            } else {
                // Mobile mode
                mainContent.classList.add('sidebar-collapsed');
                if (!sidebar.classList.contains('open')) {
                    overlay?.classList.add('hidden');
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.remove('open');
                overlay?.classList.add('hidden');
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebar-overlay')?.classList.add('hidden');
            }
        });
    </script>

    {{-- Page entrance reveal --}}
    <script>
        document.body.classList.remove('loading');
        // Remove page-enter class after animation ends to prevent
        // transform from breaking fixed positioning (modals, alerts)
        const pageEnterEl = document.querySelector('.page-enter');
        if (pageEnterEl) {
            pageEnterEl.addEventListener('animationend', () => {
                pageEnterEl.classList.remove('page-enter');
            }, {
                once: true
            });
        }
    </script>

    {{-- Page Specific Scripts --}}
    @stack('scripts')
</body>

</html>
