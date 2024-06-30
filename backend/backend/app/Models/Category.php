<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Category
 *
 * @property int $id
 * @property int $category_type_id
 * @property string $name
 * @property string $image
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \Illuminate\Database\Eloquent\Collection|Product[] $products
 */
class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'category_type_id', 'image', 'nav_menu_image1', 'nav_menu_image2'];

    /**
     * Get the products associated with the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function categoryType()
    {
        return $this->belongsTo(CategoryType::class);
    }

    public function subcategories() {
        return $this->hasMany(Subcategory::class);
    }    

    public static function boot() {
        parent::boot();
        static::saving(function($category) {
            $category->slug = static::generateUniqueSlug($category->name);
        });
    }

    private static function generateUniqueSlug($name) {
        $slug = Str::slug($name);
        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }    
}
