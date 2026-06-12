{{--
    Data Table Partial
    Menampilkan data penduduk dalam format tabel label-value dengan nomor urut

    Variables:
    - $fields: Array field names yang akan ditampilkan
    - $fieldLabels: Array mapping field => label
    - $data: Array data penduduk
--}}
@php
    $visibleIndex = 1;
    $numbered = $numbered ?? true;
    $formatValue = function (string $field, mixed $value): mixed {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->translatedFormat('d F Y');
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return \Carbon\Carbon::parse($value)->translatedFormat('d F Y');
        }

        if (in_array($field, ['jenis_kelamin', 'jenis_kelamin_bayi', 'jenis_kelamin_anak'], true)) {
            return match (strtoupper((string) $value)) {
                'L', 'LAKI-LAKI' => 'Laki-laki',
                'P', 'PEREMPUAN' => 'Perempuan',
                default => $value,
            };
        }

        return $value;
    };
@endphp
<table class="data-table">
    @foreach ($fields as $field)
        @if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '')
            <tr>
                @if ($numbered)
                    <td class="number">{{ $visibleIndex++ }}.</td>
                @else
                    <td class="number">&nbsp;</td>
                @endif
                <td class="label">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</td>
                <td class="separator">:</td>
                <td class="value">{{ $formatValue($field, $data[$field]) }}</td>
            </tr>
        @endif
    @endforeach
</table>
