<?php
namespace app\controller;

use app\BaseController;
use app\common\lib\Redis;
use app\common\lib\redis\Predis;
use app\common\lib\Util;
use think\facade\Log;

class Live extends BaseController
{

    /**
     * 发送验证码
     */
    public function send()
    {
        $phoneNum = intval($_REQUEST['phone_num']);
        if (empty($phoneNum)) {
            return Util::show(config('code.error'), 'error');
        }

        // 生成一个随机数
        $code = rand(1000, 9999);
        Log::write($phoneNum . '_' . $code, 'info');

        // task异步任务
        $taskData = [
            'method' => 'sendSms',
            'data' => [
                'phone' => $phoneNum,
                'code' => $code,
            ]
        ];
        //$_POST['ws_server']->task($taskData);

        return Util::show(config('code.success'), 'ok', ['code' => $code]);
    }

    /**
     * 登录
     */
    public function login()
    {
        // phone code
        $phoneNum = intval($_REQUEST['phone_num']);
        $code = intval($_REQUEST['code']);
        if (empty($phoneNum) || empty($code)) {
            return Util::show(config('code.error'), 'phone or code is error');
        }
        debugLog($phoneNum . '_' . $code, 'login.log');

        // redis code
        $redisCode = 0;
        try {
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        // 判断验证码
        if ($redisCode == $code) {
            // 写入redis
            $data = [
                'user' => $phoneNum,
                'srcKey' => md5(Redis::userkey($phoneNum)),
                'time' => time(),
                'isLogin' => true,
            ];
            Predis::getInstance()->set(Redis::userkey($phoneNum), $data);

            return Util::show(config('code.success'), 'ok', $data);
        }
        return Util::show(config('code.error'), 'login error');
    }
}
