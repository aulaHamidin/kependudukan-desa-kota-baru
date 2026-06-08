@props([
    'item' => null,
    'allowedRoles' => [],
    'territories' => ['desas' => [], 'rws' => [], 'rts' => []],
    'useOld' => false,
])

@php
    $name = $item?->name;
    $username = $item?->username;
    $nik = $item?->nik;
    $email = $item?->email;

    if ($useOld) {
        $name = old('name', $name);
        $username = old('username', $username);
        $nik = old('nik', $nik);
        $email = old('email', $email);
    }

    $roleValue = old('role', $item?->role ?? '');
    $statusValue = old('is_active', $item ? ($item->is_active ? '1' : '0') : '1');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="name" label="Nama Lengkap" required :value="$name" :useOld="$useOld" />
    <x-form-input name="username" label="Username" required :value="$username" :useOld="$useOld" />
    <x-form-input name="nik" label="NIK" placeholder="16 digit" :value="$nik" :useOld="$useOld" />
    <x-form-input name="email" label="Email" type="email" required :value="$email" :useOld="$useOld" />

    @if ($item)
        <x-form-input name="password" label="Password Baru" type="password"
            helper="Kosongkan jika tidak mengubah password." />
        <x-form-input name="password_confirmation" label="Konfirmasi Password" type="password" />
    @else
        <x-form-input name="password" label="Password" type="password" required />
        <x-form-input name="password_confirmation" label="Konfirmasi Password" type="password" required />
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Role</label>
        <select name="role" class="form-select-custom" required data-role-select>
            <option value="">Pilih role</option>
            @foreach ($allowedRoles as $role)
                <option value="{{ $role }}" @selected($roleValue === $role)>
                    {{ str_replace('_', ' ', ucfirst($role)) }}
                </option>
            @endforeach
        </select>
        @error('role')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
        <select name="is_active" class="form-select-custom" data-status-select>
            <option value="1" @selected($statusValue === '1')>Aktif</option>
            <option value="0" @selected($statusValue === '0')>Nonaktif</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div data-territory-group="desa">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Desa</label>
        <select name="desa_id" class="form-select-custom" data-territory-select="desa">
            <option value="">--</option>
            @foreach ($territories['desas'] as $desa)
                <option value="{{ $desa->id }}" @selected(old('desa_id', $item?->desa_id) == $desa->id)>
                    {{ $desa->nama }}
                </option>
            @endforeach
        </select>
        @error('desa_id')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-rose-500 mt-1 hidden" data-territory-error="desa">Desa wajib dipilih.</p>
        <p class="text-[11px] text-gray-400 mt-1">Wajib untuk role admin desa.</p>
    </div>
    <div data-territory-group="rw">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">RW</label>
        <select name="rw_id" class="form-select-custom" data-territory-select="rw">
            <option value="">--</option>
            @foreach ($territories['rws'] as $rw)
                <option value="{{ $rw->id }}" data-desa-id="{{ $rw->desa_id }}" @selected(old('rw_id', $item?->rw_id) == $rw->id)>
                    RW{{ str_pad($rw->nomor_rw, 3, '0', STR_PAD_LEFT) }}-Desa {{ $rw->desa?->nama }}
                </option>
            @endforeach
        </select>
        @error('rw_id')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-rose-500 mt-1 hidden" data-territory-error="rw">RW wajib dipilih.</p>
        <p class="text-[11px] text-gray-400 mt-1">Wajib untuk role admin RW.</p>
    </div>
    <div data-territory-group="rt">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">RT</label>
        <select name="rt_id" class="form-select-custom" data-territory-select="rt">
            <option value="">--</option>
            @foreach ($territories['rts'] as $rt)
                <option value="{{ $rt->id }}" data-rw-id="{{ $rt->rw_id }}"
                    data-desa-id="{{ $rt->rw?->desa_id }}" @selected(old('rt_id', $item?->rt_id) == $rt->id)>
                    RT{{ str_pad($rt->nomor_rt, 3, '0', STR_PAD_LEFT) }}/RW{{ str_pad($rt->rw?->nomor_rw ?? '', 3, '0', STR_PAD_LEFT) }}
                </option>
            @endforeach
        </select>
        @error('rt_id')
            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
        @enderror
        <p class="text-xs text-rose-500 mt-1 hidden" data-territory-error="rt">RT wajib dipilih.</p>
        <p class="text-[11px] text-gray-400 mt-1">Wajib untuk role admin RT atau viewer.</p>
    </div>
</div>
