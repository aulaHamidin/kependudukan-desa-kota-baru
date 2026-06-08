{{--
    Alert Component - SweetAlert2 Integration
    Automatically shows flash messages from Laravel session
--}}

@php
    $alerts = [];
    if (session('success')) {
        $alerts[] = ['type' => 'success', 'title' => 'Berhasil!', 'text' => session('success'), 'timer' => 4000];
    }
    if (session('error')) {
        $alerts[] = ['type' => 'error', 'title' => 'Gagal!', 'text' => session('error'), 'timer' => 5000];
    }
    if (session('warning')) {
        $alerts[] = ['type' => 'warning', 'title' => 'Perhatian!', 'text' => session('warning'), 'timer' => 4500];
    }
    if (session('info')) {
        $alerts[] = ['type' => 'info', 'title' => 'Informasi', 'text' => session('info'), 'timer' => 4000];
    }
    $errorList = $errors->any() ? $errors->all() : [];
@endphp

@if (count($alerts) || count($errorList))
    <div data-swal-alert data-swal-alerts='@json($alerts)' data-swal-errors='@json($errorList)'>
    </div>
@endif

<style>
    .colored-toast.swal2-icon-success {
        background-color: #ecfdf5 !important;
        border: 1px solid #d1fae5 !important;
    }

    .colored-toast.swal2-icon-error {
        background-color: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
    }

    .colored-toast.swal2-icon-warning {
        background-color: #fffbeb !important;
        border: 1px solid #fef3c7 !important;
    }

    .colored-toast.swal2-icon-info {
        background-color: #eff6ff !important;
        border: 1px solid #dbeafe !important;
    }

    .colored-toast-dark {
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
    }
</style>
