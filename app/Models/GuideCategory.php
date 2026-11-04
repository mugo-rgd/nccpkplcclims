<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'icon', 'order'
    ];

    public function guides()
    {
        return $this->hasMany(UserGuide::class, 'category', 'slug')->orderBy('order');
    }
}
