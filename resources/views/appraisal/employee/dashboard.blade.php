@extends('layouts.app')
@section('content')
    <div class="card exec-hero mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="hero-title mb-1">Employee Dashboard</h4>
                <p class="hero-subtitle mb-0">Welcome, {{ auth()->user()->name }}. Quick access to your objectives and
                    reviews.</p>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card exec-stat-card text-center">
                <div class="card-body">
                    <h6 class="stat-label">My Objectives</h6>
                    <div class="stat-value">{{ $stats['my_objectives'] ?? '—' }}</div>
                    <a href="{{ route('objectives.my') }}" class="btn btn-sm btn-outline-primary mt-2">Set / Edit</a>
                    <a href="{{ route('objectives.my.form') }}"
                        class="btn btn-sm btn-outline-success mt-2 @if (($stats['my_objectives'] ?? 0) == 0) disabled @endif">View
                        Form</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card exec-stat-card text-center">
                <div class="card-body">
                    <h6 class="stat-label">Midterm</h6>
                    <div class="stat-value">{{ $stats['midterm_due'] ?? '—' }}</div>
                    <a href="{{ route('appraisals.midterm') }}" class="btn btn-sm btn-outline-warning mt-2">Start</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card exec-stat-card text-center">
                <div class="card-body">
                    <h6 class="stat-label">Year-End</h6>
                    <div class="stat-value">{{ $stats['yearend_due'] ?? '—' }}</div>
                    <a href="{{ route('appraisals.yearend') }}" class="btn btn-sm btn-outline-success mt-2">Start</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card exec-stat-card text-center">
                <div class="card-body">
                    <h6 class="stat-label">My IDPs</h6>
                    <div class="stat-value">{{ $stats['my_idps'] ?? '—' }}</div>
                    <a href="{{ route('idp.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Open</a>
                </div>
            </div>
        </div>
    </div>
@endsection
