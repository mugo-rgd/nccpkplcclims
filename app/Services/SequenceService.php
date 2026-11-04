<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    public function getNextNumber(string $sequenceName): string
    {
        return DB::transaction(function () use ($sequenceName) {
            $sequence = Sequence::where('name', $sequenceName)->lockForUpdate()->first();

            if (!$sequence) {
                throw new \Exception("Sequence '{$sequenceName}' not found.");
            }

            $next = $sequence->last_number + 1;
            $sequence->update(['last_number' => $next]);

            return $sequence->prefix . str_pad($next, $sequence->padding, '0', STR_PAD_LEFT);
        });
    }
}
