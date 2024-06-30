<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'image', 'nav_menu_image1', 'nav_menu_image2'];

    public function category() {
        return $this->belongsTo(Category::class);
    }    

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function boot() {
        parent::boot();
        static::saving(function($subcategory) {
            $subcategory->slug = static::generateUniqueSlug($subcategory->name);
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
