@props([
    'label' => null,
    'name',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'value' => null,
    'helper' => null,
    'useOld' => true,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
        value="{{ $useOld ? old($name, $value) : $value ?? '' }}" placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input-custom' . ($errors->has($name) ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : '')]) }}>

    @if ($helper)
        <p class="form-hint">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
