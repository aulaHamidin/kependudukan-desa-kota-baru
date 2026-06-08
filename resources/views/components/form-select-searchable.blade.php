{{--
    Searchable Select Component using Tom Select + Alpine.js
    
    Usage (Local/Static data):
    <x-form-select-searchable 
        name="jenis_surat_kode" 
        label="Jenis Surat"
        :options="$jenisSuratOptions"
        placeholder="Pilih jenis surat..."
    />
    
    Usage (Remote/AJAX search for large datasets):
    <x-form-select-searchable 
        name="penduduk_id" 
        label="Penduduk"
        remote-url="/api/penduduk/search"
        search-field="q"
        value-field="id"
        label-field="label"
        placeholder="Ketik nama atau NIK..."
        :min-chars="2"
    />
--}}

@props([
    'label' => null,
    'name',
    'required' => false,
    'placeholder' => 'Pilih atau ketik untuk mencari...',
    'value' => null,
    'options' => [], // For local mode: ['value' => 'label', ...]
    'helper' => null,
    'useOld' => true,
    // Remote search options
    'remoteUrl' => null, // API endpoint for remote search
    'searchField' => 'q', // Query parameter name
    'valueField' => 'id', // Field name for option value in API response
    'labelField' => 'label', // Field name for option label in API response
    'minChars' => 2, // Minimum characters before search
    'maxResults' => 20, // Maximum results to show
    'debounce' => 300, // Delay in ms before triggering API call
    'preload' => false, // Preload options on focus (for small remote datasets)
    // Display options
    'allowCreate' => false, // Allow creating new options
    'clearable' => true, // Show clear button
    'disabled' => false,
])

@php
    $selectedValue = $useOld ? old($name, $value) : $value;
    $isRemote = !empty($remoteUrl);
    $uniqueId = $name . '_' . uniqid();
@endphp

<div x-data="selectSearchable({
    name: '{{ $name }}',
    remoteUrl: '{{ $remoteUrl }}',
    searchField: '{{ $searchField }}',
    valueField: '{{ $valueField }}',
    labelField: '{{ $labelField }}',
    minChars: {{ $minChars }},
    maxResults: {{ $maxResults }},
    debounceMs: {{ $debounce }},
    preload: {{ $preload ? 'true' : 'false' }},
    allowCreate: {{ $allowCreate ? 'true' : 'false' }},
    clearable: {{ $clearable ? 'true' : 'false' }},
    placeholder: '{{ $placeholder }}',
    initialValue: '{{ $selectedValue }}',
})" x-init="init($refs.select)" class="form-select-searchable-wrapper">
    @if ($label)
        <label for="{{ $uniqueId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <select x-ref="select" name="{{ $name }}" id="{{ $uniqueId }}" {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'tom-select-input' . ($errors->has($name) ? ' has-error' : '')]) }}>
        @if (!$isRemote)
            {{-- Local options --}}
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ $selectedValue == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        @else
            {{-- Remote: only render pre-selected value if exists --}}
            @if ($selectedValue)
                <option value="{{ $selectedValue }}" selected>Loading...</option>
            @endif
        @endif
    </select>

    @if ($helper)
        <p class="form-hint">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
