@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'col' => 'col-12',
    'help' => null,
    'autocomplete' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = $attributes->get('id') ?? $name;
    $resolvedValue = old($name, $value);
    if ($type === 'password') {
        $resolvedValue = null;
    }
@endphp

<div class="{{ $col }}">
    <label for="{{ $inputId }}" class="form-label fw-semibold">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        @if (!is_null($placeholder)) placeholder="{{ $placeholder }}" @endif
        @if (!is_null($autocomplete)) autocomplete="{{ $autocomplete }}" @endif
        @if (!is_null($resolvedValue)) value="{{ $resolvedValue }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => trim('form-control rounded-0 ' . ($hasError ? 'is-invalid' : ''))]) }}
    >
    @if ($hasError)
        <div class="invalid-feedback">{{ $errors->first($name) }}</div>
    @elseif ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
