<?php

namespace App\Models;

class Contact extends BaseModel
{
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
