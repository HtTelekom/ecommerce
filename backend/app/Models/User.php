<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;


class User extends Eloquent implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'address',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'address' => 'array',
    ];

    /**
     * Default values for attributes
     *
     * @var array
     */
    protected $attributes = [
        'role' => 'customer',
        'is_active' => true,
    ];

    /**
     * User roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_VENDOR = 'vendor';

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is customer
     *
     * @return bool
     */
    public function isCustomer()
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user is vendor
     *
     * @return bool
     */
    public function isVendor()
    {
        return $this->role === self::ROLE_VENDOR;
    }

    /**
     * Get user's full address
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        if (!$this->address) {
            return null;
        }

        return implode(', ', array_filter([
            $this->address['street'] ?? null,
            $this->address['city'] ?? null,
            $this->address['state'] ?? null,
            $this->address['postal_code'] ?? null,
            $this->address['country'] ?? null,
        ]));
    }

    /**
     * Get user's orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's cart items
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get user's wishlist items
     */
    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Get user's reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scope: Active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope: Customer users
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
