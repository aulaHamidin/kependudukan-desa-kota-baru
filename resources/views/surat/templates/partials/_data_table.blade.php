{{--
    Data Table Partial
    Menampilkan data penduduk dalam format tabel label-value dengan nomor urut

    Variables:
    - $fields: Array field names yang akan ditampilkan
    - $fieldLabels: Array mapping field => label
    - $data: Array data penduduk
--}}
<table class="data-table">
    @foreach ($fields as $field)
        @if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '')
            <tr>
                <td class="number">{{ $loop->iteration }}.</td>
                <td class="label">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</td>
                <td class="separator">:</td>
                <td class="value">
                    @if ($field === 'tanggal_lahir' && $data[$field] instanceof \Carbon\Carbon)
                        {{ $data[$field]->translatedFormat('d F Y') }}
                    @elseif($field === 'jenis_kelamin')
                        {{ $data[$field] === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    @else
                        {{ $data[$field] }}
                    @endif
                </td>
            </tr>
        @endif
    @endforeach
</table>
