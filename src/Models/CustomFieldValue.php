<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model {

    protected $fillable = [
        'valuetable_type',
        'valuetable_id',
        'data',
    ];

    protected $hidden = [
        'valuetable_type',
        'valuetable_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function fieldvaluetable() {
        return $this->morphTo();
    }

}
