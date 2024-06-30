<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAlert extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'message', 'url', 'read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
