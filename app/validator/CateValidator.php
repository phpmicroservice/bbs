<?php

namespace app\validatior;

class CateValidator extends \pms\Validation\Validator
{

    /**
     * 执行验证
     *
     * @param \Phalcon\Validation $validator
     * @param string $attribute
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        return true;
    }
}