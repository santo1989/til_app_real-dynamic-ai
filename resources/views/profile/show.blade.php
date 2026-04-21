@extends('layouts.app')

@section('content')
    @php
        $roleLabel = ucwords(str_replace('_', ' ', $user->role ?? 'user'));
        $statusLabel = $user->is_active ? 'Active' : 'Inactive';
        $statusBadge = $user->is_active ? 'bg-success' : 'bg-secondary';
        $isProfileRoute = request()->routeIs('profile.show');
        $editUrl = $isProfileRoute ? route('profile.edit') : route('users.edit', $user);

        $name = trim($user->name ?? '');
        $nameParts = $name !== '' ? preg_split('/\s+/', $name) : [];
        $initials = '';
        if (is_array($nameParts)) {
            foreach ($nameParts as $part) {
                if ($part !== '') {
                    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
                }
                if (mb_strlen($initials) >= 2) {
                    break;
                }
            }
        }
        if ($initials === '') {
            $initials = 'U';
        }

        $backToUsers = !$isProfileRoute;
    @endphp

    <div class="row g-3 mt-0 mb-3">
        <div class="col-12 col-md-4">
            <div class="card exec-stat-card rounded-0 text-center h-100">
                <div class="card-body">
                    <div class="mb-2 text-muted"><i class="fas fa-bullseye fa-2x"></i></div>
                    <div class="stat-value">{{ $user->objectives->count() }}</div>
                    <div class="stat-label">Objectives</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card exec-stat-card rounded-0 text-center h-100">
                <div class="card-body">
                    <div class="mb-2 text-muted"><i class="fas fa-chart-line fa-2x"></i></div>
                    <div class="stat-value">{{ $user->appraisals->count() }}</div>
                    <div class="stat-label">Appraisals</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card exec-stat-card rounded-0 text-center h-100">
                <div class="card-body">
                    <div class="mb-2 text-muted"><i class="fas fa-graduation-cap fa-2x"></i></div>
                    <div class="stat-value">{{ $user->idps->count() }}</div>
                    <div class="stat-label">IDPs</div>
                </div>
            </div>
        </div>
    </div>

    <x-ui.datatable-card title="{{ $isProfileRoute ? 'My Profile' : 'User Profile' }}"
        subtitle="{{ $roleLabel }} • {{ $user->department->name ?? 'No Department' }}" icon="fa-user"
        body-class="p-3">
        <x-slot name="actions">
            @if ($backToUsers)
                <x-ui.button variant="secondary" href="{{ route('users.index') }}" class="btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </x-ui.button>
            @endif
            <x-ui.button variant="primary" href="{{ $editUrl }}" class="btn-sm">
                <i class="fas fa-pen me-1"></i> Edit
            </x-ui.button>
        </x-slot>

        <div class="row g-3 align-items-start">
            <div class="col-12 col-md-4 col-xl-3">
                <div class="d-flex align-items-center gap-3">
                    @if ($user->user_image)
                        <img src="{{ asset('storage/' . $user->user_image) }}" alt="Profile" class="rounded-circle"
                            width="72" height="72">
                    @else
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                            style="width:72px;height:72px;background:rgba(42,135,96,.12);color:var(--primary-hover,#075432);font-size:1.25rem;">
                            {{ $initials }}
                        </span>
                    @endif
                    <div class="min-w-0">
                        <div class="h5 mb-1 text-truncate-mobile">{{ $user->name }}</div>
                        <div class="text-muted text-truncate-mobile">{{ $user->designation ?? '—' }}</div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">{{ $roleLabel }}</span>
                            <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8 col-xl-9">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Employee ID</div>
                        <div class="fw-semibold">{{ $user->employee_id ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Email</div>
                        <div class="fw-semibold">{{ $user->email ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Department</div>
                        <div class="fw-semibold">{{ $user->department->name ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Line Manager</div>
                        <div class="fw-semibold">{{ $user->lineManager->name ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Date of Joining</div>
                        <div class="fw-semibold">
                            {{ $user->date_of_joining ? \Carbon\Carbon::parse($user->date_of_joining)->format('d M, Y') : '—' }}
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="small text-muted">Tenure in Current Role</div>
                        <div class="fw-semibold">{{ $user->tenure_in_current_role ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.datatable-card>

    @if ($user->objectives->count() > 0)
        <x-ui.datatable-card title="Recent Objectives" subtitle="Latest 5 objectives" icon="fa-bullseye"
            body-class="p-0">
            <x-slot name="actions">
                <x-ui.button variant="secondary" href="{{ route('users.objectives.pdf', ['user_id' => $user->id]) }}"
                    class="btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </x-ui.button>
            </x-slot>
            <div class="table-responsive-custom">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="hide-mobile">Type</th>
                            <th class="hide-mobile">Weight</th>
                            <th>Status</th>
                            <th class="hide-mobile">FY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->objectives->take(5) as $objective)
                            @php
                                $statusClass = match ($objective->status) {
                                    'set' => 'bg-success',
                                    'draft' => 'bg-warning',
                                    'submitted' => 'bg-info',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ Str::limit($objective->description, 60) }}</td>
                                <td class="hide-mobile"><span class="badge bg-light text-dark border">{{ $objective->type }}</span></td>
                                <td class="hide-mobile">{{ $objective->weightage }}%</td>
                                <td><span class="badge {{ $statusClass }}">{{ ucfirst($objective->status) }}</span></td>
                                <td class="hide-mobile">{{ $objective->financial_year }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.datatable-card>
        <div class="mb-3"></div>
    @endif

    @if ($user->appraisals->count() > 0)
        @php $latest = $user->appraisals->first(); @endphp
        <x-ui.datatable-card title="Recent Appraisals" subtitle="Latest 5 appraisals" icon="fa-chart-line"
            body-class="p-0">
            <x-slot name="actions">
                @if ($latest)
                    <x-ui.button variant="secondary"
                        href="{{ route('appraisals.yearend.pdf', ['appraisal_id' => $latest->id]) }}" class="btn-sm"
                        target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </x-ui.button>
                @endif
            </x-slot>
            <div class="table-responsive-custom">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th class="hide-mobile">Date</th>
                            <th class="hide-mobile">Ach.</th>
                            <th class="hide-mobile">Total</th>
                            <th>Rating</th>
                            <th class="hide-mobile">FY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->appraisals->take(5) as $appraisal)
                            @php
                                $ratingClass = match (strtolower($appraisal->rating ?? '')) {
                                    'outstanding' => 'bg-success',
                                    'excellent' => 'bg-success',
                                    'good' => 'bg-primary',
                                    'average' => 'bg-warning',
                                    'below average' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($appraisal->type) }}</span></td>
                                <td class="hide-mobile">
                                    {{ $appraisal->date ? \Carbon\Carbon::parse($appraisal->date)->format('d M, Y') : '—' }}
                                </td>
                                <td class="hide-mobile">{{ $appraisal->achievement_score ?? '—' }}</td>
                                <td class="hide-mobile">{{ $appraisal->total_score ?? '—' }}</td>
                                <td>
                                    @if ($appraisal->rating)
                                        <span class="badge {{ $ratingClass }}">{{ $appraisal->rating }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="hide-mobile">{{ $appraisal->financial_year }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.datatable-card>
        <div class="mb-3"></div>
    @endif

    @if ($user->idps->count() > 0)
        <x-ui.datatable-card title="Recent IDPs" subtitle="Latest 5 development plans" icon="fa-graduation-cap"
            body-class="p-0">
            <div class="table-responsive-custom">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="hide-mobile">Review Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->idps->take(5) as $idp)
                            @php
                                $idpStatusClass = match ($idp->status) {
                                    'completed' => 'bg-success',
                                    'in_progress' => 'bg-primary',
                                    'pending' => 'bg-warning',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ Str::limit($idp->description, 70) }}</td>
                                <td class="hide-mobile">
                                    {{ $idp->review_date ? \Carbon\Carbon::parse($idp->review_date)->format('d M, Y') : '—' }}
                                </td>
                                <td>
                                    @if ($idp->status)
                                        <span
                                            class="badge {{ $idpStatusClass }}">{{ ucfirst(str_replace('_', ' ', $idp->status)) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.datatable-card>
    @endif
@endsection
