<?php

namespace app;

/**
 * 没有找到的处理
 * Class NotFound
 * @package app
 */
class NotFound
{
    public function beforeNotFoundHandler(\Phalcon\Events\Event $Event, \pms\Dispatcher $dispatcher)
    {
        $dispatcher->connect->send_error("不存在的控制器!", $dispatcher->getTaskName(), 404);
    }

    public function beforeNotFoundAction(\Phalcon\Events\Event $Event, \pms\Dispatcher $dispatcher)
    {
        $dispatcher->connect->send_error("不存在的方法!", $dispatcher->getActionName(), 404);

    }

}