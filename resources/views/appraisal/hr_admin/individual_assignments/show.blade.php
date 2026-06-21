@extends('layouts.app')

@section('content')
    @php
        $hrLocked = !empty(trim((string) ($appraisal?->supervisor_comments ?? '')));
    @endphp

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('individual-objective-assignments.index') }}" class="text-success text-decoration-none small fw-bold d-inline-block">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            @if($hrLocked)
                <span class="badge bg-success">HR Comment Submitted</span>
            @endif
        </div>

        @include('components.alert')

        <form method="POST" action="{{ route('individual-objective-assignments.hr-comment', $user->id) }}">
            @csrf

            @include('appraisal.shared.year_end_appraisal', [
                'employee' => $user,
                'fyLabel' => $fyLabel,
                'deptObjectives' => $deptObjectives,
                'individualObjectives' => $individualObjectives,
                'idps' => $idps,
                'appraisal' => $appraisal,
                'mode' => 'hr',
                'readOnly' => false,
            ])

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" @if($hrLocked) disabled @endif>Save HR Comment</button>
            </div>
        </form>
    </div>
@endsection
