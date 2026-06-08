{{-- Surat - Terbit Expiring Widget --}}
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Surat Akan Kedaluwarsa</h3>
    @if (isset($expiringSurat) && count($expiringSurat) > 0)
        <ul class="space-y-2">
            @foreach ($expiringSurat as $surat)
                <li class="flex justify-between items-center text-sm">
                    <span class="text-gray-700">{{ $surat->nomor_surat ?? 'N/A' }}</span>
                    <span class="text-red-500">{{ $surat->tanggal_kadaluarsa?->diffForHumans() ?? '-' }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-gray-500 text-sm">Tidak ada surat yang akan kedaluwarsa.</p>
    @endif
</div>
