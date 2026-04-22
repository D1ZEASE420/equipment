<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['name', 'serial_number', 'barcode', 'status', 'description', 'category'];

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowing()
    {
        return $this->hasOne(Borrowing::class)->whereNull('returned_at')->latest();
    }

    public function isBorrowed(): bool
    {
        return $this->status === 'borrowed';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
