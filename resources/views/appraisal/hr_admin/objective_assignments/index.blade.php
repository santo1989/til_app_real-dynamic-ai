@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Objective Setting" subtitle="Departmental/Team objectives for the running financial year ({{ $activeFy->label ?? 'None' }})." icon="fa-bullseye"
        :count="$departments->count()" :create-url="route('departmental-objective-assignments.create')" create-label="Set Objectives">
        
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle border-top">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Department Name</th>
                        <th style="width: 55%;">Objectives set for {{ $activeFy->label ?? 'Current FY' }}</th>
                        <th style="width: 20%;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $dept)
                        @php
                            $deptAssignments = $dept->assignments;
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">{{ $dept->name }}</td>
                            <td>
                                @if($deptAssignments->isEmpty())
                                    <span class="text-muted small italic">No objectives assigned</span>
                                @else
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                        @foreach($deptAssignments as $assignment)
                                            <li>
                                                <span class="badge rounded-pill fw-medium px-3 py-2 border" 
                                                    style="background-color: #e9f5ee; color: #1a6b3b; border-color: #1a6b3b;"
                                                    title="{{ $assignment->master->title }}">
                                                    {{ Str::limit($assignment->master->title, 40) }} ({{ $assignment->weightage }}%)
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a class="btn btn-sm btn-outline-success px-3" href="{{ route('departmental-objective-assignments.edit', $dept->id) }}">
                                        View
                                    </a>
                                    <a class="btn btn-sm btn-success px-3" href="{{ route('departmental-objective-assignments.edit', $dept->id) }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.datatable-card>

    <style>
        .table thead th {
            text-transform: none;
            font-weight: 600;
            color: #1a6b3b;
            background-color: #f0f7f3;
        }
    </style>
@endsection
