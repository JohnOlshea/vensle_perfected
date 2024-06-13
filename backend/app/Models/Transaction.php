<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_no',
        'transaction_description',
        'amount',
        'status',
    ];

    public static function generateTransactionNo()
    {
        return 'TXN-' . strtoupper(uniqid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
