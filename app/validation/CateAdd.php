<?php

namespace app\validation;

use app\model\forum;

class CateAdd extends \pms\Validation
{

    protected function initialize()
    {
        $this->add_uq('name', [
            'message' => 'name-uq',
            'model' => new forum(),
            'attribute' => 'name'
        ]);
        return parent::initialize();
    }
}