<?php

namespace app;


/**
 * 基类
 * Class Controller
 * @property \pms\Cache\Backend\Redis $gCache
 * @property \Phalcon\Cache\BackendInterface $sessionCache
 * @property \Phalcon\Config $dConfig
 * @property \Phalcon\Validation\Message\Group $message
 * @property \pms\bear\ClientSync $proxyCS
 * @package app\controller
 */
class Base extends \Phalcon\Di\Injectable
{

}