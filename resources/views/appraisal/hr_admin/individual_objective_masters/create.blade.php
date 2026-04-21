@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="Add Individual Objective" subtitle="Create a new template for personal employee KPIs" icon="fa-list-check" body-class="p-4">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form method="POST" action="{{ route('individual-objective-masters.store') }}">
            @csrf
            @include('components.alert')

            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="fw-semibold mb-3">Objective Details</div>
                    
                    <div class="mb-4">
                        <x-ui.form-field name="title" label="Objective Title" required="true" placeholder="e.g., Punctuality & Time Management" />
                        <div class="form-text small mt-1">Individual objectives are selectable by all employees regardless of department.</div>
                    </div>

                    <div class="form-check mt-4 d-flex align-items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked class="form-check-input">
                        <label for="is_active" class="form-check-label small fw-medium">Active Template</label>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="alert alert-info border-0 shadow-sm">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i> Selection Rule</h6>
                        <p class="small mb-0">These objectives form the "Individual" portion of the appraisal. Employees can select up to 5 individual objectives to reach their 30% weightage target.</p>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5 py-3 border-top">
                <x-ui.button variant="secondary" href="{{ route('individual-objective-masters.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Save Objective
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection
