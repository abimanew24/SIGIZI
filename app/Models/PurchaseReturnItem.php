<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $guarded = [];

    public function kitchenItem()
    {
        return $this->belongsTo(KitchenItem::class);
    }
}
