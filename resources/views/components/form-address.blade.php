@props([
    'label' => 'Alamat',
    'name' => 'alamat',
    'required' => false,
    'placeholder' => 'Contoh: Jl. Merdeka No. 10',
    'value' => null,
    'rows' => 3,
    'helper' => null,
])

<x-form-textarea :label="$label" :name="$name" :required="$required" :placeholder="$placeholder" :value="$value"
    :rows="$rows" :helper="$helper" {{ $attributes }} />
