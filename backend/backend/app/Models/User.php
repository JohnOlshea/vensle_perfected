<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
//use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone_number',
        'profile_picture',
        'provider_id',
	'notification_preferences',
	'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
	'notification_preferences' => 'array',
    ];

    //TODO: Hide user's coordinate

    /**
     * Get the products associated with the user.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Define the relationship between User and SavedProduct.
     */
    public function savedProducts()
    {
	return $this->hasMany(SavedProduct::class);
    }    
    
    public function userAlerts()
    {
        return $this->hasMany(UserAlert::class);
    }

    public function driverDetail()
    {
        return $this->hasOne(DriverDetail::class);
    }    

    public function businessDetails()
    {
        return $this->hasOne(BusinessDetails::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the orders associated with the driver.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    /**
     * Get the driver activities associated with the driver.
     */
    public function driverActivities()
    {
        return $this->hasMany(OrderDriverActivity::class, 'driver_id');
    }    

    public function updateNotificationPreference($type, $value)
    {
        $notificationPreferences = $this->notification_preferences ?? [];
        $notificationPreferences[$type] = $value;
        $this->notification_preferences = $notificationPreferences;
        $this->save();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }    

    public function wantsNotification($type)
    {
        return isset($this->notification_preferences[$type]) ? $this->notification_preferences[$type] : false;
    }    

	public function createAlertIfEnabled($title, $message, $preferenceName, $url = null)
	{
	    if ($this->wantsNotification($preferenceName)) {
		$data = [
		    'title' => $title,
		    'message' => $message,
		];

		if ($url !== null && $url !== '') {
		    $data['url'] = $url;
		}

		$this->userAlerts()->create($data);
	    }
	}

    public function messages()
    {
      return $this->hasMany(Message::class); // User can have many messages
    }

    public function location()
    {
        return $this->hasOne(DriverLocation::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    /*public function createAlertIfEnabled($title, $message, $preferenceName)
    {
        if ($this->wantsNotification($preferenceName)) {
            $this->userAlerts()->create([
                'title' => $title,
                'message' => $message,
            ]);
        }
    }*/   

    /*
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
     */

    /*
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }
     */
}
