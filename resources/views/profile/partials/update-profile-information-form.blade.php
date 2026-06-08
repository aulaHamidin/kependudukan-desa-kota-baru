<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div>
        <label for="name" class="form-label">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Nama Lengkap
            </span>
        </label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
            autocomplete="name" class="form-input-custom" placeholder="Masukkan nama lengkap" />
        <x-input-error class="mt-1" :messages="$errors->get('name')" />
    </div>

    <div>
        <label for="email" class="form-label">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                Alamat Email
            </span>
        </label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
            autocomplete="username" class="form-input-custom" placeholder="anda@email.com" />
        <x-input-error class="mt-1" :messages="$errors->get('email')" />
    </div>

    <div class="pt-2">
        <x-button type="submit" variant="primary" class="w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            Simpan Perubahan
        </x-button>
    </div>
</form>
