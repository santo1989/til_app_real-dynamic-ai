<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentalObjectiveAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'department_id',
        'team_id',
        'objective_master_id',
        'timeline',
        'weightage',
        'certifying_authority_role',
        'certifying_authority_user_id',
        'created_by',
    ];

    /**
     * @return BelongsTo
     */
    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(DepartmentalObjectiveMaster::class, 'objective_master_id');
    }

    /**
     * @return BelongsTo
     */
    public function certifyingAuthorityUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certifying_authority_user_id');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
