<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'company_name',
        'street_address_1',
        'street_address_2',
        'city',
        'country',
        'state',
        'postcode',
    ];

    /**
     * Relation to user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
