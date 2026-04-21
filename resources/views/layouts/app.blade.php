<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ config('app.name', 'Performance Appraisal') }}
        @auth
            - {{ auth()->user()->name }} ({{ ucwords(str_replace('_', ' ', auth()->user()->role ?? 'user')) }})
        @endauth
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auto-refresh.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>

<body>
    @include('layouts.partials.navbar')
    @auth
        @include('layouts.partials.sidebar-offcanvas')
    @endauth
    <div class="app-shell">
        @auth
            <div class="app-shell-sidebar d-none d-lg-block">
                @include('layouts.partials.sidebar')
            </div>
        @endauth
        <div class="app-shell-content">
            <div class="container-fluid pt-3 pt-md-4 px-3 px-md-4">
                    @if (session()->has('impersonator_id'))
                        @php $impersonator = \App\Models\User::find(session('impersonator_id')); @endphp
                        <div class="alert alert-warning d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Impersonation Active:</strong>
                                You are currently impersonating <strong>{{ auth()->user()->name }}</strong>.
                                @if ($impersonator)
                                    <small class="text-muted">(original: {{ $impersonator->name }})</small>
                                @endif
                            </div>
                            <div>
                                <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Stop Impersonation</button>
                                </form>
                            </div>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @yield('content')
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function() {
            $('.datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
                $(this).DataTable({
                    responsive: true,
                    pageLength: 25
                });
            });
        });
    </script>
    <!-- Auto Refresh Module -->
    <script src="{{ asset('js/auto-refresh.js') }}"></script>
    <script>
        // Enable Bootstrap tooltips for better affordance
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Confirm impersonation actions to avoid accidental switches
        $(function() {
            $('.impersonate-form').on('submit', function(e) {
                var user = $(this).data('user') || 'the user';
                if (!confirm('Start impersonating ' + user +
                        '? You can stop impersonation via your profile menu.')) {
                    e.preventDefault();
                }
            });
        });

        (function() {
            var key = 'app.sidebar.collapsed';
            var toggleBtn = document.getElementById('appSidebarToggle');

            function setCollapsed(collapsed) {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                    var icon = toggleBtn.querySelector('i');
                    if (icon) {
                        icon.className = collapsed ? 'fas fa-angles-right' : 'fas fa-angles-left';
                    }
                }
            }

            var saved = localStorage.getItem(key);
            if (saved === '1') {
                setCollapsed(true);
            } else if (saved === '0') {
                setCollapsed(false);
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    var next = !document.body.classList.contains('sidebar-collapsed');
                    setCollapsed(next);
                    localStorage.setItem(key, next ? '1' : '0');
                });
            }
        })();
    </script>
</body>

</html>

<!-- ...existing code... -->
