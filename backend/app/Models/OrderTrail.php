<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTrail extends Model
{
    use HasFactory;

    protected $table = 'order_trails';

    protected $fillable = ['order_id', 'name'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }    
}
