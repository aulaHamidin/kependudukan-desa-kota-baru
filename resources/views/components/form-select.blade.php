@props([
    'label' => null,
    'name',
    'required' => false,
    'placeholder' => 'Pilih...',
    'value' => null,
    'options' => [],
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

    <select name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'tom-select' . ($errors->has($name) ? ' has-error' : '')]) }}
        data-placeholder="{{ $placeholder }}">
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}"
                {{ ($useOld ? old($name, $value) : $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach

        {{-- Allow slot for custom options --}}
        {{ $slot }}
    </select>

    @if ($helper)
        <p class="form-hint">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize all tom-select elements
                document.querySelectorAll('select.tom-select').forEach(function(el) {
                    if (!el.tomselect) {
                        new TomSelect(el, {
                            allowEmptyOption: true,
                            placeholder: el.dataset.placeholder || 'Pilih...',
                            create: false,
                            sortField: {
                                field: "text",
                                direction: "asc"
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endonce
