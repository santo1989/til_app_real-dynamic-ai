@extends('layouts.app')
@section('content')
    <div class="card exec-hero mb-3">
        <div class="card-body">
            <h4 class="hero-title mb-1">Department Head Dashboard</h4>
            <p class="hero-subtitle mb-0">Approve appraisals and monitor department objectives with confidence.</p>
        </div>
    </div>

    <div class="card quick-links-panel">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('objectives.department') }}" class="btn btn-sm btn-outline-primary">Department
                    Objectives</a>
                <a href="{{ route('department.objectives.export') }}" class="btn btn-sm btn-outline-success">Export
                    Objectives</a>
            </div>
        </div>
    </div>
@endsection
