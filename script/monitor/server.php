<?php

date_default_timezone_set('Asia/Shanghai');

/**
 * 监控服务 ws http 端口
 */
class Server {

    const PORT = 4074;

    public function port() {
        $shell = "netstat -anp 2>/dev/null | grep " . self::PORT . " | grep LISTEN | wc -l";
        $result = shell_exec($shell);
        if ($result != 1) {
            // 发送报警服务 邮件 短信
            /// todo
            echo date("Ymd H:i:s") . "|port-error" . PHP_EOL;
            $flag = shell_exec('sh /data/www/mooc/tp6-live/script/server/reload.sh');
            echo date("Ymd H:i:s") . "|port-reload: " . $flag . PHP_EOL;
        } else {
            echo date("Ymd H:i:s") . "|port-success" . PHP_EOL;
        }
    }
}

// nohup /usr/local/php7/bin/php /data/www/mooc/tp6-live/script/monitor/server.php > /data/www/mooc/tp6-live/runtime/log/ws.monitor.log &
// ps aux|grep monitor/server.php
swoole_timer_tick(1000, function ($timer_id) {
    (new Server())->port();
});
