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

    private function effectiveDueDateTime(): Carbon
    {
        $due = Carbon::parse($this->due_date)->startOfDay();
        if ($this->due_time) {
            [$h, $m] = explode(':', $this->due_time);
            $due->setHour((int)$h)->setMinute((int)$m)->setSecond(0);
        } else {
            $due->endOfDay();
        }
        return $due;
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->returned_at) return 'green';

        $due = $this->effectiveDueDateTime();
        $now = Carbon::now();

        if ($now->greaterThan($due)) return 'red';
        if ($now->diffInDays($due) <= 2) return 'yellow';

        return 'green';
    }

    public function isOverdue(): bool
    {
        return !$this->returned_at && Carbon::now()->greaterThan($this->effectiveDueDateTime());
    }
}
