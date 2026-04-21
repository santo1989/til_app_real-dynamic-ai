@extends('layouts.app')

@section('content')
    @php
        $roleOptions = [
            'employee' => 'Employee',
            'line_manager' => 'Line Manager',
            'dept_head' => 'Department Head',
            'board' => 'Board Member',
            'hr_admin' => 'HR Admin',
            'super_admin' => 'Super Admin',
        ];

        $departmentOptions = [];
        foreach ($departments as $dept) {
            $departmentOptions[$dept->id] = $dept->name;
        }

        $lineManagerOptions = [];
        foreach ($lineManagers as $mgr) {
            $lineManagerOptions[$mgr->id] = $mgr->name . ' (' . ucwords(str_replace('_', ' ', $mgr->role)) . ')';
        }
    @endphp

    <x-ui.datatable-card title="Edit User" subtitle="{{ $user->name }}" icon="fa-user-pen" body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('users.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
            <button class="btn btn-sm btn-outline-danger" type="button"
                onclick="if(confirm('Delete this user?')) document.getElementById('delete-form').submit()">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </x-slot>

        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Basic Info</div>
                    <div class="text-muted small">Identity and contact details.</div>
                </div>

                <x-ui.form-field name="name" label="Full Name" required="true" col="col-12 col-lg-6"
                    value="{{ $user->name }}" placeholder="Enter full name" autocomplete="name" />
                <x-ui.form-field name="employee_id" label="Employee ID" col="col-12 col-lg-6"
                    value="{{ $user->employee_id }}" placeholder="e.g., 00472" />
                <x-ui.form-field name="email" label="Email Address" type="email" required="true"
                    col="col-12 col-lg-6" value="{{ $user->email }}" placeholder="name@company.com"
                    autocomplete="email" />
                <x-ui.form-select name="role" label="Role" required="true" col="col-12 col-lg-6"
                    :options="$roleOptions" selected="{{ $user->role }}" />

                <div class="col-12 mt-2"></div>
                <div class="col-12">
                    <div class="fw-semibold">Organization</div>
                    <div class="text-muted small">Department and reporting manager.</div>
                </div>

                <x-ui.form-select name="department_id" label="Department" col="col-12 col-lg-6"
                    placeholder="-- Select Department --" :options="$departmentOptions"
                    selected="{{ $user->department_id }}" />
                <x-ui.form-select name="line_manager_id" label="Line Manager" col="col-12 col-lg-6"
                    placeholder="-- Select Line Manager --" :options="$lineManagerOptions"
                    selected="{{ $user->line_manager_id }}" />

                <div class="col-12 mt-2"></div>
                <div class="col-12">
                    <div class="fw-semibold">Security</div>
                    <div class="text-muted small">Update credentials if needed (leave blank to keep current password).</div>
                </div>

                <x-ui.form-field name="password" label="New Password (optional)" type="password"
                    col="col-12 col-lg-6" autocomplete="new-password" />
                <x-ui.form-field name="password_confirmation" label="Confirm New Password" type="password"
                    col="col-12 col-lg-6" autocomplete="new-password" />
                <x-ui.form-field name="password_plain" label="Plain Password (optional)" col="col-12 col-lg-6"
                    value="{{ $user->password_plain }}"
                    help="If you need to record a plain password for onboarding, enter it here." />
                <div class="col-12 col-lg-6">
                    <x-ui.form-file name="user_image" label="Profile Image (optional)" accept="image/*" />
                    @if ($user->user_image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $user->user_image) }}" alt="Profile" width="88"
                                height="88" class="border">
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('users.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Update User
                </x-ui.button>
            </div>
        </form>

        <form id="delete-form" method="POST" action="{{ route('users.destroy', $user) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    </x-ui.datatable-card>
@endsection
