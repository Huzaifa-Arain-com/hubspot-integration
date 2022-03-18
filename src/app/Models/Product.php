<?php

namespace App\Models;

class Product extends BaseModel
{
    public function items()
    {
        return $this->hasMany(DealLineItem::class);
    }
}
