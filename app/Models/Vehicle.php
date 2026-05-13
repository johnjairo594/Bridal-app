<?php

namespace App\Models;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'brand',
        'model',
        'year',
        'plate',
        'mileage',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}