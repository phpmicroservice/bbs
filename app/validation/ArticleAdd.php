<?php

namespace app\validation;

use app\model\article;
use pms\Validation;

/**
 * 文章增加验证
 * Class ArticleAdd
 * @package app\validation
 */
class ArticleAdd extends Validation
{
    //定义验证规则
    protected $rule = [
        'title' => [
            'required' => [
                "message" => "title",
            ],
            'stringLength' => [
                "message" => "stringLength",
                'min' => 2,
                'max' => 20
            ]
        ],
        'category_id' => [
            'required' => [
                "message" => "required",
            ],
            'Validator' => [
                'name' => 'app\validator\CateValidator',
                "message" => "category_id"
            ],
        ],
        'description' => [
            'required' => [
                "message" => "required",
            ],
            'stringLength' => [
                "message" => "stringLength",
                'min' => 1,
                'max' => 50
            ]
        ],

        'can_reply' => [
            'required' => [
                "message" => "can_reply",
            ],
            'in' => [
                'domain' => [1, 0],
                "message" => "can_reply",
            ]
        ]
    ];

    protected function initialize()
    {
        $this->add_uq('content', [
            'model' => new article(),
            'message' => 'uq'
        ]);
        return parent::initialize();
    }

}