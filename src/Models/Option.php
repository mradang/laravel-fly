<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['option_name', 'option_value'];

    public $timestamps = false;

    public function getOptionValueAttribute($value)
    {
        return $value ? unserialize($value) : null;
    }

    public function setOptionValueAttribute($value)
    {
        $this->attributes['option_value'] = serialize($value);
    }
}
