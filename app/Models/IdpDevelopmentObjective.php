<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class IdpDevelopmentObjective extends Model
{
    protected $fillable = [
        'skill_area',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setSkillAreaAttribute($value): void
    {
        $trimmed = trim((string) $value);
        $this->attributes['skill_area'] = $trimmed === '' ? null : Str::upper($trimmed);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get individual objectives linked to this skill area.
     */
    public function individualObjectiveMasters(): BelongsToMany
    {
        return $this->belongsToMany(
            IndividualObjectiveMaster::class,
            'objective_skill_mapping',
            'skill_area_id',
            'objective_master_id'
        );
    }

    /**
     * Get departmental objectives linked to this skill area.
     */
    public function departmentalObjectiveMasters(): BelongsToMany
    {
        return $this->belongsToMany(
            DepartmentalObjectiveMaster::class,
            'objective_skill_mapping',
            'skill_area_id',
            'objective_master_id'
        );
    }
}
