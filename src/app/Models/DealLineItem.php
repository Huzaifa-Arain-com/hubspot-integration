<?php

namespace App\Models;

class DealLineItem extends BaseModel
{
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
