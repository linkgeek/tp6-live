<?php

/**
 * redis相关配置
 */
return [
    'host' => env('redis.host', '127.0.0.1'),
    'port' => env('redis.port', 6379),
    'auth' => env('redis.auth', ''),
    'out_time' => 600, // 短信验证码有效期s
    'time_out' => env('redis.time_out', 5), // 超时时间
    'live_game_key' => env('redis.live_game_key', 'live_game_connect_fd') // 缓存客户端连接fd的key
];