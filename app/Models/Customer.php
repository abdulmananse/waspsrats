<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    
    protected $fillable = [
        'first_name',
        'last_name',
        'account_no',
        'email',
        'company_name',
        'phone',
        'is_active'
    ];

    /**
     * @return void
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
    * Get the route key for the model.
    *
    * @return string
    */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * boot
     */
    protected static function boot ()
    {
    	parent::boot();
        static::creating(function ($model) {
            $model->uuid = getUuid();
        });
    }
}
