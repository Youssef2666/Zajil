<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@gmail.com') && $this->hasVerifiedEmail();
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->wallet()->create();
        });
    }

    public function favouriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favourite_product')->withTimestamps();
    }
    public function favouriteStores()
    {
        return $this->belongsToMany(Store::class, 'favourite_store');
    }

    public function ratedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_rating')
            ->withPivot('rating')
            ->withTimestamps();
    }

    public function ratedStores()
    {
        return $this->belongsToMany(Store::class, 'rate_store')
            ->withPivot('rating')
            ->withTimestamps();
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function phones()
    {
        return $this->hasMany(UserPhone::class);
    }
}
