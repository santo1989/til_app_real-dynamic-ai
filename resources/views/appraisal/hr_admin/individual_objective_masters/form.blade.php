@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="{{ $isEdit ? 'Edit' : 'Create' }} Individual Objective" subtitle="{{ $isEdit ? $item->title_sentence_case : 'Add a new objective master' }}"
        icon="fa-list-check" body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
            @if($isEdit)
            <button class="btn btn-sm btn-outline-danger" type="button"
                onclick="if(confirm('Delete this objective?')) document.getElementById('delete-form').submit()">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            @endif
        </x-slot>

        <form method="POST" action="{{ $isEdit ? route('individual-objective-masters.update', $item) : route('individual-objective-masters.store') }}">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Objective Details</div>
                    <div class="text-muted small">Title and status.</div>
                </div>

                <x-ui.form-field name="title" label="Objective Title" required="true" col="col-12 col-lg-8"
                    value="{{ old('title', $item->title_sentence_case ?? '') }}" placeholder="Enter objective title" />

                <div class="col-12 col-lg-4">
                    <div class="form-check form-switch mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="text-muted small">Inactive objectives won't appear in employee selection lists.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> {{ $isEdit ? 'Update' : 'Create' }} Objective
                </x-ui.button>
            </div>
        </form>

        @if($isEdit)
        <form id="delete-form" method="POST" action="{{ route('individual-objective-masters.destroy', $item) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </x-ui.datatable-card>
@endsection