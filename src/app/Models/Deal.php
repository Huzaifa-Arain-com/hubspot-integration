<?php

namespace App\Models;

class Deal extends BaseModel
{
    public function lineItems()
    {
        return $this->hasMany(DealLineItem::class);
    }

    public function associations()
    {
        return $this->hasMany(DealAssociation::class);
    }
}
