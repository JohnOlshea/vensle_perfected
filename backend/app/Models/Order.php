<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
	'driver_id',
        'stripe_session_id',
        'payment_method',
	'paid',
        'status',
        'total_price',
    ];

    /**
     * Get the user who placed the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderTrails()
    {
        return $this->hasMany(OrderTrail::class);
    }    

    /**
     * Define a relationship to products based on product_ids array.
     */
    //public function products()
    //{
        //return Product::whereIn('id', json_decode($this->product_ids))->get();
    //}
}
