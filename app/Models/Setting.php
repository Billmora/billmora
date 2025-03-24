<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public function getValueAttribute($value)
    {
        return json_decode($value, true) ?? $value;
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }
}
