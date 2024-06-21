<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriverActivity extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_driver_activity';    
    protected $fillable = ['order_id', 'driver_id', 'reason', 'action', 'rejected_driver_ids'];

    protected $casts = [
        'rejected_driver_ids' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
