@extends('layouts.app')

@section('content')
@php
    $readOnly = ($appraisal?->status ?? null) === \App\Models\Appraisal::STATUS_FINAL_COMPLETED;
    $individualObjectives = $objectives->where('type', 'individual');
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('appraisal.final.list') }}" class="text-success text-decoration-none small fw-bold d-inline-block">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        <div>
            @if($readOnly)
                <span class="badge bg-success me-2">Completed</span>
            @endif
            <button onclick="window.print()" class="btn btn-secondary btn-sm d-print-none">
                <i class="fas fa-print me-1"></i> Print / Preview
            </button>
        </div>
    </div>

    @include('components.alert')

    <form action="{{ route('appraisal.final.store') }}" method="POST">
        @csrf
        <input type="hidden" name="appraisal_id" value="{{ $appraisal->id }}">

        @include('appraisal.shared.year_end_appraisal', [
            'employee' => $employee,
            'fyLabel' => $activeFY,
            'deptObjectives' => $deptObjectives,
            'individualObjectives' => $individualObjectives,
            'idps' => $idps ?? collect(),
            'appraisal' => $appraisal,
            'mode' => 'lm',
            'readOnly' => $readOnly,
        ])

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary" @if($readOnly) disabled @endif>
                Submit Final Assessment
            </button>
        </div>
    </form>
</div>
@endsection
