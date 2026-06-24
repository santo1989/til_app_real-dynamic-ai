@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-end mb-3">
            <button onclick="window.print()" class="btn btn-secondary d-print-none">
                <i class="fas fa-print me-1"></i> Print / Preview
            </button>
        </div>

        @include('components.alert')

        @include('appraisal.shared.year_end_appraisal', [
            'employee' => $user,
            'fyLabel' => $activeFY,
            'deptObjectives' => $deptObjectives,
            'individualObjectives' => $individualObjectives,
            'idps' => $idps,
            'appraisal' => $appraisal,
            'mode' => 'read',
            'readOnly' => true,
        ])
    </div>
@endsection
