<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldGroup extends Model {

    protected $fillable = [
        'model',
        'name',
        'sort',
    ];

    public function fields() {
        return $this->hasMany('mradang\LaravelFly\Models\CustomField', 'group_id');
    }

}
