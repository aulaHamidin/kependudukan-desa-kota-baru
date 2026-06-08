<x-guest-layout>
    <div x-data="{
        step: 1,
        nik: '{{ old('nik', '') }}',
        loading: false,
        submitting: false,
        error: '',
        penduduk: null,
        nikVerified: false,
    
        async checkNik() {
            if (this.nik.length !== 16 || !/^\d{16}$/.test(this.nik)) {
                this.error = 'NIK harus 16 digit angka.';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route('register.check-nik') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nik: this.nik })
                });
                const data = await res.json();
                if (res.ok && data.available) {
                    this.penduduk = data.penduduk;
                    this.nikVerified = true;
                    this.step = 2;
                } else {
                    this.error = data.message || 'NIK tidak dapat digunakan untuk registrasi.';
                }
            } catch (e) {
                this.error = 'Terjadi kesalahan jaringan. Coba lagi.';
            } finally {
                this.loading = false;
            }
        },
    
        resetNik() {
            this.step = 1;
            this.nikVerified = false;
            this.penduduk = null;
            this.error = '';
        }
    }">
        {{-- Header with Logo --}}
        <div class="mb-6">
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
            <div class="text-center mb-5">
                <h1 class="text-xl font-bold text-gray-900">Daftar Akun Baru</h1>
            </div>

            {{-- Progress Steps --}}
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold text-white"
                        :style="step >= 1 ? 'background: #1e40af;' : 'background: #d1d5db;'">1</div>
                    <span class="text-xs font-semibold"
                        :style="step >= 1 ? 'color: #1e40af;' : 'color: #9ca3af;'">Verifikasi NIK</span>
                </div>
                <div class="flex-1 h-0.5 mx-1" :style="step >= 2 ? 'background: #1e40af;' : 'background: #e5e7eb;'">
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                        :style="step >= 2 ? 'background: #1e40af; color: white;' : 'background: #e5e7eb; color: #9ca3af;'">
                        2</div>
                    <span class="text-xs font-semibold" :style="step >= 2 ? 'color: #1e40af;' : 'color: #9ca3af;'">Buat
                        Akun</span>
                </div>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- STEP 1: Verifikasi NIK --}}
        {{-- ======================== --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

            <p class="text-sm text-gray-500 mb-4">
                Masukkan NIK (Nomor Induk Kependudukan) 16 digit Anda. Sistem akan memverifikasi data Anda dalam
                database kependudukan desa.
            </p>

            {{-- Error Message --}}
            <div x-show="error" x-cloak class="mb-4 rounded-lg p-3"
                style="background: #fef2f2; border: 1px solid #fecaca;">
                <div class="flex items-start gap-2">
                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" style="color: #dc2626;" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm font-medium" style="color: #dc2626;" x-text="error"></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="nik_check" class="block text-sm font-semibold text-gray-700 mb-1.5">NIK (16
                        digit)</label>
                    <input id="nik_check" class="auth-input" type="text" x-model="nik" maxlength="16"
                        inputmode="numeric" placeholder="Contoh: 1234567890123456" @keydown.enter.prevent="checkNik()"
                        @input="nik = nik.replace(/\D/g, '').slice(0, 16); error = '';">
                    <p class="mt-1.5 text-xs text-gray-400">
                        <span x-text="nik.length"></span>/16 digit
                    </p>
                </div>

                <button type="button" @click="checkNik()" class="auth-btn auth-btn-primary"
                    :disabled="loading || nik.length !== 16">
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <template x-if="!loading">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memverifikasi...' : 'Verifikasi NIK'"></span>
                </button>
            </div>

            <div class="mt-6 pt-5" style="border-top: 1px solid #e5e7eb;">
                <p class="text-sm text-gray-500 text-center">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color: #1e40af;">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- STEP 2: Form Registrasi --}}
        {{-- ======================== --}}
        <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

            {{-- Resident Info Card --}}
            <div class="mb-5 rounded-lg p-4" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg flex-shrink-0"
                        style="background: #1e40af;">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold" style="color: #1e40af;">NIK Terverifikasi</p>
                        <div class="mt-2 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-500 w-14">Nama</span>
                                <span class="text-sm font-semibold text-gray-900"
                                    x-text="penduduk?.nama || '-'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-500 w-14">NIK</span>
                                <span class="text-sm font-mono text-gray-700" x-text="nik"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-500 w-14">Alamat</span>
                                <span class="text-sm text-gray-700">
                                    <span x-show="penduduk?.rt">RT <span x-text="penduduk?.rt"></span></span>
                                    <span x-show="penduduk?.rw"> / RW <span x-text="penduduk?.rw"></span></span>
                                    <span x-show="penduduk?.desa">, <span x-text="penduduk?.desa"></span></span>
                                </span>
                            </div>
                        </div>
                        <button type="button" @click="resetNik()" class="mt-2 text-xs font-semibold hover:underline"
                            style="color: #1e40af;">
                            Ganti NIK
                        </button>
                    </div>
                </div>
            </div>

            {{-- Registration Form --}}
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="nik" :value="nik">

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input id="email" class="auth-input" type="email" name="email"
                        value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <input id="password" class="auth-input" type="password" name="password" required
                        autocomplete="new-password" placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input id="password_confirmation" class="auth-input" type="password"
                        name="password_confirmation" required autocomplete="new-password"
                        placeholder="Ulangi password">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                </div>

                <!-- Terms -->
                <div>
                    <label for="terms_accepted" class="inline-flex items-start gap-2 cursor-pointer">
                        <input id="terms_accepted" type="checkbox"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            name="terms_accepted" required>
                        <span class="text-sm text-gray-600">Saya menyetujui syarat dan ketentuan layanan serta
                            bertanggung jawab atas kebenaran data</span>
                    </label>
                    <x-input-error :messages="$errors->get('terms_accepted')" class="mt-1.5" />
                </div>

                <button type="submit" class="auth-btn auth-btn-primary" :disabled="submitting"
                    @click="$nextTick(() => { if($el.form.checkValidity()) submitting = true; })">
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
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                    </template>
                    <span x-text="submitting ? 'Mendaftarkan...' : 'Daftar Akun'"></span>
                </button>
            </form>

            <div class="mt-6 pt-5" style="border-top: 1px solid #e5e7eb;">
                <p class="text-sm text-gray-500 text-center">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color: #1e40af;">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
