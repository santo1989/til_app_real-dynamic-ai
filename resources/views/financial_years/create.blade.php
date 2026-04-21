@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="Create Financial Year" subtitle="Add a new appraisal cycle" icon="fa-calendar-alt"
        body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('financial-years.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form action="{{ route('financial-years.store') }}" method="POST">
            @csrf
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Cycle Details</div>
                    <div class="text-muted small">Set the financial year label and date range.</div>
                </div>

                <x-ui.form-field name="label" label="Label" required="true" col="col-12 col-lg-4"
                    value="{{ old('label') }}" placeholder="e.g., 2025-26" />
                <x-ui.form-field name="start_date" label="Start Date" required="true" col="col-12 col-lg-4"
                    type="date" value="{{ old('start_date') }}" />
                <x-ui.form-field name="end_date" label="End Date" required="true" col="col-12 col-lg-4"
                    type="date" value="{{ old('end_date') }}" />

                <div class="col-12">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Auto-calculated:</strong> Revision cutoff will be set to 9 months from start date.
                        After cutoff, objective revisions are locked.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('financial-years.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Create Financial Year
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection

@section('scripts')
<script>
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    if (startDate) {
        const endDate = new Date(startDate);
        endDate.setFullYear(endDate.getFullYear() + 1);
        endDate.setDate(endDate.getDate() - 1);
        const year = endDate.getFullYear();
        const month = String(endDate.getMonth() + 1).padStart(2, '0');
        const day = String(endDate.getDate()).padStart(2, '0');
        document.getElementById('end_date').value = `${year}-${month}-${day}`;

        if (!document.getElementById('label').value) {
            const year1 = startDate.getFullYear();
            const year2 = String(year1 + 1).substr(-2);
            document.getElementById('label').value = `${year1}-${year2}`;
        }
    }
});
</script>
@endsection