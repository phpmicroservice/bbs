<?php

namespace app\logic;

use app\Base;
use app\filterTool\CateFilter;
use app\model\forum;
use app\validation\CateAdd;
use app\validation\CateEdit;
use app\validation\ForumsDel;

class Forums extends Base
{

    /**
     * 转换分类的name 为分类的id
     * @param $name
     * @return int
     */
    public static function name2id($name): int
    {
        $model = forum::findFirstByname($name);
        if (!($model instanceof forum)) {
            return 0;
        }
        return (int)$model->id;

    }

    /**
     * 验证是否存在
     * @param $id
     */
    public function va_ex($id)
    {
        $model = forum::findFirstByid($id);
        if (empty($model)) {
            return false;
        }
        return true;
    }

    /**
     * 获取列表
     * @param $where
     * @return mixed
     */
    public function lists($where)
    {

        $modelsManager = \Phalcon\Di::getDefault()->get('modelsManager');
        $builder = $modelsManager->createBuilder()
            ->from(forum::class)
            ->orderBy("id");

        $builder = $this->call_where($builder, $where);
        $list = $builder->getQuery()->execute();
        return $list->toArray();
    }

    /**
     * 处理where条件
     * @param \Phalcon\Mvc\Model\Query\Builder $builder
     * @param $where
     */
    protected function call_where(\Phalcon\Mvc\Model\Query\Builder $builder, $where)
    {

        if (isset($where['pid']) && !empty($where['pid'])) {
            $builder->andWhere(' pid =:pid:', [
                'pid' => $where['pid']
            ]);
        }
        return $builder;
    }

    /**
     * 增加文章分类,管理员的途径
     * @param $data
     */
    public function add($data)
    {
        # 进行数据过滤
        $ft = new CateFilter();
        $ft->filter($data);
        $va = new CateAdd();
        if (!$va->validate($data)) {
            return $va->getMessages();
        }
        $subject_category = new forum();
        $subject_category->setData($data);
        try {
            if (!$subject_category->save()) {
                return $subject_category->getMessage();
            }
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }

        return (int)$subject_category->id;
    }

    /**
     * 编辑分类信息,管理员的途径
     *
     * @param $id
     * @param $data
     */
    public function edit($data)
    {
        # 进行数据过滤 和验证
        $ft = new CateFilter();
        $ft->filter($data);
        $id = $data['id'] ?? 0;
        $va = new CateEdit();
        if (!$va->validate($data)) {
            return $va->getMessages();
        }
        # 验证完成
        $subject_category = forum::findFirst([
            'id = :id:', 'bind' => [
                'id' => $id
            ]
        ]);
        if ($subject_category instanceof forum) {
            //成功的读取了数据
        } else {
            return "empty-error";
        }
        $subject_category->setData($data);
        if ($subject_category->save() === false) {
            return $subject_category->getMessage();
        }
        return true;
    }

    /**
     * 删除文章分类,管理员的途径
     * @param $id
     */
    public function dele($id)
    {
        # 验证数据
        $validation = new ForumsDel();
        if (!$validation->validate(['id' => $id])) {
            return $validation->getMessages();
        }
        $forum = forum::findFirst([
            'id = :id:', 'bind' => [
                'id' => $id
            ]
        ]);
        if ($forum instanceof forum) {
            //成功的读取了数据
        } else {
            return "empty-error";
        }
        if ($forum->delete() === false) {
            return $forum->getMessage();

        }
        return true;

    }

    /**
     * 获取信息
     * @param $id
     * @return \Phalcon\Mvc\Model|string
     */
    public function info($id)
    {
        $forum = forum::findFirst([
            'id = :id:', 'bind' => [
                'id' => $id
            ]
        ]);
        if ($forum instanceof forum) {
            //成功的读取了数据
            return $forum->toArray();
        } else {
            return "empty-error";
        }
    }

}