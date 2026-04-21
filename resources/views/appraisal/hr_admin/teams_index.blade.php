@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Teams" subtitle="Manage departmental teams and their leaders." icon="fa-people-group"
        :count="$teams->count()" :create-url="route('teams.create')" create-label="Create Team">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Team Name</th>
                        <th>Department</th>
                        <th>Team Lead</th>
                        <th class="text-center">Members</th>
                        <th class="hide-mobile">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $i => $team)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $team->name }}</div>
                            </td>
                            <td>{{ $team->department->name ?? '-' }}</td>
                            <td>
                                @if($team->teamLead)
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted">{{ $team->teamLead->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small italic">No Lead Assigned</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill">{{ $team->users_count }}</span>
                            </td>
                            <td class="hide-mobile">
                                <span class="badge {{ $team->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('teams.edit', $team) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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
