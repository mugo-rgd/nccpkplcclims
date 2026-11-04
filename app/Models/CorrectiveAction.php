<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveAction extends Model
{
    use SoftDeletes;

    protected $table = 'corrective_actions';

    protected $fillable = [
        'car_id',
        'objective_id',  // Changed from quality_objective_id to objective_id
        'issue_description',
        'root_cause',
        'action_plan',
        'action_owner_id',
        'deadline',
        'status',
        'opened_date',
        'closed_date',
        'verification_notes'
    ];

    protected $casts = [
        'deadline' => 'date',
        'opened_date' => 'date',
        'closed_date' => 'date'
    ];

    // Relationships
   public function objective()
{
    return $this->belongsTo(QualityObjective::class, 'objective_id', 'id');
}

    public function actionOwner()
    {
        return $this->belongsTo(User::class, 'action_owner_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['Open', 'In Progress']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->whereIn('status', ['Open', 'In Progress']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }
}
