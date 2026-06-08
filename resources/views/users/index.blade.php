<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'User Management']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="User Management" subtitle="Manage system users.">
            <x-slot name="actions">
                @can('create', \App\Models\User::class)
                    <x-button variant="primary" icon="plus" :href="route('users.create')">
                        Add User
                    </x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    <x-card>
        <x-data-table :datatable="true" :datatableOptions="[
            'perPage' => 10,
            'perPageSelect' => [5, 10, 25, 50, 100],
            'searchable' => true,
            'paging' => true,
            'labels' => [
                'placeholder' => 'Search name, username, or NIK...',
                'perPage' => 'per page',
                'noRows' => 'No data available',
                'noResults' => 'No results for {query}',
                'info' => 'Showing {start} - {end} of {rows} entries',
            ],
        ]" id="usersTable">
            <x-slot name="filters">
                <div class="flex items-center justify-end w-full">
                    <div data-datatable-search-for="usersTable"></div>
                </div>
            </x-slot>

            <x-slot name="head">
                <tr>
                    <x-table-header>Name</x-table-header>
                    <x-table-header>Username</x-table-header>
                    <x-table-header>Role</x-table-header>
                    <x-table-header>Status</x-table-header>
                    <x-table-header class="text-center">Actions</x-table-header>
                </tr>
            </x-slot>

            @forelse ($users as $user)
                <tr class="table-row-hover">
                    <x-table-cell class="font-medium text-gray-900">
                        {{ $user->name }}
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-gray-600">{{ $user->username }}</div>
                        @if ($user->nik)
                            <div class="text-xs text-gray-400">
                                NIK: {{ \App\Support\Masking::nik($user->nik) }}
                            </div>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        {{ $user->role_label }}
                    </x-table-cell>
                    <x-table-cell>
                        @if ($user->is_active)
                            <span class="badge badge-aktif">Active</span>
                        @else
                            <span class="badge badge-pending">Inactive</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}"
                                    class="btn-action btn-action-edit"
                                    title="Edit">
                                    <x-button-icon icon="edit" class="w-4 h-4" />
                                </a>
                            @endcan
                            @can('delete', $user)
                                <x-delete-confirm :action="route('users.destroy', $user)" title="Delete this user?">
                                    <button type="button"
                                        class="btn-action btn-action-delete"
                                        title="Delete">
                                        <x-button-icon icon="delete" class="w-4 h-4" />
                                    </button>
                                </x-delete-confirm>
                            @endcan
                        </div>
                    </x-table-cell>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-empty-state title="No Users" description="No user data to display." icon="empty" />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

</x-app-layout>
