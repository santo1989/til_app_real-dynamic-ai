@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark">Departmental Review Hub</h2>
            <p class="text-muted">Provide qualitative and quantitative reviews for your assigned departmental objectives.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold text-dark mb-0">My Assigned Objectives</h5>
                </div>
                <div class="table-responsive px-2 pb-3">
                    <table class="table table-hover align-middle mb-0 datatable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Department</th>
                                <th>Objective</th>
                                <th>Midterm Status</th>
                                <th>Final Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $asgn)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $asgn->department->name }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $asgn->master->title }}</div>
                                    <div class="small text-muted">Weightage: {{ $asgn->weightage }}%</div>
                                </td>
                                <td>
                                     @if($asgn->midterm_status === 'completed')
                                         <span class="badge bg-success text-white rounded-pill">Completed</span>
                                     @elseif($asgn->midterm_status === 'triggered')
                                          <span class="badge bg-warning text-dark rounded-pill">Awaiting Notes</span>
                                     @else
                                         <span class="badge bg-light text-dark rounded-pill">Not Started</span>
                                     @endif
                                </td>
                                <td>
                                     @if($asgn->final_status === 'completed')
                                         <span class="badge bg-success text-white rounded-pill">Completed</span>
                                     @elseif($asgn->final_status === 'triggered')
                                         <span class="badge bg-warning text-dark rounded-pill">Awaiting Marks</span>
                                     @else
                                         <span class="badge bg-light text-dark rounded-pill">Not Started</span>
                                     @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        @if($asgn->midterm_status === 'triggered' || $asgn->midterm_status === 'completed')
                                        <a href="{{ route('appraisal.dept.midterm', $asgn->id) }}" 
                                           class="btn btn-sm btn-{{ $asgn->midterm_status === 'completed' ? 'outline-mint' : 'mint' }} rounded-pill px-3">
                                           Midterm Review
                                        </a>
                                        @endif

                                        @if($asgn->final_status === 'triggered' || $asgn->final_status === 'completed')
                                        <a href="{{ route('appraisal.dept.final', $asgn->id) }}" 
                                           class="btn btn-sm btn-{{ $asgn->final_status === 'completed' ? 'outline-success' : 'success' }} rounded-pill px-3 ms-2">
                                           Final Evaluation
                                        </a>
                                        @endif
                                        
                                        @if(!$asgn->midterm_status && !$asgn->final_status)
                                        <button class="btn btn-sm btn-light disabled rounded-pill px-3">Locked</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted italic">
                                    You have not been assigned as an authority for any departmental objectives this financial year.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

            <style>
    .btn-mint { background-color: #1a6b3b; color: white; }
    .btn-mint:hover { background-color: #14552e; color: white; }
    .btn-outline-mint { border-color: #1a6b3b; color: #1a6b3b; }
    .btn-outline-mint:hover { background-color: #1a6b3b; color: white; }
</style>
@endsection
