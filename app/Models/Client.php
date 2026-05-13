<?php

namespace App\Models;

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
}