<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 *
 * Stores application settings using a flexible `category`, `key`, `value` structure.
 * Automatically encodes/decodes the `value` attribute to/from JSON if needed.
 *
 * @property string $category
 * @property string $key
 * @property mixed  $value
 */
class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category',
        'key',
        'value'
    ];

    /**
     * Accessor for the `value` attribute.
     * If the stored value is JSON, it will be automatically decoded to an array.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        if ($this->isJson($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Mutator for the `value` attribute.
     * Automatically encodes arrays or objects into JSON before saving.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) || is_object($value)
            ? json_encode($value)
            : $value;
    }

    /**
     * Check whether the given string is a valid JSON.
     *
     * @param  mixed  $string
     * @return bool
     */
    private function isJson($string): bool
    {
        if (!is_string($string) || $string === '') return false;
    
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
