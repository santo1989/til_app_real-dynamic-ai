@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Dept/Team Objective Master" subtitle="Global library of departmental and team targets (pre-assignment)." icon="fa-diagram-project"
        :count="$items->count()" :create-url="route('departmental-objective-masters.create')" create-label="Add Objective">
        
        <x-slot name="actions">
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import CSV
            </button>
        </x-slot>

        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Objective Title</th>
                        <th class="hide-mobile">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $obj)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $obj->title_sentence_case }}</div>
                            </td>
                            <td class="hide-mobile">
                                <span class="badge {{ $obj->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $obj->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('departmental-objective-masters.edit', $obj) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('departmental-objective-masters.destroy', $obj) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this master objective?')">
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
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">
                {{ $items->links() }}
            </div>
        @endif
    </x-ui.datatable-card>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('departmental-objective-masters.import-csv') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Import Objectives</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <div class="form-text small mt-2">
                                Expected format: <code>title, is_active</code>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
