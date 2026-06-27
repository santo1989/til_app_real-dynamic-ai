<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Idp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year',
        'skill_area',
        'description',
        'expected_benefits',
        'action_plan',
        'resources_required',
        'progress_till_dec',
        'revised_description',
        'accomplishment',
        'review_date',
        'is_approved',
        'approved_by_id',
        'approved_at',
        'approved_by_role',
        'status',
        'tracking_indicator',
        'action_points_agreed',
        'hr_input',
        'signed_by_employee',
        'employee_signed_by_name',
        'employee_signed_at',
        'employee_signature_path',
        'signed_by_manager',
        'manager_signed_by_name',
        'manager_signed_at',
        'manager_signature_path',
    ];

    protected $casts = [
        'signed_by_employee' => 'boolean',
        'signed_by_manager' => 'boolean',
        'employee_signed_at' => 'datetime',
        'manager_signed_at' => 'datetime',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'attainment' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function milestones()
    {
        return $this->hasMany(IdpMilestone::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function setSkillAreaAttribute($value): void
    {
        $this->attributes['skill_area'] = $this->normalizeText($value);
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = $this->normalizeText($value);
    }

    public function setExpectedBenefitsAttribute($value): void
    {
        $this->attributes['expected_benefits'] = $this->normalizeText($value);
    }

    public function setActionPlanAttribute($value): void
    {
        $this->attributes['action_plan'] = $this->normalizeText($value);
    }

    public function setResourcesRequiredAttribute($value): void
    {
        $this->attributes['resources_required'] = $this->normalizeText($value);
    }

    public function setProgressTillDecAttribute($value): void
    {
        $this->attributes['progress_till_dec'] = $this->normalizeText($value);
    }

    public function setRevisedDescriptionAttribute($value): void
    {
        $this->attributes['revised_description'] = $this->normalizeText($value);
    }

    public function setAccomplishmentAttribute($value): void
    {
        $this->attributes['accomplishment'] = $this->normalizeText($value);
    }

    public function setEmployeeSignedByNameAttribute($value): void
    {
        $this->attributes['employee_signed_by_name'] = $this->normalizeText($value);
    }

    public function setManagerSignedByNameAttribute($value): void
    {
        $this->attributes['manager_signed_by_name'] = $this->normalizeText($value);
    }

    public function getSkillAreaSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['skill_area'] ?? null);
    }

    public function getDescriptionSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['description'] ?? null);
    }

    public function getExpectedBenefitsSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['expected_benefits'] ?? null);
    }

    public function getActionPlanSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['action_plan'] ?? null);
    }

    public function getResourcesRequiredSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['resources_required'] ?? null);
    }

    public function getProgressTillDecSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['progress_till_dec'] ?? null);
    }

    public function getRevisedDescriptionSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['revised_description'] ?? null);
    }

    public function getAccomplishmentSentenceCaseAttribute(): ?string
    {
        return $this->sentenceCase($this->attributes['accomplishment'] ?? null);
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
