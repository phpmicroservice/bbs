<?php

namespace app;

use Phalcon\Events\Event;

/**
 * 引导类,初始化
 * Class guidance
 * @package app
 */
class Guidance extends \Phalcon\Di\Injectable
{

    /**
     * 构造函数
     * guidance constructor.
     */
    public function __construct()
    {

    }

    /**
     * 开始之前
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function beforeStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        \pms\output('beforeStart  beforeStart', 'beforeStart');
        # 写入依赖注入

    }

    /**
     * 启动事件
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function onWorkerStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        \pms\output($server->taskworker, 'guidance');
        # 绑定一个权限验证
        $this->eventsManager->attach('Router:handleCall', $this);
        # 绑定一个准备判断和准备成功
        $this->eventsManager->attach('Server:readyJudge', $this);
        $this->eventsManager->attach('Server:readySucceed', $this);
        $this->eventsManager->attach('dispatch:beforeNotFoundHandler', new NotFound());
        $this->eventsManager->attach('dispatch:beforeNotFoundAction', new NotFound());
        $this->eventsManager->attach('dispatch:beforeDispatch', new Alc(), 1);
    }

    /**
     * 准备判断
     */
    public function readyJudge(Event $event, \pms\Server $pms_server, $timeid)
    {
        $this->dConfig->ready = true;
        \pms\output('初始化完成', 'init');
    }

    /**
     * 准备完成
     */
    public function readySucceed(Event $event, \pms\Server $pms_server, \Swoole\Server $swoole_server)
    {


    }

    /**
     * 路由事件
     * @param Event $event
     * @param \pms\Router $router
     * @param $data
     */
    public function handleCall(Event $event, \pms\Router $router, $data)
    {
        return true;
    }

}
