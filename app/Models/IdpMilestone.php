<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IdpMilestone extends Model
{
    protected $fillable = ['idp_id', 'title', 'description', 'resource_required', 'start_date', 'end_date', 'progress', 'status', 'attainment', 'visible_demonstration', 'hr_input', 'attained_by_id', 'attained_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
        'attainment' => 'boolean',
        'attained_at' => 'datetime',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->normalizeText($value);
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = $this->normalizeText($value);
    }

    public function setResourceRequiredAttribute($value): void
    {
        $this->attributes['resource_required'] = $this->normalizeText($value);
    }

    public function setVisibleDemonstrationAttribute($value): void
    {
        $this->attributes['visible_demonstration'] = $this->normalizeText($value);
    }

    public function setHrInputAttribute($value): void
    {
        $this->attributes['hr_input'] = $this->normalizeText($value);
    }

    public function getTitleSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['title'] ?? null);
    }

    public function getDescriptionSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['description'] ?? null);
    }

    public function getResourceRequiredSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['resource_required'] ?? null);
    }

    public function getVisibleDemonstrationSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['visible_demonstration'] ?? null);
    }

    public function getHrInputSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['hr_input'] ?? null);
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }
        return Str::upper($trimmed);
    }

    private function sentenceCase($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }
        return Str::ucfirst(Str::lower($trimmed));
    }
}
