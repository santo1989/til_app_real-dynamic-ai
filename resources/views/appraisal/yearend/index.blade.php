@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
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
