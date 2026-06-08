@props([
    'label' => null,
    'name',
    'required' => false,
    'placeholder' => '',
    'value' => null,
    'rows' => 3,
    'helper' => null,
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

    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input-custom resize-none' . ($errors->has($name) ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : '')]) }}>{{ old($name, $value) }}</textarea>

    @if ($helper)
        <p class="form-hint">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
