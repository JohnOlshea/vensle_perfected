<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPasswordReset extends Model
{
    use HasFactory;

    protected $table = 'custom_password_resets';

    protected $fillable = ['email', 'token', 'created_at'];

    public $timestamps = false;    
}
