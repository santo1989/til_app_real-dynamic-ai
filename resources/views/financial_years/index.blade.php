@extends('layouts.app')
@section('content')
    <x-ui.datatable-card title="Financial Years" subtitle="Manage appraisal cycles." icon="fa-calendar-alt"
        :count="$financialYears->count()" :create-url="route('financial-years.create')" create-label="Create">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>Label</th>
                        <th class="hide-mobile">Start Date</th>
                        <th class="hide-mobile">End Date</th>
                        <th class="hide-mobile">Revision Cutoff</th>
                        <th>Status</th>
                        <th>Revisions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($financialYears as $fy)
                        @php
                            $statusClass = match($fy->status) {
                                'active' => 'bg-success',
                                'upcoming' => 'bg-primary',
                                'closed' => 'bg-secondary',
                                default => 'bg-secondary',
                            };
                            $revisionLabel = $fy->isRevisionAllowed() ? 'Open' : 'Locked';
                            $revisionClass = $fy->isRevisionAllowed() ? 'bg-success' : 'bg-danger';
                        @endphp
                        <tr class="{{ $fy->is_active ? 'table-success' : '' }}">
                            <td>
                                <div class="fw-semibold">{{ $fy->label }}</div>
                                @if ($fy->is_active)
                                    <span class="badge bg-success">ACTIVE</span>
                                @endif
                            </td>
                            <td class="hide-mobile">{{ optional($fy->start_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="hide-mobile">{{ optional($fy->end_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="hide-mobile">{{ optional($fy->revision_cutoff)->format('M d, Y') ?? 'Auto' }}</td>
                            <td>
                                <span class="badge badge-responsive {{ $statusClass }}">{{ ucfirst($fy->status ?? 'N/A') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-responsive {{ $revisionClass }}">{{ $revisionLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('financial-years.show', $fy) }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('financial-years.edit', $fy) }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if (!$fy->is_active && $fy->status !== 'closed')
                                        <form action="{{ route('financial-years.activate', $fy) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                onclick="return confirm('Activate this financial year?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($fy->is_active && $fy->status !== 'closed')
                                        <form action="{{ route('financial-years.close', $fy) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-warning"
                                                onclick="return confirm('Close this financial year?')">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if (!$fy->is_active)
                                        <form action="{{ route('financial-years.destroy', $fy) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this financial year?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="alert alert-info mb-0 mt-2 small">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Note:</strong> Only one FY can be active. Revision cutoff is auto-set to 9 months from start date.
            Active FYs cannot be deleted.
        </div>
    </x-ui.datatable-card>
@endsection