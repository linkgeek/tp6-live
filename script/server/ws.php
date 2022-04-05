<?php

/**
 * WebSocket服务
 * Class Ws
 */
class Ws {
    CONST HOST = "0.0.0.0";
    CONST PORT = 8089;

    public $ws = null;
    private $redis_key_fd = 'live_game_connect_fd'; //缓存客户端连接

    public function __construct() {
        $this->ws = new Swoole\WebSocket\Server(self::HOST, self::PORT);
        $this->ws->set(
            [
                'enable_static_handler' => true,
                'document_root'         => __DIR__ . '/../../public/static',
                'worker_num'            => 2, //cpu核数1-4倍
                'task_worker_num'       => 2, //设置异步任务的工作进程数量，
            ]
        );

        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("workerStart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
     * @param $server
     */
    public function onStart($server) {
        echo '[' . date('Y-m-d H:i:s') . ']onStart' . "\n";
        // 设置主进程别名
        swoole_set_process_name("sports_live_master");
    }

    /**
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id) {
        // $worker_id: < worker_num 为worker进程，>= worker_num 为task进程
        echo '[' . date('Y-m-d H:i:s') . ']onWorkerStart：' . "{$worker_id}\n";

        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../../app/');
        define('LOG_PATH', __DIR__ . '/../../runtime/log/');

        // 引入自动加载文件
        require __DIR__ . '/../../vendor/autoload.php';

        // 重启服务时，获取 key 有值 del
        app\common\lib\redis\Predis::getInstance()->del($this->redis_key_fd);
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) {
        echo '[' . date('Y-m-d H:i:s') . ']' . "onOpen-fd: {$request->fd}\n";
        app\common\lib\redis\Predis::getInstance()->sAdd($this->redis_key_fd, $request->fd);
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response) {
        echo '[' . date('Y-m-d H:i:s') . ']onRequest' . "\n";
        if ($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return;
        }

        //$_SERVER = [];
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }
        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        // 记录请求日志
        // $this->writeLog();

        // 传递Swoole server服务对象
        $_POST['ws_server'] = $this->ws;
        //print_r($_POST);

        // 开启缓冲区
        ob_start();

        // 执行应用并响应
        try {
            $tp_http = (new \think\App())->http;
            $tp_response = $tp_http->run();
            $tp_response->send();
        } catch (\Exception $e) {
            // todo
            echo $e->getMessage();
        }

        //获取缓冲区内容
        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);
    }

    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) {
        echo '[' . date('Y-m-d H:i:s') . ']' . "onMessage - data: {$frame->data}\n";
        $ws->push($frame->fd, "server - push: " . date("Y - m - d H:i:s"));
    }

    /**
     * onTask 回调函数 Task 进程池内被异步执行。执行完成后调用 $serv->finish() 返回结果
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     * @return
     */
    public function onTask($serv, $taskId, $workerId, $data) {
        echo '[' . date('Y-m-d H:i:s') . ']' . "onTask-start: " . json_encode($data) . PHP_EOL;
        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\lib\task\Task;

        $method = $data['method'];
        $flag = $obj->$method($data['data'], $serv);
        echo '[' . date('Y-m-d H:i:s') . ']' . "onTask-{$method}: {$flag}" . PHP_EOL;

        //返回任务执行的结果给worker
        return $flag;
    }

    /**
     * 处理异步任务的结果(此回调函数在worker进程中执行)
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
        echo '[' . date('Y-m-d H:i:s') . ']' . "onFinish - {$taskId}: {$data}" . PHP_EOL;
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        app\common\lib\redis\Predis::getInstance()->sRem($this->redis_key_fd, $fd);
        echo '[' . date('Y-m-d H:i:s') . ']' . "onClose - fd: {$fd}\n";
    }

    /**
     * 记录日志
     */
    public function writeLog() {
        $data = array_merge(['date' => date("Ymd H:i:s")], $_GET, $_POST, $_SERVER);
        $logs = "";
        foreach ($data as $key => $value) {
            $logs .= $key . ":" . $value . " ";
        }

        $filename = LOG_PATH . date("Ym") . "/" . date("d") . "_access.log";
        Swoole\Coroutine\System::writeFile($filename, $logs . "\r\n", FILE_APPEND);
    }
}

// 平滑重启服务：sh /data/www/mooc/tp6-live/script/server/reload.sh
// 守护进程化：nohup /usr/local/php7/bin/php /data/www/mooc/tp6-live/script/server/ws.php > /data/www/mooc/tp6-live/runtime/log/ws.log &
new Ws();