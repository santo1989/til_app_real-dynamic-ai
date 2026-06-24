@extends('layouts.app')

@section('content')
    @php
        $fyLabel = $activeFY ?? null;
    @endphp

    <x-ui.datatable-card title="IDP Review (HR)" subtitle="{{ $fyLabel ? 'Active FY: ' . $fyLabel : 'All financial years' }}"
        icon="fa-graduation-cap" body-class="p-0">
        <x-slot name="actions">
            <span class="badge bg-light text-dark border">
                <i class="fas fa-sync-alt me-1"></i> Auto-refresh: 30s
            </span>
            <button class="btn btn-sm btn-outline-secondary" type="button"
                onclick="AutoRefresh.manualRefresh('hr-idps-table-container')">
                <i class="fas fa-rotate me-1"></i> Refresh
            </button>
        </x-slot>

        <div id="hr-idps-table-container" data-auto-refresh="true" data-refresh-url="{{ route('idp.hr.list') }}"
            data-refresh-target="#hr-idps-table-container">
            <div class="table-responsive-custom">
                <table class="table table-striped datatable mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th class="hide-mobile">Department</th>
                            <th class="hide-mobile">Line Manager</th>
                            <th class="text-center">Total IDPs</th>
                            <th class="text-center">Approved</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $employee)
                            @php
                                $total = $employee->idps_count;
                                $approved = $employee->approved_count;
                                $status = ($total > 0 && $approved === $total) ? 'Approved' : 'Pending Review';
                                $badge = $status === 'Approved' ? 'bg-success' : 'bg-warning text-dark';
                                $firstIdp = $employee->idps->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $employee->name }}</td>
                                <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                                <td class="hide-mobile">{{ $employee->department->name ?? '—' }}</td>
                                <td class="hide-mobile">{{ $employee->lineManager->name ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark rounded-pill px-3">{{ $total }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success text-white rounded-pill px-3">{{ $approved }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badge }} rounded-pill px-3">{{ $status }}</span>
                                </td>
                                <td class="text-end">
                                    @if($firstIdp)
                                    <a class="btn btn-sm btn-outline-primary shadow-none"
                                        href="{{ route('idp.hr.review', $firstIdp) }}">
                                        <i class="fas fa-search me-1"></i> Review Plan
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-ui.datatable-card>
@endsection

