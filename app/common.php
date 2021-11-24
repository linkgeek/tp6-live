<?php
// 应用公共文件

// 应用公共文件
if (!function_exists('debugLog')) {
    /**
     * 打印日志
     * @param string|array $msg 内容
     * @param string $fileName 文件名
     * @param int $maxSize 单位M
     */
    function debugLog($msg, $fileName = 'debug.log', $maxSize = 2)
    {
        //$filePath = LOG_PATH . $fileName;
        $filePath = '/data/www/mooc/tp6-live/runtime/log/' . $fileName;
        $fileSize = file_exists($filePath) ? @filesize($filePath) : 0;
        $flag = $fileSize < max(1, $maxSize) * 1024 * 1024;
        $msgPrefix = '[' . date('Y-m-d H:i:s') . ']';
        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $msg = $msgPrefix . $msg . "\r\n";
        @file_put_contents($filePath, $msg, $flag ? FILE_APPEND : null);
    }
}