<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price'
    ];

    public function workOrderServices()
    {
        return $this->hasMany(WorkOrderService::class);
    }
}