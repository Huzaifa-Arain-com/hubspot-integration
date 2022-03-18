<?php

namespace App\Models;

class DealAssociation extends BaseModel
{
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function associateable()
    {
        return $this->morphTo();
    }
}
