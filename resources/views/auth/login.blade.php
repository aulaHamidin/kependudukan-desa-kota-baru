<x-guest-layout>
    <div x-data="{ submitting: false }">
        <div class="mb-6">
            {{-- Logo --}}
            <div class="flex justify-center mb-5">
                <a href="/" class="flex flex-col items-center gap-2 no-underline">
                    <img src="{{ asset('images/logo-desa.png') }}" alt="Logo Desa Kota Baru"
                        class="h-16 w-16 object-contain">
                    <div class="text-center">
                        <p class="text-[10px] font-bold uppercase" style="letter-spacing: 0.2em; color: #1e40af;">
                            SIAK-DESA</p>
                        <p class="text-sm font-bold text-gray-900">Desa Kota Baru</p>
                    </div>
                </a>
            </div>
            <div class="text-center">
                <h1 class="text-xl font-bold text-gray-900">Masuk ke Akun</h1>
                <p class="mt-1.5 text-sm leading-relaxed text-gray-500">
                    Gunakan akun yang telah terdaftar untuk mengakses layanan kependudukan.
                </p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit="submitting = true">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}"
                    required autofocus autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                <input id="password" class="auth-input" type="password" name="password" required
                    autocomplete="current-password" placeholder="Masukkan password">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <!-- Remember Me & Forgot -->
            <div class="flex items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input id="remember_me" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" name="remember">
                    <span>Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold hover:underline" style="color: #1e40af;"
                        href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>

            <button type="submit" class="auth-btn auth-btn-primary" :disabled="submitting">
                <template x-if="submitting">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </template>
                <template x-if="!submitting">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </template>
                <span x-text="submitting ? 'Memproses...' : 'Masuk'"></span>
            </button>
        </form>

        <div class="mt-6 pt-5" style="border-top: 1px solid #e5e7eb;">
            <p class="text-sm text-gray-500 text-center">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold hover:underline" style="color: #1e40af;">
                    Daftar di sini
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
