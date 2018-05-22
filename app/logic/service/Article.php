<?php

namespace app\logic\service;

use app\Base;
use app\logic\Cate;

class Article extends Base
{

    public static function ids2list($id_list)
    {
        return \app\model\article::query()->inWhere('id', $id_list)->execute();
    }

    /**
     * 列表
     * @param $uid
     * @param $page
     * @param int $row
     * @return \stdClass
     */
    public static function lists($where, $page, $row = 10)
    {
        $modelsManager = \Phalcon\Di::getDefault()->get('modelsManager');
        $builder = $modelsManager->createBuilder()
            ->from(\app\model\article::class)
            ->orderBy("id");
        $builder = self::call_where($builder, $where);
        $paginator = new \pms\Paginator\Adapter\QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $row,
                "page" => $page,
            ]
        );
        return $paginator->getPaginate();
    }

    private static function call_where(\Phalcon\Mvc\Model\Query\Builder $builder, $where)
    {
        if (isset($where['user_id']) && !empty($where['user_id'])) {
            $builder->andwhere(' uid= :uid:', [
                'uid' => $where['user_id']
            ]);
        }
        if (isset($where['froum_id']) && !empty($where['froum_id'])) {
            $builder->andwhere(' froum_id= :froum_id:', [
                'froum_id' => $where['froum_id']
            ]);
        }

        if (isset($where['search_key']) && !empty($where['search_key'])) {
            $builder->where("title LIKE :title:", [
                "title" => "%" . $where['search_key'] . "%"]);
        }
        return $builder;
    }

}