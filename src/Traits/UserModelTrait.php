<?php

namespace mradang\LaravelFly\Traits;

use Firebase\JWT\JWT;

use mradang\LaravelFly\Services\UserService;
use mradang\LaravelFly\Services\RbacNodeService;

trait UserModelTrait {

    public function rbacRoles() {
        return $this->belongsToMany('mradang\LaravelFly\Models\RbacRole', 'rbac_role_user', 'user_id', 'role_id');
    }

    abstract protected function getIsAdminAttribute();

    // access
    public function getAccessAttribute() {
        $nodes = RbacNodeService::publicNodes();

        if ($this->isAdmin) {
            $nodes = array_merge($nodes, RbacNodeService::AuthNodes());
        } else {
            foreach ($this->rbacRoles as $role) {
                $nodes = array_merge($nodes, $role->nodes->pluck('name')->toArray());
            }
        }

        return array_values(array_unique($nodes));
    }

    public function rbacResetSecret() {
        $this->secret = str_random(8);
        return $this->save();
    }

    public function rbacMakeToken(array $fields = ['id', 'name']) {
        if (empty($this->secret)) {
            $this->rbacResetSecret();
        }
        $payload = array_only($this->toArray(), $fields);
        $payload['exp'] = time() + config('fly.ttl');
        return JWT::encode($payload, $this->secret);
    }

    public function rbacSyncRoles(array $roles) {
        $this->load('rbacRoles');
        $old = $this->rbacRoles->pluck('name')->toArray();
        $this->rbacRoles()->sync($roles);
        $new = $this->refresh()->rbacRoles->pluck('name')->toArray();
        $remove = array_diff($old, $new);
        $add = array_diff($new, $old);
        if ($old || $new) {
            $this->rbacResetSecret();
            L(sprintf(
                '修改用户「%s」角色%s%s',
                $this->name,
                $remove ? sprintf('，移除角色「%s」', implode(',', $remove)) : '',
                $add ? sprintf('，新增角色「%s」', implode(',', $add)) : ''
            ));
        }
    }

    public function rbacDeleteUser() {
        $this->rbacRoles()->detach();
        $this->rbacResetSecret();
    }

}
