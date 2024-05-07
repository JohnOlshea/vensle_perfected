<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;

/**
 * Class Product
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property string $condition
 * @property float $price
 * @property string $address
 * @property string $phone_number
 * @property string $description
 * @property string $type
 * @property string $status
 * @property string $ratings
 * @property string $quantity
 * @property string $sold
 * @property string $views
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Database\Eloquent\Collection|Specification[] $specifications
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    'name', 'category_id', 'subcategory_id', 'condition', 'price', 'discount', 'address', 'phone_number', 'description', 'type', 'key_specifications', 'status', 'ratings', 'product_quantity', 'sold', 'views', 'latitude', 'longitude', 'display_image_id', 'city', 'currency', 'country', 'user_id', 'deleted'
    ];

    /**
     * Get the user who owns the product.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the specifications associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specifications()
    {
        return $this->belongsToMany(Specification::class, 'product_specification');
    }

    /**
     * Get the orders associated with the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function order_items()
    {
        return $this->belongsToMany(OrderItem::class)->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function displayImage()
    {
        return $this->belongsTo(Image::class, 'display_image_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the total number of feedback for this product.
     *
     * @return int
     */
    public function getTotalFeedbackAttribute()
    {
        return $this->feedback()->count();
    }

    /**
     * Append the total feedback count to the model's array form.
     *
     * @var array
     */
    protected $appends = ['total_feedback'];
}
