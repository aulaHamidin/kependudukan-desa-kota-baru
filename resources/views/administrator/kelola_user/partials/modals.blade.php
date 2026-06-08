@foreach ($users as $item)
    @php
        $editModal = 'edit-user-' . $item->id;
        $useOld = old('_modal') === $editModal;
    @endphp

    @can('update', $item)
        <x-modal.form :name="$editModal" title="Edit User" subtitle="Perbarui data pengguna {{ $item->name }}.">
            <form id="{{ $editModal }}-form" method="POST" action="{{ route('administrator.kelola-user.update', $item) }}"
                class="space-y-6" data-user-form>
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="{{ $editModal }}">

                @include('administrator.kelola_user.partials.form-fields', [
                    'item' => $item,
                    'allowedRoles' => $allowedRoles,
                    'territories' => $territories,
                    'useOld' => $useOld,
                ])
            </form>

            <x-slot name="footer">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', '{{ $editModal }}')">
                    Batal
                </x-button>
                <x-button type="submit" icon="save" form="{{ $editModal }}-form">
                    Simpan Perubahan
                </x-button>
            </x-slot>
        </x-modal.form>
    @endcan

    @can('delete', $item)
        <x-modal.confirm :name="'delete-user-' . $item->id" title="Hapus User?"
            description="User {{ $item->name }} akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan."
            :action="route('administrator.kelola-user.destroy', $item)" />
    @endcan

    @can('update', $item)
        @if ($item->is_active)
            <x-modal.confirm :name="'disable-user-' . $item->id" title="Nonaktifkan User?"
                description="User {{ $item->name }} akan dinonaktifkan dan tidak bisa login sampai diaktifkan kembali."
                confirmText="Ya, Nonaktifkan" confirmVariant="warning" :action="route('administrator.kelola-user.update', $item)" method="PUT">
                <input type="hidden" name="name" value="{{ $item->name }}">
                <input type="hidden" name="username" value="{{ $item->username }}">
                <input type="hidden" name="nik" value="{{ $item->nik }}">
                <input type="hidden" name="email" value="{{ $item->email }}">
                <input type="hidden" name="role" value="{{ $item->role }}">
                <input type="hidden" name="desa_id" value="{{ $item->desa_id }}">
                <input type="hidden" name="rw_id" value="{{ $item->rw_id }}">
                <input type="hidden" name="rt_id" value="{{ $item->rt_id }}">
                <input type="hidden" name="is_active" value="0">
            </x-modal.confirm>
        @else
            <x-modal.confirm :name="'enable-user-' . $item->id" title="Aktifkan User?"
                description="User {{ $item->name }} akan diaktifkan kembali dan bisa login." confirmText="Ya, Aktifkan"
                confirmVariant="primary" :action="route('administrator.kelola-user.update', $item)" method="PUT">
                <input type="hidden" name="name" value="{{ $item->name }}">
                <input type="hidden" name="username" value="{{ $item->username }}">
                <input type="hidden" name="nik" value="{{ $item->nik }}">
                <input type="hidden" name="email" value="{{ $item->email }}">
                <input type="hidden" name="role" value="{{ $item->role }}">
                <input type="hidden" name="desa_id" value="{{ $item->desa_id }}">
                <input type="hidden" name="rw_id" value="{{ $item->rw_id }}">
                <input type="hidden" name="rt_id" value="{{ $item->rt_id }}">
                <input type="hidden" name="is_active" value="1">
            </x-modal.confirm>
        @endif
    @endcan

    @can('restore', $item)
        @if ($item->trashed())
            <x-modal.confirm :name="'restore-user-' . $item->id" title="Kembalikan User?"
                description="User {{ $item->name }} akan dipulihkan dan bisa digunakan kembali."
                confirmText="Ya, Kembalikan" confirmVariant="primary" :action="route('administrator.kelola-user.restore', $item->id)" method="PATCH" />
        @endif
    @endcan
@endforeach

@can('create', \App\Models\User::class)
    <x-modal.form name="create-user" title="Tambah User" subtitle="Buat akun pengguna baru.">
        <form id="create-user-form" method="POST" action="{{ route('administrator.kelola-user.store') }}"
            class="space-y-6" data-user-form>
            @csrf
            <input type="hidden" name="_modal" value="create-user">

            @include('administrator.kelola_user.partials.form-fields', [
                'item' => null,
                'allowedRoles' => $allowedRoles,
                'territories' => $territories,
                'useOld' => true,
            ])
        </form>

        <x-slot name="footer">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-user')">
                Batal
            </x-button>
            <x-button type="submit" icon="save" form="create-user-form">
                Simpan User
            </x-button>
        </x-slot>
    </x-modal.form>
@endcan

@once
    @push('scripts')
        <script>
            function initUserForm(form) {
                if (!form || form.dataset.userFormInitialized) return;
                form.dataset.userFormInitialized = 'true';

                const roleSelect = form.querySelector('[data-role-select]');
                const desaSelect = form.querySelector('[data-territory-select="desa"]');
                const rwSelect = form.querySelector('[data-territory-select="rw"]');
                const rtSelect = form.querySelector('[data-territory-select="rt"]');
                const desaGroup = form.querySelector('[data-territory-group="desa"]');
                const rwGroup = form.querySelector('[data-territory-group="rw"]');
                const rtGroup = form.querySelector('[data-territory-group="rt"]');
                const desaError = form.querySelector('[data-territory-error="desa"]');
                const rwError = form.querySelector('[data-territory-error="rw"]');
                const rtError = form.querySelector('[data-territory-error="rt"]');

                if (!roleSelect || !desaSelect || !rwSelect || !rtSelect || !desaGroup || !rwGroup || !rtGroup) {
                    return;
                }

                const rwOptions = Array.from(rwSelect.options).slice(1);
                const rtOptions = Array.from(rtSelect.options).slice(1);

                function filterRw() {
                    const desaId = desaSelect.value;
                    let hasSelected = false;

                    rwOptions.forEach((option) => {
                        const optionDesaId = option.getAttribute('data-desa-id');
                        const visible = !desaId || optionDesaId === desaId;
                        option.hidden = !visible;
                        option.disabled = !visible;
                        if (visible && option.selected) {
                            hasSelected = true;
                        }
                    });

                    if (!hasSelected && desaId) {
                        rwSelect.value = '';
                    }
                }

                function filterRt() {
                    const desaId = desaSelect.value;
                    const rwId = rwSelect.value;
                    let hasSelected = false;

                    rtOptions.forEach((option) => {
                        const optionRwId = option.getAttribute('data-rw-id');
                        const optionDesaId = option.getAttribute('data-desa-id');
                        const visible = rwId ? optionRwId === rwId : (!desaId || optionDesaId === desaId);
                        option.hidden = !visible;
                        option.disabled = !visible;
                        if (visible && option.selected) {
                            hasSelected = true;
                        }
                    });

                    if (!hasSelected && (desaId || rwId)) {
                        rtSelect.value = '';
                    }
                }

                function setFieldErrors(show) {
                    if (desaError) {
                        desaError.classList.toggle('hidden', !(show && desaGroup.style.display !== 'none' && !desaSelect
                            .value));
                    }
                    if (rwError) {
                        rwError.classList.toggle('hidden', !(show && rwGroup.style.display !== 'none' && !rwSelect
                            .value));
                    }
                    if (rtError) {
                        rtError.classList.toggle('hidden', !(show && rtGroup.style.display !== 'none' && !rtSelect.value));
                    }
                }

                function setFieldVisibility() {
                    const role = roleSelect.value;

                    const showDesa = role === 'admin_desa';
                    const showRw = role === 'admin_rw';
                    const showRt = role === 'admin_rt' || role === 'viewer';

                    desaGroup.style.display = showDesa ? '' : 'none';
                    rwGroup.style.display = showRw ? '' : 'none';
                    rtGroup.style.display = showRt ? '' : 'none';

                    if (!showDesa) {
                        desaSelect.value = '';
                    }

                    if (!showRw) {
                        rwSelect.value = '';
                    }

                    if (!showRt) {
                        rtSelect.value = '';
                    }

                    setFieldErrors(false);
                }

                function refreshFilters() {
                    filterRw();
                    filterRt();
                }

                function validateTerritoryFields() {
                    setFieldErrors(true);

                    if (desaGroup.style.display !== 'none' && !desaSelect.value) {
                        desaSelect.focus();
                        return false;
                    }

                    if (rwGroup.style.display !== 'none' && !rwSelect.value) {
                        rwSelect.focus();
                        return false;
                    }

                    if (rtGroup.style.display !== 'none' && !rtSelect.value) {
                        rtSelect.focus();
                        return false;
                    }

                    return true;
                }

                roleSelect.addEventListener('change', function() {
                    setFieldVisibility();
                    refreshFilters();
                });

                desaSelect.addEventListener('change', function() {
                    setFieldErrors(true);
                    refreshFilters();
                });

                rwSelect.addEventListener('change', function() {
                    setFieldErrors(true);
                    refreshFilters();
                });

                rtSelect.addEventListener('change', function() {
                    setFieldErrors(true);
                });

                form.addEventListener('submit', function(event) {
                    if (!validateTerritoryFields()) {
                        event.preventDefault();
                    }
                });

                setFieldVisibility();
                refreshFilters();
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-user-form]').forEach(initUserForm);
            });
        </script>
    @endpush
@endonce
