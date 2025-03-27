<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['category', 'key', 'value'];

    public function getValueAttribute($value)
    {
        if ($this->isJson($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) || is_object($value)
            ? json_encode($value)
            : $value;
    }

    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
