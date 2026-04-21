@extends('layouts.app')

@section('content')
    @php
        $headOptions = [];
        foreach (App\Models\User::whereIn('role', ['dept_head', 'hr_admin', 'line_manager', 'board'])->orderBy('name')->get() as $user) {
            $headOptions[$user->id] = $user->name . ' (' . ucwords(str_replace('_', ' ', $user->role)) . ')';
        }
    @endphp

    <x-ui.datatable-card title="Create Department" subtitle="Add a new department to the organization" icon="fa-building"
        body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('departments.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form method="POST" action="{{ route('departments.store') }}" enctype="multipart/form-data">
            @csrf
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Basic Info</div>
                    <div class="text-muted small">Department identity and head.</div>
                </div>

                <x-ui.form-field name="name" label="Department Name" required="true" col="col-12 col-lg-6"
                    placeholder="Enter department name" />
                <x-ui.form-field name="code" label="Department Code" col="col-12 col-lg-6"
                    placeholder="e.g., ITMIS" />
                <x-ui.form-select name="head_id" label="Department Head" col="col-12 col-lg-6"
                    placeholder="-- Select Head --" :options="$headOptions" />
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('departments.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Create Department
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection