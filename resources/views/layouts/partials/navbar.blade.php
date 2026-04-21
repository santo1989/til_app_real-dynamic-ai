<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top" data-bs-theme="dark">
    <div class="container-fluid px-3">
        <div class="d-flex align-items-center gap-2">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold text-white m-0"
                href="{{ route('dashboard') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-white text-primary"
                style="width:32px;height:32px;">
                <i class="fas fa-clipboard-check"></i>
            </span>
            <span>{{ config('app.name', 'TIL Appraisals') }}</span>
            </a>

            @auth
                <button class="btn btn-sm btn-outline-light d-none d-lg-inline-flex" type="button"
                    id="appSidebarToggle" aria-label="Toggle sidebar" aria-pressed="false"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="Collapse sidebar">
                    <i class="fas fa-angles-left"></i>
                </button>

                <button class="btn btn-sm btn-outline-light d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#appSidebarOffcanvas" aria-controls="appSidebarOffcanvas">
                    <i class="fas fa-bars me-1"></i> Menu
                </button>
            @endauth
        </div>
        @auth
            @php
                $currentRole = auth()->user()->role ?? 'user';
                $roleLabel = ucwords(str_replace('_', ' ', $currentRole));
                $nameParts = preg_split('/\s+/', trim(auth()->user()->name));
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
            @endphp
            <div class="d-flex align-items-center gap-2 ms-auto">
                <span class="text-white small fw-semibold d-none d-lg-inline">{{ auth()->user()->name }}</span>
                <span class="badge bg-light text-dark">{{ $roleLabel }}</span>

                <form method="POST" action="{{ route('logout') }}" class="m-0 d-none d-md-inline-block">
                    @csrf
                    <button class="btn btn-sm btn-light text-primary fw-semibold" type="submit">
                        <i class="fas fa-right-from-bracket me-1"></i> Logout
                    </button>
                </form>

                <ul class="navbar-nav mb-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                            id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span
                                class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                                style="width:32px;height:32px;background:rgba(255,255,255,.18);color:#ffffff;">
                                {{ $initials !== '' ? $initials : 'U' }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ $roleLabel }}</div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i
                                        class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i
                                        class="fas fa-gear me-2"></i>Settings</a></li>
                            @if (session()->has('impersonator_id'))
                                <li>
                                    <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
                                        @csrf
                                        <button class="dropdown-item text-warning" type="submit">
                                            <i class="fas fa-user-lock me-2"></i>Stop Impersonation
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button class="dropdown-item" type="submit">
                                        <i class="fas fa-right-from-bracket me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</nav>
