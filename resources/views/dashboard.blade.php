<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--primary-color);">
            {{ __('Performance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php $role = auth()->user()->role ?? 'guest'; @endphp
            @switch($role)
                @case('hr_admin')
                    @include('appraisal.hr_admin.dashboard')
                @break

                @case('line_manager')
                    @include('appraisal.line_manager.dashboard')
                @break

                @case('dept_head')
                    @include('appraisal.dept_head.dashboard')
                @break

                @case('board')
                    @include('appraisal.board.dashboard')
                @break

                @case('super_admin')
                    @include('appraisal.super_admin.dashboard')
                @break

                @case('admin')
                    @include('appraisal.super_admin.dashboard')
                @break

                @case('employee')
                    @include('appraisal.employee.dashboard')
                @break

                @default
                    <div class="card exec-hero mb-3">
                        <div class="card-body">
                            <h4 class="hero-title mb-1">Welcome, {{ auth()->user()->name }}</h4>
                            <p class="hero-subtitle mb-0">Use the menu to navigate the performance management system.</p>
                        </div>
                    </div>
                    <div class="card quick-links-panel">
                        <div class="card-body">
                            <span class="text-muted">No role-specific dashboard is configured for this account yet.</span>
                        </div>
                    </div>
            @endswitch
        </div>
    </div>
</x-app-layout>
