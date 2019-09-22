<?php

namespace mradang\LaravelFly\Services;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileService {

    // 确保目录存在
    public static function ensureFolderExists(string $path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    // 文件是否图片
    public static function isImage(string $file_storage) {
        $file_full = storage_path($file_storage);
        return @is_array(getimagesize($file_full));
    }

    // 获取指定目录名的当日存储路径
    public static function getStorageFolder(string $folder) {
        $folder_storage = $folder.'/'.date('Y/m/d/');
        self::ensureFolderExists(
            storage_path($folder_storage)
        );
        return $folder_storage;
    }

    // 上传文件
    public static function uploadFile(UploadedFile $file, string $folder = 'fileService') {
        $folder_storage = self::getStorageFolder($folder);
        $file_name = md5(md5_file($file->path()).time()).'.'.$file->guessClientExtension();
        $file->move(storage_path($folder_storage), $file_name);
        return is_file(storage_path($folder_storage.$file_name))
            ? $folder_storage.$file_name
            : false;
    }

    // 上传 Url
    public static function uploadUrl(string $url, string $folder = 'fileService') {
        $folder_storage = self::getStorageFolder($folder);
        $parsed_url = parse_url($url);
        if (!is_array($parsed_url)) {
            return false;
        }
        $file_ext = pathinfo($parsed_url['path'], PATHINFO_EXTENSION);
        $file_ext = ($file_ext ? '.' : '').$file_ext;
        $file_name = md5($url.time()).$file_ext;
        $file_full = storage_path($folder_storage.$file_name);
        $ret = file_put_contents($file_full, file_get_contents($url));
        return $ret
            ? $folder_storage.$file_name
            : false;
    }

    // 生成图片缩略图
    public static function makeThumb(string $file_storage, int $width, int $height) {
        if (!self::isImage($file_storage)) {
            return false;
        }
        if ($width < 0 || $height < 0) {
            return false;
        }
        $thumb_storage = "fileService.thumbs/{$file_storage}_{$width}x{$height}.jpg";
        $thumb_full = storage_path($thumb_storage);
        if (!is_file($thumb_full)) {
            self::ensureFolderExists(dirname($thumb_full));
            $image = new \Gumlet\ImageResize(storage_path($file_storage));
            $image->resizeToBestFit($width, $height);
            $image->save($thumb_full, \IMAGETYPE_JPEG);
        }
        return $thumb_storage;
    }

    // 缩略图是否存在
    public static function thumbExists(string $file_storage, int $width, int $height) {
        $thumb_storage = "fileService.thumbs/{$file_storage}_{$width}x{$height}.jpg";
        $thumb_full = storage_path($thumb_storage);
        return is_file($thumb_full);
    }

    // 下载文件
    public static function download(string $file_storage, string $name = '') {
        $file_full = storage_path($file_storage);
        if (!is_file($file_full)) {
            return response('Not Found', 404);
        }
        $file_ext = pathinfo($file_full, PATHINFO_EXTENSION);
        $file_ext = ($file_ext ? '.' : '').$file_ext;
        $file_name = $name ?: basename($file_full, $file_ext);
        $headers = [
            'Content-Type' => mime_content_type($file_full),
            'Cache-Control' => 'no-cache',
        ];
        return response()->download(
            $file_full,
            $file_name.$file_ext,
            $headers
        );
    }

    // 输出文件
    public static function response(string $file_storage, string $name = '') {
        $file_full = storage_path($file_storage);
        if (!is_file($file_full)) {
            return response('Not Found', 404);
        }
        $file_ext = pathinfo($file_full, PATHINFO_EXTENSION);
        $file_ext = ($file_ext ? '.' : '').$file_ext;
        $file_name = $name ?: basename($file_full, $file_ext);
        $headers = [
            'Content-Type' => mime_content_type($file_full),
            'Cache-Control' => 'no-cache',
        ];
        $response = new BinaryFileResponse($file_full, 200, $headers);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file_name.$file_ext
        );
        return $response;
    }

    // 显示图片
    public static function showImage(string $file_storage, int $width = 0, int $height = 0) {
        if (!self::isImage($file_storage)) {
            return response('非图片', 400);
        }

        $file_full = storage_path($file_storage);
        if ($width > 0 && $height > 0) {
            $file_full = self::makeThumb($file_storage, $width, $height);
            if (empty($file_full)) {
                return response('生成缩略图失败', 400);
            }
            $file_full = storage_path($file_full);
        }

        $headers = [
            'Content-Type' => mime_content_type($file_full),
            'Cache-Control' => 'no-cache',
        ];
        return new BinaryFileResponse($file_full, 200, $headers);
    }

    // 删除文件
    public static function deleteFile($file_storage) {
        $file_full = storage_path($file_storage);
        if (is_file($file_full)) {
            if (self::isImage($file_storage)) {
                self::clearThumbs($file_storage);
            }
            @unlink($file_full);
        }
        return !is_file($file_full);
    }

    private static function clearThumbs(string $file_storage) {
        $thumb_full = storage_path("fileService.thumbs/{$file_storage}");
        $pattern = $thumb_full.'_*.jpg';
        if (glob($pattern)) {
            array_map('unlink', glob($pattern));
        }
    }

}
