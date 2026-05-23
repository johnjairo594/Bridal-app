<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'vehicle_id',
        'mechanic_id',
        'diagnosis',
        'repair_notes',
        'status',
        'total_price',
        'entry_date',
        'finish_date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(User::class);
    }

    public function workOrderProducts()
    {
        return $this->hasMany(WorkOrderProduct::class);
    }

    public function workOrderServices()
    {
        return $this->hasMany(WorkOrderService::class);
    }
}