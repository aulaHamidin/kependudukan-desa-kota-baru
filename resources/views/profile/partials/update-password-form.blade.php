<form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="form-label">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
                Password Saat Ini
            </span>
        </label>
        <input id="update_password_current_password" name="current_password" type="password"
            autocomplete="current-password" class="form-input-custom" placeholder="Masukkan password saat ini" />
        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
    </div>

    <div>
        <label for="update_password_password" class="form-label">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                Password Baru
            </span>
        </label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password"
            class="form-input-custom" placeholder="Minimal 8 karakter" />
        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
    </div>

    <div>
        <label for="update_password_password_confirmation" class="form-label">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                Konfirmasi Password Baru
            </span>
        </label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
            autocomplete="new-password" class="form-input-custom" placeholder="Ulangi password baru" />
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
    </div>

    <div class="pt-2">
        <x-button type="submit" variant="primary" class="w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Perbarui Password
        </x-button>
    </div>
</form>
