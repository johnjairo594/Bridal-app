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
        'entry_date'
    ];

    protected function total(): Attribute
    {
        return Attribute::make(
            get: function () {
                $productsTotal = $this->workOrderProducts->sum(function ($item) {
                    return $item->quantity * $item->product->price;
                });

                $servicesTotal = $this->workOrderServices->sum(function ($item) {
                    return $item->service->price;
                });

                return $productsTotal + $servicesTotal;
            }
        );
    }

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

    public function workOrdersProducts()
    {
        return $this->hasMany(WorkOrderProduct::class);
    }

    public function workOrdersServices()
    {
        return $this->hasMany(WorkOrderService::class);
    }
}