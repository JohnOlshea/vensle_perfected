<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    //use SoftDeletes;

    protected $fillable = ['sender_id', 'receiver_id', 'product_id', 'content'];
    //protected $fillable = ['sender_id', 'receiver_id', 'product_id', 'content', 'read'];

    //protected $dates = ['deleted_at'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
        //return $this->hasMany(Message::class, 'parent_id')->withTrashed();
    }

    public function latestReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
