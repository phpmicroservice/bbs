<?php

namespace app\controller;


class Server extends Controller
{


    /**
     * 验证论坛版块是否存在
     */
    public function vaforum_ex()
    {
        $id = $this->getData('id');
        $server = new \app\logic\Forums();
        $re = $server->va_ex($id);
        $this->send($re);
    }

    /**
     * 验证 论坛主题 是否存在
     */
    public function vasubject_ex()
    {
        $id = $this->getData('id');
        $server = new \app\logic\Subject();
        $re = $server->va_ex($id);
        $this->send($re);
    }

}