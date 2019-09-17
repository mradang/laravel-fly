<?php

namespace mradang\LaravelFly\Models;

use Illuminate\Database\Eloquent\Model;

class RbacNode extends Model {

    protected $table = 'rbac_node';

    protected $fillable = ['name', 'description'];

    public $timestamps = false;

    public function roles() {
        return $this->belongsToMany('mradang\LaravelFly\Models\RbacRole', 'rbac_access', 'node_id', 'role_id');
    }

}
