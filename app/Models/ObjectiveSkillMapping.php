<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectiveSkillMapping extends Model
{
    protected $fillable = [
        'objective_master_id',
        'skill_area_id',
    ];

    public function objectiveMaster()
    {
        return $this->belongsTo(IndividualObjectiveMaster::class, 'objective_master_id');
    }

    public function skillArea()
    {
        return $this->belongsTo(IdpDevelopmentObjective::class, 'skill_area_id');
    }
}