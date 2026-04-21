@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="Edit Financial Year" subtitle="{{ $financialYear->label }}" icon="fa-calendar-alt"
        body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('financial-years.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('financial-years.show', $financialYear) }}" class="btn-sm">
                <i class="fas fa-eye me-1"></i> View
            </x-ui.button>
            @if (!$financialYear->is_active)
            <form action="{{ route('financial-years.destroy', $financialYear) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Delete this financial year?')">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
            @endif
        </x-slot>

        <form action="{{ route('financial-years.update', $financialYear) }}" method="POST">
            @csrf
            @method('PUT')
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Cycle Details</div>
                    <div class="text-muted small">Set the financial year label and date range.</div>
                </div>

                <x-ui.form-field name="label" label="Label" required="true" col="col-12 col-lg-4"
                    value="{{ old('label', $financialYear->label) }}" placeholder="e.g., 2025-26" />
                <x-ui.form-field name="start_date" label="Start Date" required="true" col="col-12 col-lg-4"
                    type="date" value="{{ old('start_date', optional($financialYear->start_date)->format('Y-m-d') ?? '') }}" />
                <x-ui.form-field name="end_date" label="End Date" required="true" col="col-12 col-lg-4"
                    type="date" value="{{ old('end_date', optional($financialYear->end_date)->format('Y-m-d') ?? '') }}" />

                <div class="col-12">
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Warning:</strong> Changing dates for an active financial year may affect existing objectives and appraisals.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('financial-years.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Update Financial Year
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection