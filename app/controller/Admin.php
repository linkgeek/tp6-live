<?php

namespace app\controller;

use app\BaseController;
use app\common\lib\Util;
use think\facade\Db;
use think\facade\Log;
use think\facade\Config;

class Admin extends BaseController {

    /**
     * 上传图片
     */
    public function upload() {
        return Util::upload('file', 'upload');
    }

    /**
     * 推送赛况
     */
    public function push() {
        if (empty($_POST)) {
            return Util::show(Config::get('code.error'), 'empty request');
        }

        // 后台系统安全检验 token: md5(content)

        // 获取球队信息
        $info = [];
        if ($_POST['team_id']) {
            $info = Db::table('live_team')->where('id', intval($_POST['team_id']))->find();
        }

        // 获取连接的用户
        // 1.赛况的基本信息入库
        $data = [
            'game_id'     => 1,
            'team_id'     => intval($_POST['team_id']),
            'content'     => $_POST['content'],
            'image'       => !empty($_POST['image']) ? $_POST['image'] : '',
            'type'        => intval($_POST['type']),
            'status'      => 1,
            'create_time' => time(),
        ];
        Db::table('live_outs')->insert($data);

        // 2.数据组织好 push到直播页面
        $data['title'] = !empty($info['name']) ? $info['name'] : '直播员';
        $data['logo'] = !empty($info['image']) ? $info['image'] : '';
        if ($data['image']) {
            $data['image'] = env('live.host', '') . $data['image'];
        }
        $data['show_type'] = 'out';
        $taskData = [
            'method' => 'pushLive',
            'data'   => $data
        ];
        //投递异步任务
        $_POST['ws_server']->task($taskData);
        return Util::show(Config::get('code.success'), 'ok');
    }

    /**
     * 获取赛况列表
     */
    public function getOuts() {
        $gameId = intval($_POST['game_id']);
        if (empty($gameId)) {
            return Util::show(Config::get('code.error'), 'error');
        }
        $list = Db::table('live_outs')->where('game_id', $gameId)->order('id desc')->limit(10)->select();
        $list = array_reverse($list);
        $teamIds = array_unique(array_column($list, 'team_id'));
        $teams = Db::table('live_team')->where('id', 'in', $teamIds)->select();
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
        $list = Db::table('live_chart')->where('game_id', $gameId)->order('id desc')->limit(10)->select();
        $list = array_reverse($list);
        return Util::show(Config::get('code.success'), 'ok', $list);
    }

}