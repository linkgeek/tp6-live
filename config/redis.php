<?php
/**
 * redis相关配置
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/3/23
 * Time: 上午9:17
 */

return [
    'host' => env('redis.host', '127.0.0.1'),
    'port' => env('redis.port', 6379),
    'auth' => env('redis.auth', ''),
    'out_time' => 120,
    'time_out' => env('redis.time_out', 5), // 超时时间
    'live_game_key' => 'tp6_live_game_key'
];