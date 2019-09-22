<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {

    protected $fillable = [
        'attachmentable_type',
        'attachmentable_id',
        'file_name',
        'file_size',
        'sort',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    protected $hidden = [
        'attachmentable_type',
        'attachmentable_id',
    ];

    public function attachmentable() {
        return $this->morphTo();
    }

}
