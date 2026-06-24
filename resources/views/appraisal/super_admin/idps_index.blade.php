@extends('layouts.app')

@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> All IDPs</h5>
            <div class="d-flex align-items-center gap-2">
                @can('create', App\Models\Idp::class)
                    <a href="{{ route('idps.create') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-plus"></i> Create IDP
                    </a>
                @endcan
                 <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('idps-table-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="idps-table-container" data-auto-refresh="true"
            data-refresh-url="{{ route('idps.index') }}" data-refresh-target="#idps-table-container">
            @if (!empty($activeFY ?? null))
                <div class="alert alert-info py-2 mb-3">Active Financial Year: <strong>{{ $activeFY }}</strong></div>
            @endif

            @if (!empty($teamUsers ?? null) && count($teamUsers) > 0)
                <form method="GET" action="{{ route('idps.index') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-md-5">
                        <label for="user_id" class="form-label mb-1">Filter By Employee</label>
                        <select name="user_id" id="user_id" class="form-control form-control-sm">
                            <option value="">All Team Members</option>
                            @foreach ($teamUsers as $u)
                                <option value="{{ $u->id }}"
                                    {{ (int) ($selectedUserId ?? 0) === (int) $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                    </div>
                </form>
            @endif

            <div class="table-responsive-custom">
                <table class="table table-striped datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th class="hide-mobile">Description</th>
                            <th>Status</th>
                            <th class="hide-mobile">LM</th>
                            <th class="hide-mobile">HR</th>
                            <th class="hide-mobile">Review Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($idps as $i)
                            @php
                                $lmReviewed =
                                    !empty(trim((string) ($i->expected_benefits ?? ''))) ||
                                    !empty(trim((string) ($i->action_plan ?? ''))) ||
                                    !empty(trim((string) ($i->resources_required ?? ''))) ||
                                    !empty($i->review_date) ||
                                    !is_null($i->attainment) ||
                                    !empty(trim((string) ($i->visible_demonstration ?? '')));
                                $hrDone = !empty(trim((string) ($i->hr_input ?? '')));
                            @endphp
                            <tr>
                                <td>{{ $i->id }}</td>
                                <td class="text-truncate-mobile">{{ $i->user->name ?? 'N/A' }}</td>
                                <td class="hide-mobile">
                                    {{ Str::limit($i->description_sentence_case ?? $i->description, 60) }}</td>
                                <td>
                                    <span
                                        class="badge badge-responsive bg-{{ $i->status === 'completed' ? 'success' : ($i->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($i->status ?? 'n/a') }}</span>
                                </td>
                                <td class="hide-mobile">
                                    <span class="badge {{ $lmReviewed ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $lmReviewed ? 'Reviewed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="hide-mobile">
                                    <span class="badge {{ $hrDone ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $hrDone ? 'Done' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="hide-mobile">{{ optional($i->review_date)->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-end">
                                    @php
                                        $reviewRoute = (auth()->user()?->role ?? null) === 'line_manager'
                                            ? route('idp.team.review', $i)
                                            : route('idp.hr.review', $i);
                                    @endphp
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $reviewRoute }}">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
