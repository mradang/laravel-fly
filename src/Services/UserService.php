<?php

namespace mradang\LaravelFly\Services;

use Firebase\JWT\JWT;

class UserService {

    public static function checkToken() {
        // 获取请求中的令牌
        $token = self::getTokenForRequest();
        if (empty($token)) {
            return false;
        }

        // 获取荷载中的用户id
        $tks = explode('.', $token);
        if (count($tks) !== 3) {
            return false;
        }
        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        if (!$payload || !$payload->id) {
            return false;
        }

        // 读取用户
        $user = call_user_func(
            [config('fly.user_model'), 'find'],
            $payload->id
        );
        if (empty($user)) {
            return false;
        }

        // 校验令牌
        try {
            $payload = JWT::decode($token, $user->secret, array('HS256'));
            if ($payload) {
                return $user;
            }
        } catch (\Exception $e) {
            info('JWTException: '.$e->getMessage());
        }
        return false;
    }

    private static function getTokenForRequest() {
        $request = app()->request;
        $token = $request->input('api_token', $request->bearerToken());
        return $token;
    }

}
