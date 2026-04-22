@php
    $user = auth()->user();
    $role = $user?->role ?? 'employee';

    $groups = [];

    $groups[] = [
        'title' => null,
        'items' => [
            [
                'label' => 'Dashboard',
                'icon' => 'fa-house',
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
            ],
        ],
    ];

    if ($role === 'employee') {
        $groups[] = [
            'title' => 'My Appraisal',
            'items' => [
                ['label' => 'My Objectives', 'icon' => 'fa-bullseye', 'route' => 'objectives.my', 'active' => request()->routeIs('objectives.my')],
                ['label' => 'My IDP', 'icon' => 'fa-graduation-cap', 'route' => 'idp.index', 'active' => request()->routeIs('idp.*')],
                ['label' => 'Midterm', 'icon' => 'fa-calendar-check', 'route' => 'appraisals.midterm', 'active' => request()->routeIs('appraisals.midterm*')],
                ['label' => 'Year-End', 'icon' => 'fa-flag-checkered', 'route' => 'appraisals.yearend', 'active' => request()->routeIs('appraisals.yearend*')],
            ],
        ];
    }

    if (in_array($role, ['line_manager', 'hr_admin', 'super_admin'], true)) {
        $groups[] = [
            'title' => 'Team',
            'items' => array_values(array_filter([
                ['label' => 'Team Objectives', 'icon' => 'fa-users-cog', 'route' => 'objectives.team', 'active' => request()->routeIs('objectives.team')],
                ['label' => 'Approvals', 'icon' => 'fa-check-double', 'route' => 'objectives.approvals', 'active' => request()->routeIs('objectives.approvals*')],
                $role === 'line_manager'
                    ? ['label' => 'Midterm Review', 'icon' => 'fa-calendar-check', 'route' => 'appraisal.midterm.list', 'active' => request()->routeIs('appraisal.midterm.*')]
                    : null,
                $role === 'line_manager'
                    ? ['label' => 'Final Assessment', 'icon' => 'fa-flag-checkered', 'route' => 'appraisal.final.list', 'active' => request()->routeIs('appraisal.final.*')]
                    : null,
                $role === 'line_manager'
                    ? ['label' => 'Dept. Objectives', 'icon' => 'fa-building', 'route' => 'team.objectives.index', 'active' => request()->routeIs('team.objectives.*')]
                    : null,
            ])),
        ];
    }

    if ($role === 'dept_head') {
        $groups[] = [
            'title' => 'Department',
            'items' => [
                ['label' => 'Dept. Objectives', 'icon' => 'fa-building', 'route' => 'objectives.department', 'active' => request()->routeIs('objectives.department')],
                ['label' => 'Export Objectives', 'icon' => 'fa-file-export', 'route' => 'department.objectives.export', 'active' => request()->routeIs('department.objectives.export')],
            ],
        ];
    }

    if ($role === 'board') {
        $groups[] = [
            'title' => 'Board',
            'items' => [
                ['label' => 'Set Dept. Objectives', 'icon' => 'fa-layer-group', 'route' => 'objectives.board.index', 'active' => request()->routeIs('objectives.board.*')],
                ['label' => 'Financial Years', 'icon' => 'fa-calendar-alt', 'route' => 'financial-years.index', 'active' => request()->routeIs('financial-years.*')],
            ],
        ];
    }

    if (in_array($role, ['hr_admin', 'super_admin', 'admin'], true)) {
        $groups[] = [
            'title' => 'People',
            'items' => [
                ['label' => 'Users', 'icon' => 'fa-users', 'route' => 'users.index', 'active' => request()->routeIs('users.*')],
                ['label' => 'Departments', 'icon' => 'fa-building', 'route' => 'departments.index', 'active' => request()->routeIs('departments.*')],
                ['label' => 'Teams', 'icon' => 'fa-people-group', 'route' => 'teams.index', 'active' => request()->routeIs('teams.*')],
            ],
        ];

        $groups[] = [
            'title' => 'Master Data',
            'items' => [
                ['label' => 'Individual Objectives', 'icon' => 'fa-list-check', 'route' => 'individual-objective-masters.index', 'active' => request()->routeIs('individual-objective-masters.*')],
                ['label' => 'Dept/Team Objectives', 'icon' => 'fa-diagram-project', 'route' => 'departmental-objective-masters.index', 'active' => request()->routeIs('departmental-objective-masters.*')],
                ['label' => 'IDP Skill Mapping', 'icon' => 'fa-sitemap', 'route' => 'idp-development-objectives.index', 'active' => request()->routeIs('idp-development-objectives.*')],
                ['label' => 'Financial Years', 'icon' => 'fa-calendar-alt', 'route' => 'financial-years.index', 'active' => request()->routeIs('financial-years.*')],
            ],
        ];

        $groups[] = [
            'title' => 'Operations',
            'items' => [
                ['label' => 'Set Dept/Team Objective', 'icon' => 'fa-bullseye', 'route' => 'departmental-objective-assignments.index', 'active' => request()->routeIs('departmental-objective-assignments.*')],
                ['label' => 'Individual Objective List', 'icon' => 'fa-user-check', 'route' => 'individual-objective-assignments.index', 'active' => request()->routeIs('individual-objective-assignments.*')],
                ['label' => 'Appraisals', 'icon' => 'fa-chart-line', 'route' => 'appraisals.index', 'active' => request()->routeIs('appraisals.index')],
                ['label' => 'IDPs', 'icon' => 'fa-graduation-cap', 'route' => 'idps.index', 'active' => request()->routeIs('idps.index')],
                ['label' => 'PIPs', 'icon' => 'fa-triangle-exclamation', 'route' => 'pips.index', 'active' => request()->routeIs('pips.*')],
            ],
        ];

        $groups[] = [
            'title' => 'Reports',
            'items' => array_values(array_filter([
                ['label' => 'Reports', 'icon' => 'fa-chart-pie', 'route' => 'reports.index', 'active' => request()->routeIs('reports.*')],
                in_array($role, ['super_admin', 'admin'], true)
                    ? ['label' => 'Audit Logs', 'icon' => 'fa-clipboard-list', 'route' => 'audit-logs.index', 'active' => request()->routeIs('audit-logs.*')]
                    : null,
            ])),
        ];
    }
@endphp

<nav class="nav nav-pills flex-column gap-1 app-sidebar-nav">
    @foreach ($groups as $group)
        @if (!empty($group['title']))
            <div class="px-2 pt-3 pb-1 small text-uppercase text-muted fw-semibold app-sidebar-group-title">
                {{ $group['title'] }}
            </div>
        @endif
        @foreach ($group['items'] as $item)
            @php
                $href = isset($item['params'])
                    ? route($item['route'], $item['params'])
                    : route($item['route']);
                $classes = $item['active'] ? 'nav-link active' : 'nav-link text-body';
            @endphp
            <a class="{{ $classes }}" href="{{ $href }}" title="{{ $item['label'] }}" data-bs-toggle="tooltip"
                data-bs-placement="right">
                <i class="fas {{ $item['icon'] }} me-2 app-sidebar-icon"></i>
                <span class="app-sidebar-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    @endforeach
</nav>
