@props([
    'label' => 'Status Kependudukan',
    'name' => 'status_kependudukan_code',
    'required' => false,
    'placeholder' => 'Pilih status',
    'value' => null,
    'options' => [],
    'helper' => null,
    'useOld' => true,
])

<x-form-select :label="$label" :name="$name" :required="$required" :placeholder="$placeholder" :value="$value"
    :options="$options" :helper="$helper" :useOld="$useOld" {{ $attributes }} />
