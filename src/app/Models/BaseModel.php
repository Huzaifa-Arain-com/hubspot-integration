<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $guarded = [];

    public function getSynchedAtAttribute($value)
    {
        return $value != null ? Carbon::parse($value)->toDayDateTimeString() : null;
    }

    public function getFailedAtAttribute($value)
    {
        return $value != null ? Carbon::parse($value)->toDayDateTimeString() : null;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDayDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDayDateTimeString();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
