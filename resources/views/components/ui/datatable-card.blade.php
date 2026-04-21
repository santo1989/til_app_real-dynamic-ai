@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'count' => null,
    'refreshTarget' => null,
    'refreshUrl' => null,
    'createUrl' => null,
    'createLabel' => 'Create',
    'bodyClass' => '',
])

@php
    $cardClass = trim('card card-responsive rounded-0 ' . ($attributes->get('class') ?? ''));
    $containerId = $refreshTarget ?: null;
    $hasRefresh = !empty($containerId) && !empty($refreshUrl);
@endphp

<div class="{{ $cardClass }} datatable-card">
    <div class="card-header datatable-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0">
                    @if ($icon)
                        <i class="fas {{ $icon }}"></i>
                    @endif
                    {{ $title }}
                </h5>
                @if (!is_null($count))
                    <span class="badge bg-light text-dark border">{{ $count }} total</span>
                @endif
            </div>
            @if ($subtitle)
                <div class="text-muted small">{{ $subtitle }}</div>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($hasRefresh)
                <span class="badge bg-light text-dark border d-none d-md-inline">
                    <i class="fas fa-arrows-rotate"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-secondary"
                    onclick="AutoRefresh.manualRefresh('{{ $containerId }}')">
                    <i class="fas fa-rotate"></i>
                </button>
            @endif
            @if ($createUrl)
                <a class="btn btn-sm btn-primary" href="{{ $createUrl }}">
                    <i class="fas fa-plus me-1"></i> {{ $createLabel }}
                </a>
            @endif
            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    </div>

    <div class="card-body {{ $bodyClass }}"
        @if ($hasRefresh) id="{{ $containerId }}" data-auto-refresh="true" data-refresh-url="{{ $refreshUrl }}" data-refresh-target="#{{ $containerId }}" @endif>
        {{ $slot }}
    </div>
</div>
