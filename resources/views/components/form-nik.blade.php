@props([
    'label' => 'NIK',
    'name' => 'nik',
    'required' => false,
    'placeholder' => '16 digit',
    'value' => null,
    'helper' => '16 digit angka tanpa spasi',
    'useOld' => true,
])

<x-form-input :label="$label" :name="$name" type="text" inputmode="numeric" maxlength="16" pattern="[0-9]{16}"
    :required="$required" :placeholder="$placeholder" :value="$value" :helper="$helper" :useOld="$useOld"
    x-on:input="event.target.value = event.target.value.replace(/\D/g, '').slice(0, 16)" autocomplete="off"
    {{ $attributes }} />
