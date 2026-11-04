<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGuide extends Model
{
    use HasFactory;

    protected $table = 'user_guide';

    protected $fillable = [
        'title', 'slug', 'category', 'order', 'content',
        'content_html', 'icon', 'is_published', 'metadata', 'created_by', 'views'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'metadata' => 'array',
        'views' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function feedback()
    {
        return $this->hasMany(UserGuideFeedback::class, 'guide_id');
    }

    public function getHelpfulPercentageAttribute()
    {
        $total = $this->feedback()->count();
        if ($total === 0) return 0;
        $helpful = $this->feedback()->where('helpful', true)->count();
        return round(($helpful / $total) * 100);
    }
}
