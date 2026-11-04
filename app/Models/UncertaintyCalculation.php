<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UncertaintyCalculation extends Model
{
    use HasFactory;

    protected $table = 'uncertainty_calculations';

    protected $fillable = [
        'test_result_id',
        'load_type',
        'u1_repeatability',
        'u2_reference_std',
        'u3_resolution',
        'combined_uncertainty',
        'expanded_uncertainty',
        'confidence_level'
    ];

    protected $casts = [
        'u1_repeatability' => 'decimal:6',
        'u2_reference_std' => 'decimal:6',
        'u3_resolution' => 'decimal:6',
        'combined_uncertainty' => 'decimal:6',
        'expanded_uncertainty' => 'decimal:6',
        'confidence_level' => 'decimal:2'
    ];

    public function testResult()
    {
        return $this->belongsTo(TestResult::class);
    }

    public function getUcFormulaAttribute()
    {
        return "√({$this->u1_repeatability}² + {$this->u2_reference_std}² + {$this->u3_resolution}²)";
    }

    // Get expanded uncertainty with confidence level
    public function getFormattedUncertaintyAttribute()
    {
        return "{$this->expanded_uncertainty}% at {$this->confidence_level}% confidence level";
    }
}
