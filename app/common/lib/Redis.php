<?php

namespace app\common\lib;

class Redis {

    /**
     * 验证码 redis key的前缀
     * @var string
     */
    public static $pre = "sms2_";

    /**
     * 用户user pre
     * @var string
     */
    public static $userpre = "user2_";

    /**
     * 存储验证码 redis key
     * @param $phone
     * @return string
     */
    public static function smsKey($phone) {
        return self::$pre . $phone;
    }

    /**
     * 用户 key
     * @param $phone
     * @return string
     */
    public static function userkey($phone) {
        return self::$userpre . $phone;
    }
}