@extends('layouts.app')
@section('content')
    @php
        $refreshUrl = route('pips.index') . '?' . http_build_query(request()->query());
    @endphp

    <x-ui.datatable-card title="PIP Management" subtitle="Performance Improvement Plans and follow-up actions." icon="fa-clipboard-list"
        :count="$pips->total()" refresh-target="pips-table-container" :refresh-url="$refreshUrl" :create-url="route('pips.create')"
        create-label="Create">

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="open" @selected(request('status') == 'open')>Open</option>
                        <option value="closed" @selected(request('status') == 'closed')>Closed</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Start from</label>
                    <input type="date" name="start_date_from" value="{{ request('start_date_from') }}"
                        class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="start_date_to" value="{{ request('start_date_to') }}"
                        class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Department</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @if (isset($departments))
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted mb-1">Line Manager</label>
                    <select name="manager_id" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @if (isset($managers))
                            @foreach ($managers as $m)
                                <option value="{{ $m->id }}" @selected((string) request('manager_id') === (string) $m->id)>
                                    {{ $m->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('pips.index') }}" class="btn btn-sm btn-outline-secondary">
                        Reset
                    </a>
                    <a href="{{ route('pips.export') }}?{{ http_build_query(request()->query()) }}"
                        class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-export me-1"></i> Export CSV
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive-custom">
            <table class="table table-hover align-middle border-top">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Employee</th>
                        <th class="hide-mobile">Manager</th>
                        <th style="width: 140px;">Status</th>
                        <th class="hide-mobile">Reason</th>
                        <th class="hide-mobile" style="width: 150px;">Created</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pips as $pip)
                        @php
                            $statusLabel = ucfirst($pip->status ?? 'open');
                            $statusBadge = ($pip->status ?? 'open') === 'closed' ? 'bg-secondary' : 'bg-warning text-dark';
                        @endphp
                        <tr>
                            <td class="text-muted fw-semibold">{{ $pip->id }}</td>
                            <td class="text-truncate-mobile">
                                @if ($pip->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                                            style="width:32px;height:32px;background:rgba(42,135,96,.12);color:var(--primary-hover,#075432);">
                                            {{ mb_strtoupper(mb_substr(trim($pip->user->name ?? 'U'), 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <a class="fw-semibold text-decoration-none" href="{{ route('users.show', $pip->user->id) }}">
                                                {{ $pip->user->name }}
                                            </a>
                                            <div class="small text-muted text-truncate-mobile">{{ $pip->user->employee_id ?? '—' }}</div>
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="hide-mobile">
                                @if ($pip->user && $pip->user->lineManager)
                                    <a class="text-decoration-none" href="{{ route('users.show', $pip->user->lineManager->id) }}">
                                        {{ $pip->user->lineManager->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-responsive {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="hide-mobile text-truncate" style="max-width: 420px;">
                                {{ $pip->reason }}
                            </td>
                            <td class="hide-mobile">{{ optional($pip->created_at)->format('d-M-Y') ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('pips.show', $pip->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if (($pip->status ?? 'open') !== 'closed')
                                        <form action="{{ route('pips.close', $pip->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No PIPs found for the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 px-4 pb-4">
            {{ $pips->links() }}
        </div>

        <style>
            .table thead th {
                text-transform: none;
                font-weight: 600;
                color: #1a6b3b;
                background-color: #f0f7f3;
                font-size: 0.85rem;
            }
        </style>
    </x-ui.datatable-card>
@endsection
