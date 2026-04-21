@extends('layouts.app')

@section('content')
    @php
        foreach (App\Models\IndividualObjectiveMaster::where('is_active', true)->orderBy('title')->get() as $obj) {
            $objectiveOptions[$obj->id] = Str::ucfirst(Str::lower($obj->title ?? ''));
        }

        $mappedId = $item->individualObjectiveMasters->first()?->id ?? '';
    @endphp

    <x-ui.datatable-card title="{{ $item->exists ? 'Edit' : 'Create' }} IDP Skill Mapping" subtitle="{{ $item->exists ? Str::ucfirst(Str::lower($item->skill_area ?? '')) : 'Map a skill area to an individual objective' }}" icon="fa-sitemap" body-class="p-3">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('idp-development-objectives.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
            @if($item->exists)
            <button class="btn btn-sm btn-outline-danger" type="button"
                onclick="if(confirm('Delete this mapping?')) document.getElementById('delete-form').submit()">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            @endif
        </x-slot>

        <form method="POST" action="{{ $item->exists ? route('idp-development-objectives.update', $item) : route('idp-development-objectives.store') }}">
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif
            @include('components.alert')

            <div class="row g-3">
                <div class="col-12">
                    <div class="fw-semibold">Mapping Details</div>
                    <div class="text-muted small">Select the individual objective and skill area.</div>
                </div>

                <x-ui.form-select name="objective_master_id" label="Individual Objective" col="col-12 col-lg-6"
                    :options="$objectiveOptions" selected="{{ old('objective_master_id', $mappedId) }}" />

                <x-ui.form-field name="skill_area" label="Skill Area" required="true" col="col-12 col-lg-6"
                    value="{{ old('skill_area', $item->skill_area ?? '') }}" placeholder="e.g., Advanced Excel" />

                <div class="col-12 col-lg-6">
                    <div class="form-check form-switch mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="text-muted small">Inactive skill areas won't appear in employee IDP selection.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <x-ui.button variant="secondary" href="{{ route('idp-development-objectives.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> {{ $item->exists ? 'Update' : 'Create' }} Mapping
                </x-ui.button>
            </div>
        </form>

        @if($item->exists)
        <form id="delete-form" method="POST" action="{{ route('idp-development-objectives.destroy', $item) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </x-ui.datatable-card>
@endsection