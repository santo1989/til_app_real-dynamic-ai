@props([
    'name',
    'label',
    'required' => false,
    'col' => 'col-12',
    'help' => null,
    'accept' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = $attributes->get('id') ?? $name;
@endphp

<div class="{{ $col }}">
    <label for="{{ $inputId }}" class="form-label fw-semibold">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $inputId }}"
        @if (!is_null($accept)) accept="{{ $accept }}" @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => trim('form-control rounded-0 ' . ($hasError ? 'is-invalid' : ''))]) }}
    >
    @if ($hasError)
        <div class="invalid-feedback">{{ $errors->first($name) }}</div>
    @elseif ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
