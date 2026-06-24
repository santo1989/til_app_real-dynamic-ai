@extends('layouts.app')

@section('content')
    @php
        $departmentOptions = [];
        foreach ($departments as $dept) {
            $departmentOptions[$dept->id] = $dept->name;
        }

        $lineManagerOptions = [];
        foreach ($lineManagers as $mgr) {
            $lineManagerOptions[$mgr->id] = $mgr->name . ' (' . ucwords(str_replace('_', ' ', $mgr->role)) . ')';
        }
    @endphp

    <x-ui.datatable-card title="Create User" subtitle="Add a new account for the appraisal system" icon="fa-user-plus"
        body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('users.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
            @csrf
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Basic Info</div>
                    <div class="text-muted small">Identity and contact details.</div>
                </div>

                <x-ui.form-field name="name" label="Full Name" required="true" col="col-12 col-lg-6"
                    placeholder="Enter full name" autocomplete="name" />
                <x-ui.form-field name="employee_id" label="Employee ID" col="col-12 col-lg-6"
                    placeholder="e.g., 00472" />
                <x-ui.form-field name="email" label="Email Address" type="email" required="true"
                    col="col-12 col-lg-6" placeholder="name@company.com" autocomplete="email" />
                <x-ui.form-select name="role" label="Role" required="true" col="col-12 col-lg-6"
                    :options="$roleOptions" :selected="'employee'" />

                <div class="col-12 mt-2"></div>
                <div class="col-12">
                    <div class="fw-semibold">Organization</div>
                    <div class="text-muted small">Department and reporting manager.</div>
                </div>

                <x-ui.form-select name="department_id" label="Department" col="col-12 col-lg-6"
                    placeholder="-- Select Department --" :options="$departmentOptions" />
                <x-ui.form-select name="line_manager_id" label="Line Manager" col="col-12 col-lg-6"
                    placeholder="-- Select Line Manager --" :options="$lineManagerOptions" />

                <div class="col-12 mt-2"></div>
                <div class="col-12">
                    <div class="fw-semibold">Security</div>
                    <div class="text-muted small">Set login credentials for the user.</div>
                </div>

                <x-ui.form-field name="password" label="Password" type="password" required="true"
                    col="col-12 col-lg-6" autocomplete="new-password" />
                <x-ui.form-field name="password_confirmation" label="Confirm Password" type="password"
                    required="true" col="col-12 col-lg-6" autocomplete="new-password" />
                <x-ui.form-field name="password_plain" label="Plain Password (optional)" col="col-12 col-lg-6"
                    help="If you need to record a plain password for onboarding, enter it here." />
                <x-ui.form-file name="user_image" label="Profile Image (optional)" col="col-12 col-lg-6"
                    accept="image/*" />
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('users.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Create User
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>
@endsection
