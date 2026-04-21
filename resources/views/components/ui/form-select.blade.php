@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => '-- Select --',
    'required' => false,
    'col' => 'col-12',
    'help' => null,
])

@php
    $hasError = $errors->has($name);
    $inputId = $attributes->get('id') ?? $name;
    // Convert empty string to null so Laravel's nullable rule works
    $resolvedSelected = old($name, $selected);
    if ($resolvedSelected === '') {
        $resolvedSelected = null;
    }
    $isList = function_exists('array_is_list')
        ? array_is_list($options)
        : array_keys($options) === range(0, count($options) - 1);
@endphp

<div class="{{ $col }}">
    <label for="{{ $inputId }}" class="form-label fw-semibold">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <select
        name="{{ $name }}"
        id="{{ $inputId }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => trim('form-select rounded-0 ' . ($hasError ? 'is-invalid' : ''))])->except(['value']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $text)
            @php
                if ($isList) {
                    $optValue = is_array($text) ? ($text['value'] ?? '') : $text;
                    $optText = is_array($text) ? ($text['label'] ?? ($text['text'] ?? '')) : $text;
                } else {
                    $optValue = $value;
                    $optText = is_array($text) ? ($text['label'] ?? ($text['text'] ?? '')) : $text;
                }
            @endphp
            <option value="{{ $optValue }}" @selected((string) $resolvedSelected === (string) $optValue)>{{ $optText }}</option>
        @endforeach
    </select>
    @if ($hasError)
        <div class="invalid-feedback">{{ $errors->first($name) }}</div>
    @elseif ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
