<?php

namespace app\common\lib\redis;

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