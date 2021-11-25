<?php

namespace app\common\lib;

use think\facade\Config;
use think\facade\Filesystem;

class Util {

    /**
     * API 输出格式
     * @param $status
     * @param string $message
     * @param array $data
     * @return false|string
     */
    public static function show($status, $message = '', $data = []) {
        $result = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ];

        return json_encode($result);
    }

    /**
     * 上传图片
     * @param string $fileName
     * @param string $savePath
     * @param bool $fileas
     * @return false|string
     */
    public static function upload($fileName = 'file', $savePath = 'images', $fileas = false) {
        $file = request()->file($fileName);
        try {
            $maxSize = Config::get('filesystem.image_max_size');
            if ($file->getSize() > $maxSize) {
                return Util::show(0, '文件过大,请选择' . floor($maxSize / 1024 / 1024) . 'M以内的文件');
            }
            $rule = Config::get('filesystem.image_rule');
            validate([$fileName => $rule])->check([$fileName => $file]);
            $saveName = $fileas ? Filesystem::disk('public')->putFile($savePath, $file) : Filesystem::disk('public')->putFileAs($savePath . '/' . date('Ymd'), $file, $file->getOriginalName());
            return Util::show(1, '上传成功', ['path' => Filesystem::getDiskConfig('public', 'url') . '/' . str_replace('\\', '/', $saveName)]);
        } catch (ValidateException $e) {
            return Util::show(0, $e->getMessage());
        }
    }
}