<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/3/26
 * Time: 上午3:52
 */

namespace app\common\lib\redis;

use think\facade\Config;

class Predis {

    public $redis = "";

    /**
     * 定义单例模式的变量
     * @var null
     */
    private static $_instance = null;

    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->redis = new \Redis();
        //$redis_conf = Config::get('redis');
        //debugLog('redis-config: ' . json_encode($redis_conf), 'login.log');
        //$result = $this->redis->connect($redis_conf['host'], $redis_conf['port'], $redis_conf['time_out']);
        $result = $this->redis->connect('127.0.0.1', 6379, 3);
        $this->redis->auth('$redis@0918');
        if ($result === false) {
            throw new \Exception('redis connect error');
        }
    }

    /**
     * set
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0) {
        if (!$key) {
            return '';
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if (!$time) {
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $time, $value);
    }

    /**
     * get
     * @param $key
     * @return bool|string
     */
    public function get($key) {
        if (!$key) {
            return '';
        }

        return $this->redis->get($key);
    }

    public function del($key) {
        if (!$key) {
            return '';
        }

        return $this->redis->del($key);
    }

    /*public function sAdd($key, $val) {
        return $this->redis->sAdd($key, $val);
    }

    public function sRem($key, $val) {
        return $this->redis->sRem($key, $val);
    }*/

    /**
     * @param $key
     * @return array
     */
    public function sMembers($key) {
        return $this->redis->sMembers($key);
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool
     */
    public function __call($name, $arguments) {
        if (!is_array($arguments) || count($arguments) != 2) {
            return false;
        }
        return $this->redis->$name($arguments[0], $arguments[1]);
    }
}