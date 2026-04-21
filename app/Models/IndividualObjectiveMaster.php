<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class IndividualObjectiveMaster extends Model
{
    protected $fillable = [
        'title',
        'department_id',
        'team_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = Str::upper(trim((string) $value));
    }

    public function getTitleSentenceCaseAttribute(): string
    {
        return Str::ucfirst(Str::lower((string) ($this->attributes['title'] ?? '')));
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get skill areas linked to this objective.
     */
    public function skillAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            IdpDevelopmentObjective::class,
            'objective_skill_mapping',
            'objective_master_id',
            'skill_area_id'
        );
    }
}
