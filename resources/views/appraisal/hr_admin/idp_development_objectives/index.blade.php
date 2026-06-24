@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="IDP Skill Areas" subtitle="Master data for IDP skill categories." icon="fa-sitemap"
        :count="$items->count()" :create-url="route('idp-development-objectives.create')" create-label="Add Skill Area">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Skill Area Name</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $obj)
                        @php
                            $statusLabel = $obj->is_active ? 'Active' : 'Inactive';
                            $statusBadge = $obj->is_active ? 'bg-success' : 'bg-secondary';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ Str::ucfirst(Str::lower($obj->skill_area ?? '')) }}</div>
                            </td>
                            <td>
                                <span class="badge badge-responsive {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('idp-development-objectives.edit', $obj) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('idp-development-objectives.destroy', $obj) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this skill mapping?')">
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
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-2">
                {{ $items->links() }}
            </div>
        @endif
    </x-ui.datatable-card>
@endsection