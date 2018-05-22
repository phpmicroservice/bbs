<?php

namespace app;

use pms\Validation\Message\Group;


/**
 * 主控制器
 * Class Controller
 * @property \Phalcon\Cache\BackendInterface $gCache
 * @property \Phalcon\Config $dConfig
 * @property \pms\Validation\Message\Group $message
 * @property \pms\bear\Client $clientSync
 * @package app\controller
 */
class Controller extends \pms\Controller
{
    public $user_id;
    protected $session_id;

    /**
     * 初始化
     * @param $connect
     */
    public function initialize()
    {
        $this->user_id = $this->session->user_id;
        $this->di->setShared('message', function () {
            return new Group();
        });
        $this->message->pruge();
        parent::initialize();
    }


    /**
     * 获取数据
     * @param $pa
     */
    public function getData($name = '', $defind = null)
    {
        $d = $this->connect->getData();
        if ($name) {
            return $d[$name] ?? $defind;
        }
        return $d;
    }


    /**
     * 发送消息
     * @param $re
     */
    public function send($re)
    {
        if ($re instanceof \pms\Validation\Message\Group) {
            # 错误消息
            $d = $re->toArray();
            $this->connect->send_error($d['message'], $d['data'], 424);
        } else {
            if (is_object($re)) {
                $re = json_decode(json_encode($re));
            }
            $this->connect->send_succee($re, '成功');
        }
    }


}