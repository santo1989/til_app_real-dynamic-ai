        @extends('layouts.app')

        @section('content')
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="fw-bold text-dark mb-0">Appraisal Operations Center</h2>
                    <p class="text-muted mb-0">Manage departmental and individual appraisal lifecycles for FY {{ $activeFY }}</p>
                </div>
            </div>

            <!-- ROW 1: Departmental Objectives -->
            <div class="row g-4 mb-4">
                <!-- Dept Midterm -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Departmental Midterm Reviews</h5>
                                <small class="text-muted">Qualitative comments by assigned authorities</small>
                            </div>
                            <form action="{{ route('appraisal.trigger.all_dept_midterms') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-mint rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Send All
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive px-2 pb-3">
                            <table class="table table-hover align-middle mb-0 datatable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Department</th>
                                        <th>Authority</th>
                                        <th>Objective</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deptMidtermList as $asgn)
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{{ $asgn->department->name }}</td>
                                        <td>
                                            <div class="small fw-bold text-dark">{{ $asgn->certifyingAuthorityUser->name }}</div>
                                            <div class="text-muted x-small">{{ $asgn->certifyingAuthorityUser->designation }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark rounded-pill" title="{{ $asgn->master->title }}">
                                                {{ Str::limit($asgn->master->title, 20) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($asgn->midterm_status === 'completed')
                                                <span class="badge bg-success text-white rounded-pill">Completed</span>
                                            @elseif($asgn->midterm_status === 'triggered')
                                                <span class="badge bg-warning text-dark rounded-pill">Awaiting Notes</span>
                                            @else
                                                <span class="badge bg-light text-dark rounded-pill">Eligible</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($asgn->midterm_status === 'completed')
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3">Done</button>
                                            @elseif($asgn->midterm_status === 'triggered')
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3">Sent</button>
                                            @else
                                            <form action="{{ route('appraisal.trigger.dept_midterm', $asgn->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-mint rounded-pill px-3">Trigger</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dept Final -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Departmental Final Evaluations</h5>
                                <small class="text-muted">Quantitative scoring for departmental goals</small>
                            </div>
                            <form action="{{ route('appraisal.trigger.all_dept_finals') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Send All
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive px-2 pb-3">
                            <table class="table table-hover align-middle mb-0 datatable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Department</th>
                                        <th>Authority</th>
                                        <th>Objective</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deptFinalList as $asgn)
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{{ $asgn->department->name }}</td>
                                        <td>
                                            <div class="small fw-bold text-dark">{{ $asgn->certifyingAuthorityUser->name }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark rounded-pill">
                                                {{ Str::limit($asgn->master->title, 20) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($asgn->final_status === 'completed')
                                                <span class="badge bg-success text-white rounded-pill">Completed</span>
                                            @elseif($asgn->final_status === 'triggered')
                                                <span class="badge bg-warning text-dark rounded-pill">Awaiting Marks</span>
                                            @else
                                                <span class="badge bg-light text-dark rounded-pill">Ready</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($asgn->final_status === 'completed')
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3">Done</button>
                                            @elseif($asgn->final_status === 'triggered')
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3">Sent</button>
                                            @else
                                            <form action="{{ route('appraisal.trigger.dept_final', $asgn->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success rounded-pill px-3">Trigger</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Individual Objectives -->
            <div class="row g-4">
                <!-- Individual Midterm -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Individual Midterm Reviews</h5>
                                <small class="text-muted">Employee progress notes by Line Managers</small>
                            </div>
                            <form action="{{ route('appraisals.trigger_all_midterms') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-mint rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Send All
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive px-2 pb-3">
                            <table class="table table-hover align-middle mb-0 datatable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Employee</th>
                                        <th>Manager</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($midtermList as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $item['user']->name }}</div>
                                            <div class="text-muted x-small">{{ $item['user']->employee_id }}</div>
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $item['user']->lineManager->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($item['status'] === 'eligible')
                                                <span class="badge bg-light text-dark rounded-pill">Eligible</span>
                                            @elseif($item['status'] === 'midterm_triggered')
                                                <span class="badge bg-warning text-dark rounded-pill">Awaiting Note</span>
                                            @else
                                                <span class="badge bg-info text-dark rounded-pill">In Progress</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($item['status'] === 'eligible')
                                            <form action="{{ route('appraisals.trigger_midterm', $item['user']->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-mint rounded-pill px-3">Trigger</button>
                                            </form>
                                            @else
                                            <button class="btn btn-sm btn-light disabled rounded-pill px-3">Sent</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Individual Final -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Individual Final Evaluations</h5>
                                <small class="text-muted">End-of-year marking by Line Managers</small>
                            </div>
                            <form action="{{ route('appraisals.trigger_all_finals') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Send All
                                </button>
                            </form>
                        </div>
                        <div class="table-responsive px-2 pb-3">
                            <table class="table table-hover align-middle mb-0 datatable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Employee</th>
                                        <th>Manager</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($finalYearList as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $item['user']->name }}</div>
                                        </td>
                                        <td>
                                            <div class="small text-muted">{{ $item['user']->lineManager->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($item['status'] === \App\Models\Appraisal::STATUS_FINAL_COMPLETED)
                                                <span class="badge bg-success text-white rounded-pill"><i class="fas fa-check-circle me-1"></i> Completed</span>
                                            @elseif($item['status'] === \App\Models\Appraisal::STATUS_READY_FOR_FINAL)
                                                <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-clock me-1"></i> Triggered</span>
                                            @else
                                                <span class="badge bg-light text-dark rounded-pill">Ready for Final</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            @if(in_array($item['status'], [\App\Models\Appraisal::STATUS_MIDTERM_TRIGGERED, \App\Models\Appraisal::STATUS_IN_PROGRESS, \App\Models\Appraisal::STATUS_MIDTERM_COMPLETED]))
                                            <form action="{{ route('appraisals.trigger_final', $item['user']->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success rounded-pill px-3">Trigger</button>
                                            </form>
                                            @else
                                                <button class="btn btn-sm btn-light disabled rounded-pill px-3">Sent</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
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
            .x-small { font-size: 0.7rem; }
</style>
        @endsection