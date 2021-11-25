<?php

namespace app\controller;

use app\BaseController;
use app\common\lib\Util;
use think\facade\Db;
use think\facade\Log;
use think\facade\Config;

class Live extends BaseController {

    /**
     * 获取赛况列表
     */
    public function getOuts() {
        $gameId = intval($_POST['game_id']);
        if (empty($gameId)) {
            return Util::show(Config::get('code.error'), 'error');
        }
        $list = Db::table('live_outs')->where('game_id', $gameId)->order('id desc')->limit(10)->select()->toArray();
        $list = array_reverse($list);
        $teamIds = array_unique(array_column($list, 'team_id'));
        $teams = Db::table('live_team')->where('id', 'in', $teamIds)->select()->toArray();
        $teams = array_column($teams, null, 'id');
        foreach ($list as $k => $item) {
            $list[$k]['title'] = '直播员';
            if ($item['team_id']) {
                $list[$k]['title'] = isset($teams[$item['team_id']]) ? $teams[$item['team_id']]['name'] : '';
                $list[$k]['logo'] = isset($teams[$item['team_id']]) ? $teams[$item['team_id']]['image'] : '';
            }
        }
        return Util::show(Config::get('code.success'), 'ok', $list);
    }

    /**
     * 获取聊天室列表
     */
    public function getChat() {
        $gameId = intval($_POST['game_id']);
        if (empty($gameId)) {
            return Util::show(Config::get('code.error'), 'error');
        }
        $list = Db::table('live_chart')->where('game_id', $gameId)->order('id desc')->limit(10)->select()->toArray();
        $list = array_reverse($list);
        return Util::show(Config::get('code.success'), 'ok', $list);
    }

    /**
     * 聊天室
     */
    public function chat() {
        if (empty($_POST['game_id'])) {
            return Util::show(Config::get('code.error'), 'error');
        }
        if (empty($_POST['content'])) {
            return Util::show(Config::get('code.error'), 'error');
        }

        // 聊天入库，参数校验 todo
        $data = [
            'game_id'     => intval($_POST['game_id']),
            'user_name'   => "用户" . rand(0, 2000),
            'content'     => $_POST['content'],
            'status'      => 1,
            'create_time' => time(),
        ];
        Db::table('live_chart')->insert($data);

        // 任务推送
        $data['show_type'] = 'chat'; //聊天室标记
        $taskData = [
            'method' => 'pushChat',
            'data'   => $data
        ];
        $_POST['ws_server']->task($taskData);

        return Util::show(Config::get('code.success'), 'ok');
    }
}
