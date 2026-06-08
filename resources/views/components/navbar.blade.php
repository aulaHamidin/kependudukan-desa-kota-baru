<nav class="sticky top-0 z-30 border-b shadow-sm"
    style="background: linear-gradient(90deg, #003580 0%, #002a6b 100%); border-color: rgba(59, 130, 246, 0.3);">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-14">
        {{-- Left Side --}}
        <div class="flex items-center gap-3">
            {{-- Menu Toggle (Always visible) --}}
            <button onclick="toggleSidebar()" class="p-2 rounded-md text-blue-200 hover:bg-white/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Breadcrumb --}}
            @if (isset($breadcrumb))
                <div class="hidden sm:block text-white">
                    {{ $breadcrumb }}
                </div>
            @endif
        </div>

        {{-- Right Side --}}
        <div class="flex items-center gap-2">
            {{-- User Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-white/10 transition">
                    {{-- Avatar --}}
                    <div
                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-semibold border border-white/30">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    {{-- User Info --}}
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-white leading-tight">
                            {{ Auth::user()->name ?? 'User' }}
                        </p>
                        <p class="text-[11px] text-blue-200">
                            {{ Auth::user()->getRoleNames() ?? 'Admin' }}</p>
                    </div>
                    {{-- Chevron --}}
                    <svg class="w-4 h-4 text-blue-200 hidden sm:block" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" x-cloak
                    class="absolute right-0 mt-1 w-52 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-50">
                    {{-- User Info Header --}}
                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-800">
                            {{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-400">
                            {{ Auth::user()->email ?? 'user@example.com' }}</p>
                    </div>

                    {{-- Menu Items --}}
                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Profil Saya
                        </a>
                    </div>

                    {{-- Logout --}}
                    <div class="border-t border-gray-200 py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
