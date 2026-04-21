<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DesignationMaster extends Model
{
    protected $fillable = [
        'title',
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
}
