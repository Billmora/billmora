<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageField extends Model
{
    protected $fillable = [
        'package_id',
        'type',
        'name',
        'label',
        'helper',
        'default',
        'required',
        'visible_on_order',
        'visible_on_invoice',
        'options',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'visible_on_order' => 'boolean',
            'visible_on_invoice' => 'boolean',
            'options' => 'array',
        ];
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}