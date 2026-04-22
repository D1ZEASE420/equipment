<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'borrowed_at',
        'due_date',
        'due_time',
        'returned_at',
        'student_name',
        'student_email',
        'notification_sent',
    ];

    protected $casts = [
        'borrowed_at'       => 'datetime',
        'due_date'          => 'datetime',
        'returned_at'       => 'datetime',
        'notification_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->returned_at) return 'green';

        $due = Carbon::parse($this->due_date);
        $now = Carbon::now();

        if ($now->greaterThan($due)) return 'red';
        if ($now->diffInDays($due) <= 2) return 'yellow';

        return 'green';
    }

    public function isOverdue(): bool
    {
        return !$this->returned_at && Carbon::now()->greaterThan($this->due_date);
    }
}
