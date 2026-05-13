<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'identification',
        'phone',
        'address',
        'birth_date',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }
}