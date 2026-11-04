<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGuideFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_id', 'user_id', 'helpful', 'comment'
    ];

    protected $casts = [
        'helpful' => 'boolean',
    ];

    public function guide()
    {
        return $this->belongsTo(UserGuide::class, 'guide_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
