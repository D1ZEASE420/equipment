<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'device_id', 'borrowed_at', 'due_date', 'returned_at'];
    protected $casts = ['borrowed_at' => 'datetime', 'due_date' => 'datetime', 'returned_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function device() { return $this->belongsTo(Device::class); }

    public function getStatusColorAttribute(): string
    {
        if ($this->returned_at) return 'returned';
        $due = Carbon::parse($this->due_date)->startOfDay();
        if ($due->isToday()) return 'yellow';
        if ($due->isPast()) return 'red';
        return 'green';
    }

    public function isOverdue(): bool { return !$this->returned_at && Carbon::parse($this->due_date)->isPast(); }
    public function isReturned(): bool { return !is_null($this->returned_at); }
}
