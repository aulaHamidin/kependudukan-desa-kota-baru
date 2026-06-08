{{-- 
    Delete Confirm Component
    Usage: <x-delete-confirm :action="route('something.destroy', $item)" />
--}}

@props([
    'action',
    'title' => 'Hapus Data?',
    'text' => 'Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen dari sistem.',
    'confirmText' => 'Ya, Hapus',
    'cancelText' => 'Batal',
])

<form action="{{ $action }}" method="POST" class="inline" x-data="swalConfirm(@js(['title' => $title, 'text' => $text, 'confirmText' => $confirmText, 'cancelText' => $cancelText]))" @submit.prevent="submit($event)">
    @csrf
    @method('DELETE')
    {{ $slot }}
</form>
