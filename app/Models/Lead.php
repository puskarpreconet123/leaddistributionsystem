<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['assigned_to', 'status', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
