<?php

namespace app\controller;

use app\Controller;

/**
 * 论坛版块管理
 * Class Forum
 * @package app\controller
 */
class Forum extends Controller

{

    public function index()
    {
        $where = $this->getData();
        $server = new \app\logic\Forums();
        $re = $server->lists($where);
        $this->send($re);
    }

    /**
     * 创建一个版块
     */
    public function create()
    {
        $data = $this->getData();
        $server = new \app\logic\Forums();
        $re = $server->add($data);
        $this->send($re);
    }

    public function edit()
    {
        $data = $this->getData();
        $server = new \app\logic\Forums();
        $re = $server->edit($data);
        $this->send($re);
    }

    public function dele()
    {
        $id = $this->getData('id');
        $server = new \app\logic\Forums();
        $re = $server->dele($id);
        $this->send($re);
    }

    public function info()
    {
        $id = $this->getData('id');
        $server = new \app\logic\Forums();
        $re = $server->info($id);
        $this->send($re);
    }
}