<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'area',
        'description',
        'state',
        'city',
        'contact',
        'salary',
    ];

    protected $casts = [
        'salary' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
}
