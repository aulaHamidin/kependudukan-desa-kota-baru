{{-- PDF Header Component --}}
<div style="text-align: center; border-bottom: 3px solid #003580; padding-bottom: 15px; margin-bottom: 20px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 80px; vertical-align: middle; text-align: center; border: none;">
                @if (file_exists(public_path('images/logo-desa.png')))
                    <img src="{{ public_path('images/logo-desa.png') }}" alt="Logo Desa"
                        style="height: 70px; width: 70px; object-fit: contain;">
                @endif
            </td>
            <td style="vertical-align: middle; text-align: center; border: none;">
                <div style="font-size: 16px; font-weight: bold; margin-bottom: 2px;">PEMERINTAH
                    {{ strtoupper(config('app.desa.kabupaten')) }}</div>
                <div style="font-size: 14px; font-weight: bold; margin-bottom: 2px;">KECAMATAN
                    {{ strtoupper(config('app.desa.kecamatan')) }}</div>
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 4px;">
                    {{ strtoupper(config('app.desa.nama')) }}</div>
                <div style="font-size: 11px; line-height: 1.4;">
                    {{ config('app.desa.alamat') }}
                    @if (config('app.desa.kode_pos'))
                        - {{ config('app.desa.kode_pos') }}
                    @endif
                    @if (config('app.desa.telepon'))
                        | Telp: {{ config('app.desa.telepon') }}
                    @endif
                </div>
            </td>
            <td style="width: 80px; border: none;"></td>
        </tr>
    </table>
</div>
