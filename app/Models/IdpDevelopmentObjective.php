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
}
