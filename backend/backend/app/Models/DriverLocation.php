<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = ['user_id', 'latitude', 'longitude', 'is_online', 'status'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }    
}
