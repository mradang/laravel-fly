<?php

namespace mradang\LaravelFly\Services;

use mradang\LaravelFly\Models\Log;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LogService
{
    public static function create($msg, $username = null)
    {
        $ip = app()->request->ip();

        if (empty($username)) {
            $username = optional(Auth::user())->name ?: 'sys';
        }

        $log = new Log([
            'username' => $username,
            'ip' => $ip ?: '',
            'log_msg' => $msg,
        ]);
        $log->save();

        return $log;
    }

    public static function lists($params, $page, $pagesize)
    {
        $query = Log::where(function ($query) use ($params) {
            if (!empty($params['username'])) {
                $query->where('username', $params['username']);
            }
            if (!empty($params['log_msg'])) {
                $query->where('log_msg', 'like', "%{$params['log_msg']}%");
            }
            if (!empty($params['ip'])) {
                $query->where('ip', $params['ip']);
            }
        });
        $ret = [
            'count' => $query->count(),
            'data' => $query->orderBy('id', 'desc')
                ->forPage($page, $pagesize)
                ->get(),
        ];
        return $ret;
    }

    public static function migrateToFile()
    {
        $end = Carbon::now()->subMonths(3)->firstOfMonth()->format('Y-m-d');
        Log::where('created_at', '<', $end)->orderBy('created_at')->chunk(100, function ($logs) {
            foreach ($logs as $log) {
                $dt = Carbon::parse($log->created_at);
                $folder = storage_path('logs/app' . $dt->format('Y'));
                if (!is_dir($folder)) {
                    mkdir($folder, 0755, true);
                }
                $filename = $folder . '/' . $dt->format('m') . '.log';
                $content = sprintf(
                    "%s\t%s\t%s\t%s\n",
                    $log->created_at,
                    $log->ip,
                    $log->username,
                    $log->log_msg
                );
                file_put_contents($filename, $content, FILE_APPEND);
            }
        });

        Log::where('created_at', '<', $end)->delete();
        app('db')->statement('optimize table logs');
        self::create(
            sprintf('转存 %s 之前的日志到磁盘', $end),
            'sys'
        );
    }
}
