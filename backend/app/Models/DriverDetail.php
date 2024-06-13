<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    use HasFactory;

    protected $fillable = [
	'user_id',
        'license_identification_number',
        'vehicle_registration_number',
        'vehicle_make_model',
        'vehicle_color',
        'license_plate_number',
	'license_image_path',
	'vehicle_photo_path',
	'ratings',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }    
}
