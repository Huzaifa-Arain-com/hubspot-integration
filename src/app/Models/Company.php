<?php

namespace App\Models;

class Company extends BaseModel
{
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
