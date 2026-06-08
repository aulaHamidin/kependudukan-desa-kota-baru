@props([
    'label' => null,
    'name',
    'required' => false,
    'placeholder' => '',
    'value' => null,
    'helper' => null,
    'useOld' => true,
    'min' => null,
    'max' => null,
    'enableTime' => false,
    'mode' => 'single',
    'altFormat' => null,
    'dateFormat' => null,
    'altInput' => true,
])

<x-form-input :label="$label" :name="$name" type="date" :required="$required" :placeholder="$placeholder" :value="$value"
    :helper="$helper" :useOld="$useOld" :min="$min" :max="$max" data-datepicker="true" :data-date-enable-time="$enableTime ? 'true' : 'false'"
    :data-date-mode="$mode" :data-date-alt-format="$altFormat ?? ($enableTime ? 'd F Y H:i' : 'd F Y')" :data-date-format="$dateFormat ?? ($enableTime ? 'Y-m-d H:i' : 'Y-m-d')" :data-date-alt-input="$altInput ? 'true' : 'false'" {{ $attributes }} />
