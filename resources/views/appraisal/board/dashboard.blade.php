@extends('layouts.app')
@section('content')
    <div class="card exec-hero mb-3">
        <div class="card-body">
            <h4 class="hero-title mb-1">Board Dashboard</h4>
            <p class="hero-subtitle mb-0">Set departmental objectives and drive strategic governance from one place.</p>
        </div>
    </div>

    <div class="card quick-links-panel">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('objectives.board.index') }}" class="btn btn-sm btn-outline-primary">Set Departmental
                    Objectives</a>
                <a href="{{ route('financial-years.index') }}" class="btn btn-sm btn-outline-warning">Financial Years</a>
            </div>
        </div>
    </div>
@endsection
