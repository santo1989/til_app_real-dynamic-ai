@extends('layouts.app')
@section('content')
    <div class="card exec-hero mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="hero-title">Line Manager Dashboard</h4>
                    <p class="hero-subtitle mb-0">Welcome, {{ auth()->user()->name }} — manage your team efficiently.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card quick-links-panel">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <x-ui.button variant="primary" href="{{ route('objectives.team') }}">Team Objectives</x-ui.button>
                <a href="{{ route('objectives.approvals') }}" class="btn btn-sm btn-outline-warning">Objective Approvals</a>
                <a href="{{ route('team.objectives.index') }}" class="btn btn-sm btn-outline-info">Departmental
                    Objectives</a>
                <a href="{{ route('idp-development-objectives.index') }}" class="btn btn-sm btn-outline-secondary">IDP
                    Master Pairs</a>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card exec-stat-card text-center">
                        <div class="card-body">
                            <h6 class="stat-label">Team Members</h6>
                            <div class="stat-value">{{ $stats['team_size'] ?? '—' }}</div>
                            <small class="text-muted">Active reports</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card exec-stat-card text-center">
                        <div class="card-body">
                            <h6 class="stat-label">Team IDPs</h6>
                            <div class="stat-value">{{ $stats['team_idps'] ?? '—' }}</div>
                            <p class="text-muted">Development plans for your reports</p>
                            <a href="{{ route('objectives.team') }}" class="btn btn-sm btn-outline-info mt-2">Open Team</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card exec-stat-card text-center">
                        <div class="card-body">
                            <h6 class="stat-label">Pending Midterms</h6>
                            <div class="stat-value">{{ $stats['pending_midterms'] ?? '—' }}</div>
                            <a href="{{ route('objectives.team') }}" class="btn btn-sm btn-outline-primary mt-2">Open Team
                                Midterms</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card exec-stat-card text-center">
                        <div class="card-body">
                            <h6 class="stat-label">Pending Year-End</h6>
                            <div class="stat-value">{{ $stats['pending_yearend'] ?? '—' }}</div>
                            <a href="{{ route('objectives.team') }}" class="btn btn-sm btn-outline-success mt-2">Open Team
                                Year-End</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
