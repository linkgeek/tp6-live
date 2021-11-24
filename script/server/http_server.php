<?php

use Swoole\Http\Server;

$http = new Server('0.0.0.0', 4074);//需要开启8811端口

$http->set(
    [
        'enable_static_handler' => true,
        //'document_root' => __DIR__.'/../public/static',
        //'document_root' => public_path('static'),
        'document_root' => '/data/www/mooc/tp6-live/public/static',//这个路径根据自己的目录设置
        'worker_num' => 5,
    ]
);

//此事件在Worker进程/Task进程启动时发生,这里创建的对象可以在进程生命周期内使用
$http->on('WorkerStart', function (swoole_server $server, $worker_id) {
    // 定义应用目录
    define('APP_PATH', __DIR__ . '/../../app/');
    // tp6.0采用composer安装，没有了tp5.0中的base.php，所以这里需要加载自动加载文件
    require __DIR__ . '/../../vendor/autoload.php';
});

$http->on('request', function ($request, $response) {
    /**
     * 解决上一次输入的变量还存在的问题
     * 方案一：if(!empty($_GET)) {unset($_GET);}
     * 方案二：$http-close();把之前的进程kill，swoole会重新启一个进程，重启会释放内存，把上一次的资源包括变量等全部清空
     * 方案三：$_SERVER  =  []
     */
    //$_SERVER  =  [];//需要注释掉，否则提示找不到参数argv。。。
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

    $_POST = [];
    if (isset($request->post)) {
        foreach ($request->post as $k => $v) {
            $_POST[$k] = $v;
        }
    }

    //开启缓冲区
    ob_start();

    // 执行应用并响应
    try {
        // 执行HTTP应用并响应
        $tp_http = (new \think\App())->http;
        $tp_response = $tp_http->run();
        $tp_response->send();
        //$tp_http->end($tp_response);
    } catch (\Exception $e) {
        // todo
        echo $e->getMessage();
    }

    //输出TP当前请求的控制方法
    //echo "-action-".request()->action().PHP_EOL;
    //获取缓冲区内容
    $res = ob_get_contents();
    ob_end_clean();
    $response->end($res);
    //把之前的进程kill，swoole会重新启一个进程，重启会释放内存，把上一次的资源包括变量等全部清空
    //$http->close();
});

$http->start();