<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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