@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Individual Objective List" subtitle="Monitoring self-service objective status for the current financial year." icon="fa-user-check"
        :count="$users->total()" :create-url="null">
        
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle border-top">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Employee</th>
                        <th style="width: 20%;">Department / Team</th>
                        <th style="width: 15%; text-align: center;">Dept Set (30%)</th>
                        <th style="width: 15%; text-align: center;">Indiv Set (70%)</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                        <th style="width: 10%;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="smaller text-muted">{{ $user->designation ?: 'Staff' }} ({{ $user->employee_id ?? 'No ID' }})</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $user->department->name ?? 'N/A' }}</div>
                                <div class="smaller text-muted">{{ $user->team->name ?? 'No Team' }}</div>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-check-circle text-success shadow-sm"></i>
                                <div class="smaller mt-1 text-success fw-bold">Deployed</div>
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 6px; width: 60px; margin: 0 auto;">
                                    <div class="progress-bar {{ $user->individual_weight >= 70 ? 'bg-success' : 'bg-warning' }}" 
                                        role="progressbar" style="width: {{ ($user->individual_weight / 70) * 100 }}%"></div>
                                </div>
                                <div class="smaller mt-1 fw-bold {{ $user->individual_weight >= 70 ? 'text-success' : 'text-warning' }}">
                                    {{ $user->individual_weight }}%
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $totalSet = 30 + $user->individual_weight;
                                @endphp
                                @if($totalSet >= 100)
                                     <span class="badge bg-success text-white px-3 border border-success">Complete</span>
                                @else
                                     <span class="badge bg-warning text-dark px-3 border border-warning">Pending {{ 100 - $totalSet }}%</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-success px-3" href="{{ route('individual-objective-assignments.show', $user->id) }}">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 px-4 pb-4">
            {{ $users->links() }}
        </div>
    </x-ui.datatable-card>

    <style>
        .table thead th {
            text-transform: none;
            font-weight: 600;
            color: #1a6b3b;
            background-color: #f0f7f3;
            font-size: 0.85rem;
        }
        .smaller { font-size: 0.75rem; }
    </style>
@endsection
