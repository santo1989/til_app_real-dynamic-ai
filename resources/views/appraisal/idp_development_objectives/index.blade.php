@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">IDP Skill Area -> Development Objective Master</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('idp-development-objectives.export-csv') }}"
                        class="btn btn-sm btn-outline-success">Export CSV</a>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark">Back</a>
                </div>
            </div>

            @include('components.alert')

            <div class="card mb-3">
                <div class="card-header">Add / Upsert Skill Area</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('idp-development-objectives.store') }}"
                        class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label mb-1">Skill Area</label>
                            <input type="text" name="skill_area"
                                class="form-control @error('skill_area') is-invalid @enderror"
                                value="{{ old('skill_area') }}" required>
                            @error('skill_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-1">
                            <div class="form-check mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                    value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Import Skill Areas From CSV</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('idp-development-objectives.import-csv') }}"
                        enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label mb-1">CSV File</label>
                            <input type="file" name="csv_file" accept=".csv,.txt"
                                class="form-control form-control-sm @error('csv_file') is-invalid @enderror" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Columns: skill_area,is_active</small>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Skill Area</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($item->skill_area)) }}
                                </td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="showEditModal({{ $item->id }}, '{{ addslashes($item->skill_area) }}', {{ $item->is_active ? 'true' : 'false' }})">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('idp-development-objectives.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Delete this master pair?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No skill areas found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPairModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editPairForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Master Pair</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Skill Area</label>
                            <input type="text" name="skill_area" id="edit_skill_area" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active"
                                value="1">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showEditModal(id, skillArea, isActive) {
            const form = document.getElementById('editPairForm');
            form.action = '{{ url('/appraisal/idp-development-objectives') }}/' + id;
            document.getElementById('edit_skill_area').value = skillArea;
            document.getElementById('edit_is_active').checked = isActive;
            const modal = new bootstrap.Modal(document.getElementById('editPairModal'));
            modal.show();
        }
    </script>
@endsection
