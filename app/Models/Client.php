<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'person_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}