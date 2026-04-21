@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Users" subtitle="Manage employees, managers, and admin accounts." icon="fa-users"
        :count="$users->count()" refresh-target="users-container" :refresh-url="route('users.index')"
        :create-url="route('users.create')" create-label="Create">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="hide-mobile">Emp ID</th>
                        <th>Name</th>
                        <th class="hide-mobile">Email</th>
                        <th class="hide-mobile">Department</th>
                        <th>Role</th>
                        <th class="hide-mobile">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $i => $u)
                        @php
                            $roleLabel = ucwords(str_replace('_', ' ', $u->role ?? 'user'));
                            $statusLabel = $u->is_active ? 'Active' : 'Inactive';
                            $statusBadge = $u->is_active ? 'bg-success' : 'bg-secondary';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="hide-mobile">{{ $u->employee_id ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                                        style="width:32px;height:32px;background:rgba(42,135,96,.12);color:var(--primary-hover,#075432);">
                                        {{ mb_strtoupper(mb_substr(trim($u->name ?? 'U'), 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate-mobile">{{ $u->name }}</div>
                                        <div class="small text-muted text-truncate-mobile">{{ $u->designation ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-mobile">{{ $u->email }}</td>
                            <td class="hide-mobile">{{ $u->department->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-responsive bg-primary">{{ $roleLabel }}</span>
                            </td>
                            <td class="hide-mobile">
                                <span class="badge badge-responsive {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @can('view', $u)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('users.show', $u) }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.edit', $u) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.datatable-card>
@endsection
