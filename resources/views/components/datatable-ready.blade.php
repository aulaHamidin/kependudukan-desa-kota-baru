@props(['tableId'])

<div x-data="{ dt: null }" x-init="dt = $store.datatables.get('{{ $tableId }}');
if (!dt) {
    $store.datatables.onReady('{{ $tableId }}', (instance) => { dt = instance; });
}">
    {{ $slot }}
</div>
