<?php

namespace mradang\LaravelFly\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command {

    protected $signature = 'fly:publish {--force : Overwrite any existing files}';

    protected $description = 'Publish LaravelFly script to project dir (--force)';

    public function handle() {
        $publishes_path = realpath(__DIR__.'/../../publishes');
        $this->publishes([
            $publishes_path.'/shell' => base_path(),
        ]);
    }

    private function publishes(array $paths) {
        foreach ($paths as $key => $value) {
            // 源必须存在，可以是目录或文件
            $from = realpath($key);
            if (!$from) {
                continue;
            }
            // 目标必须是目录，不存在时自动创建
            if (!is_dir($value) && !$this->ensureDirectoryExists($value)) {
                continue;
            }
            $to = realpath($value);

            // 发布资源
            if (is_file($from)) {
                $this->publishFile($from, $to);
            } elseif (is_dir($from)) {
                $this->publishDirectory($from, $to);
            }
        }
    }

    // 发布文件到目录
    private function publishFile($from, $to) {
        info(__FUNCTION__, [$from, $to]);
        $to = $to.'/'.basename($from);
        if (!file_exists($to) || $this->option('force')) {
            return @copy($from, $to);
        }
        return false;
    }

    private function ensureDirectoryExists($directory) {
        if (!is_dir($directory)) {
            return @mkdir($directory, 0777, true);
        }
        return true;
    }

    private function publishDirectory($srcDir, $dstDir) {
        $dir = opendir($srcDir);
        if (!$dir) {
            return false;
        }
        if (!is_dir($dstDir)) {
            if (!mkdir($dstDir, 0777, true)) {
                return false;
            }
        }
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($srcDir.'/'.$file)) {
                    if (!$this->publishDirectory($srcDir.'/'.$file, $dstDir.'/'.$file)) {
                        return false;
                    }
                } else {
                    $this->publishFile($srcDir.'/'.$file, $dstDir);
                }
            }
        }
        closedir($dir);
        return true;
    }

}