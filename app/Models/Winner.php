<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'participant_id',
        'rank',
        'total_score',
        'week_number',
        'week_start_date',
        'week_end_date',
        'notified',
        'notified_at',
        'prize',
    ];

    protected $casts = [
        'notified' => 'boolean',
        'notified_at' => 'datetime',
        'week_start_date' => 'date',
        'week_end_date' => 'date',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    // Marquer comme notifié
    public function markAsNotified(): void
    {
        $this->update([
            'notified' => true,
            'notified_at' => now(),
        ]);
    }
}
