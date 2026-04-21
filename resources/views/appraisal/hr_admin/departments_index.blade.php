@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Departments" subtitle="Manage departments and their heads." icon="fa-building"
        :count="$departments->count()" refresh-target="departments-container" :refresh-url="route('departments.index')"
        :create-url="route('departments.create')" create-label="Create">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="hide-mobile">Code</th>
                        <th>Name</th>
                        <th>Head</th>
                        <th class="hide-mobile">Users</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $i => $d)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="hide-mobile">{{ $d->code ?? '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $d->name }}</div>
                            </td>
                            <td>{{ $d->head->name ?? '-' }}</td>
                            <td class="hide-mobile">
                                <span class="badge bg-light text-dark">{{ $d->users_count ?? 0 }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('departments.edit', $d) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('departments.destroy', $d) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this department?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.datatable-card>
@endsection