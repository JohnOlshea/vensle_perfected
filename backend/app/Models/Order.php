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
        'order_number',
        'payment_method',
	    'proof_type',
	    'paid',
        'status',
        'total_price',
	    'shipping_address_id',
	    'rejected_driver_ids',
    ];

    protected $casts = [
        'rejected_driver_ids' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'rejected_driver_ids',
        'stripe_session_id',
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

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }    

    public function orderTrails()
    {
        return $this->hasMany(OrderTrail::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the driver activities associated with the order.
     */
    public function driverActivities()
    {
        return $this->hasMany(OrderDriverActivity::class);
    }


    /**
     * Scope a query to count total orders excluding 'Inactive' status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return int
     */
    public function scopeActiveOrders($query)
    {
        return $query->where('status', '!=', 'Inactive')->count();
    }    

    /**
     * Define a relationship to products based on product_ids array.
     */
    //public function products()
    //{
        //return Product::whereIn('id', json_decode($this->product_ids))->get();
    //}
}
