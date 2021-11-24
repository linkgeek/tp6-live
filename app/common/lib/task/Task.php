<?php

/**
 * Swoole task异步任务模块
 */

namespace app\common\lib\task;

use app\common\lib\ali\Sms;
use app\common\lib\redis\Predis;
use app\common\lib\Redis;
use think\facade\Config;

class Task {

    /**
     * 异步发送 验证码
     * @param $data
     * @param $serv
     * @return bool
     */
    public function sendSms($data, $serv) {
        /*try {
            //发送短信
            $sms = new Sms();
            $response = $sms->sendSms($data['phone'], $data['code']);
            Log::write($response, 'debug');
        } catch (\Exception $e) {
            // todo
            Log::write($e->getMessage(), 'error');
            return false;
        }

        // 如果发送成功 把验证码记录到redis里面
        if ($response->Code !== "OK") {
            return false;
        }*/
        //debugLog($data, 'debug.log');

        Predis::getInstance()->set(Redis::smsKey($data['phone']), $data['code'], 600);
        return true;
    }

    /**
     * 赛况推送
     * @param $data
     * @param $serv
     * @return bool
     */
    public function pushLive($data, $serv) {
        $clients = Predis::getInstance()->sMembers(Config::get("redis.live_game_key"));
        foreach ($clients as $fd) {
            $serv->push($fd, json_encode($data));
        }
        return true;
    }

    /**
     * 聊天室推送
     * @param $data
     * @param $serv
     * @return bool
     */
    public function pushChat($data, $serv) {
        /*foreach ($_POST['ws_server']->ports[1]->connections as $fd) {
            $_POST['ws_server']->push($fd, json_encode($data));
        }*/
        $clients = Predis::getInstance()->sMembers(Config::get("redis.live_game_key") . '_chat');
        foreach ($clients as $fd) {
            $serv->push($fd, json_encode($data));
        }
        return true;
    }
}