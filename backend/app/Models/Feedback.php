<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id', 'product_id', 'rating', 'parent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function replies()
    {
        return $this->hasMany(Feedback::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Feedback::class, 'parent_id');
    }
}
