<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    use HasFactory;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
                'name', 'category_id', 'price', 'address', 'phone_number', 'description', 'type', 'ratings', 'sold', 'views',
        ];

        /**
         * Get the categories associated with the product request.
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
         */
        public function category()
        {
                return $this->belongsTo(Category::class);
        }

        /**
         * Get the specifications associated with the product request.
         *
         * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
         */
        public function specifications()
        {
                return $this->belongsToMany(Specification::class, 'product_request_specifications');
        }

}
