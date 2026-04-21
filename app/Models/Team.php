<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = ['department_id', 'name', 'team_lead_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department the team belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the team lead.
     */
    public function teamLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    /**
     * Get all users in this team.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }

    /**
     * Get all members of this team (including team lead).
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }

    /**
     * Get individual objective masters for this team.
     */
    public function individualObjectiveMasters(): HasMany
    {
        return $this->hasMany(IndividualObjectiveMaster::class, 'team_id');
    }

    /**
     * Get departmental objective masters for this team.
     */
    public function departmentalObjectiveMasters(): HasMany
    {
        return $this->hasMany(DepartmentalObjectiveMaster::class, 'team_id');
    }
}