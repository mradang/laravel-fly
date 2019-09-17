<?php

namespace mradang\LaravelFly\Services;

use mradang\LaravelFly\Models\RbacNode;

class RbacNodeService {

    public static function all() {
        return RbacNode::orderBy('name')->get();
    }

    public static function allWithRole() {
        return RbacNode::with('roles')->orderBy('name')->get();
    }

    public static function ids() {
        return RbacNode::pluck('id');
    }

    public static function publicNodes() {
        $nodes = [];
        $routes = app()->router->getRoutes();
        foreach ($routes as $key => $value) {
            $middleware = array_get($value, 'action.middleware', []);
            // 不需要授权
            if (!in_array('auth', $middleware)) {
                $nodes[] = $value['uri'];
            }
        }
        return $nodes;
    }

    public static function AuthNodes() {
        $nodes = [];
        $routes = app()->router->getRoutes();
        foreach ($routes as $key => $value) {
            $middleware = array_get($value, 'action.middleware', []);
            // 需要授权
            if (in_array('auth', $middleware)) {
                $nodes[] = $value['uri'];
            }
        }
        return $nodes;
    }

    private static function getRouteDesc() {
        $filename = storage_path('app/route_desc.json');
        $desc = [];
        if (is_file($filename) && is_readable($filename)) {
            $desc = json_decode(file_get_contents($filename), true);
        }
        return $desc;
    }

    private static function setRouteDesc(array $desc) {
        $filename = storage_path('app/route_desc.json');
        $content = json_encode($desc, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
        if ($content && is_writable(dirname($filename))) {
            return file_put_contents($filename, $content);
        }
    }

    public static function makeRouteDescFile() {
        // 读取节点数据
        $nodes = [];
        $routes = app()->router->getRoutes();
        foreach ($routes as $key => $value) {
            if (array_key_exists('middleware', $value['action'])) {
                if (in_array('auth', $value['action']['middleware'])) {
                    $nodes[] = $value['uri'];
                }
            }
        }

        // 读取功能说明文件
        $desc = self::getRouteDesc();

        // 重新生成功能说明文件
        $new = [];
        foreach ($nodes as $node) {
            list(, $module) = explode('/', $node);
            if (!array_key_exists($module, $new)) {
                $new[$module] = [];
            }
            $function = str_after($node, "/$module/");
            $new[$module][$function] = array_get($desc, "$module.$function", '');
        }

        // 排序
        foreach ($new as $key => &$value) {
            ksort($value);
        }
        ksort($new);

        // 写入文件
        return self::setRouteDesc($new);
    }

    public static function refresh() {
        // 读取功能说明文件
        $desc = self::getRouteDesc();

        // 获取需要授权的路由节点，并更新数据库
        $nodes = self::AuthNodes();
        $ids = [];
        foreach ($nodes as $node) {
            list(, $module) = explode('/', $node);
            $function = str_after($node, "/$module/");
            $rbac_node = RbacNode::firstOrNew(['name' => $node]);
            $rbac_node->description = array_get($desc, "$module.$function", '');
            $rbac_node->save();
            $ids[] = $rbac_node->id;
        }

        // 清理无效节点
        RbacNode::whereNotIn('id', $ids)->delete();
        // 清理无效权限
        RbacAccessService::clearInvalidAccess();
    }

}
