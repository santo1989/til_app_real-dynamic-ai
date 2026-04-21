@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>{{ $isEdit ? 'Edit' : 'Add' }} Departmental Objective Master</h5>

            <form method="POST"
                action="{{ $isEdit ? route('departmental-objective-masters.update', $item) : route('departmental-objective-masters.store') }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label">Department Scope</label>
                    <select name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ (string) old('department_id', $item->department_id) === (string) $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Objective Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $item->title_sentence_case ?? '') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                        {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('departmental-objective-masters.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
