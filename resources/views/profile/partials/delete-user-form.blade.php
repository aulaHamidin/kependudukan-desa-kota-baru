<div class="space-y-4">
    @if (!$canDeleteSelf)
        <p class="text-sm text-gray-600">
            Akun super admin terakhir tidak dapat dihapus. Tambahkan super admin lain terlebih dahulu.
        </p>
    @else
        <p class="text-sm text-gray-600">
            Menghapus akun akan menghapus akses Anda secara permanen. Pastikan data penting sudah disimpan.
        </p>

        <x-button variant="danger" x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            Hapus Akun
        </x-button>
    @endif

    @if ($canDeleteSelf)
        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus Akun</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Masukkan password untuk menghapus akun secara permanen.
                </p>

                <div class="mt-4">
                    <label for="password"
                        class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" class="form-input-custom"
                        placeholder="Password" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" x-on:click="$dispatch('close')">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="danger">Hapus Akun</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
