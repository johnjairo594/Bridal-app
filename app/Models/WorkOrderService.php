<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'service_id',
        'price'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}