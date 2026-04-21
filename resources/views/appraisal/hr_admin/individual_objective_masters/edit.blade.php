@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="Edit Individual Objective" subtitle="{{ $item->title_sentence_case }}" icon="fa-pen-to-square" body-class="p-4">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form method="POST" action="{{ route('individual-objective-masters.update', $item) }}">
            @csrf
            @method('PUT')
            @include('components.alert')

            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="fw-semibold mb-3">Objective Details</div>
                    
                    <div class="mb-4">
                        <x-ui.form-field name="title" label="Objective Title" value="{{ $item->title }}" required="true" />
                    </div>

                    <div class="form-check mt-4 d-flex align-items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="form-check-input">
                        <label for="is_active" class="form-check-label small fw-medium">Active Template</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5 py-3 border-top">
                <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Update Objective
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection
