<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'user_id', 'note'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
