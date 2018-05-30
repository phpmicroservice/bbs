<?php

namespace app\logic;

use app\Base;
use app\validation\ArticleAdd;
use pms\Validation\Validator\ServerAction;

class Subject extends Base
{


    public static function ids2list($id_list)
    {
        $list = service\Article::ids2list($id_list);
        return \tool\Arr::array_change_index($list->toArray(), 'id');
    }

    /**
     * 下一篇
     * @return string
     */
    public static function next_info($id)
    {
        $model = \app\model\Subject::next_info($id);
        if ($model === false) {
            return [];
        }
        return $model;
    }

    /**
     * 编辑文章
     * @param type $uid
     * @param type $id
     * @param type $data
     */
    public static function edit_admin($id, $data)
    {
        //验证
        $validation = new Validation();

        if (!$validation->validate($data)) {
            return $validation->getMessage();
        }
        $articleModel = new model\article();
        $findData = [
            'conditions' => 'id = ' . $id
        ];
        $dataBoj = $articleModel->findFirst($findData);
        if (!$dataBoj) {
            return "不存在的数据!";
        }
        $attachmentArray = new \logic\Attachment\attachmentArray();

        # 附件处理
        if ($data['cover_id']) {
            $data['cover_id'] = $attachmentArray->many(1, 'article_cover', $dataBoj->cover_id, $data['cover_id']);
        }

        $data['attachment'] = $attachmentArray->many(1, 'article_attachment', $dataBoj->attachment, $data['attachment']);

        Trace::add('info', $data);
        $dataBoj->setData($data);
        $re = $dataBoj->update();
        if ($re === false) {
            return $dataBoj->getMessage();
        } else {
            return true;
        }
    }

    public function edit($data)
    {
        # 过滤
        $filter = new \app\filterTool\ArticleEdit();
        $filter->filter($data);
        //验证
        $validation = new Validation();
        $validation->validate($data);
        if ($validation->getMessage()) {
            return $validation->getMessage();
        }
        $dataBoj = \app\model\article::findFirst([
            'id = :id:', 'bind' => [
                'id' => $data['id']
            ]
        ]);
        $data['update_time'] = time();
        if (!$dataBoj) {
            return "不存在的数据!";
        }
        Trace::add('info', $data);
        $dataBoj->setData($data);
        $re = $dataBoj->update();
        if ($re === false) {
            return $dataBoj->getMessage();
        } else {
            return true;
        }

    }

    /**
     * 删除
     * @param $id
     * @return bool|string
     */
    public function del($id)
    {
        $model = \app\model\article::findFirstById($id);
        if ($model === false) {
            return '_empty-info';
        }
        $model->status = -1;
        if ($model->save() === false) {
            return "_model-error";
        }
        return true;
    }

    /**
     * @param $id
     */
    public function info4user($id, $user_id)
    {
        $model = \logic\Article\model\article::findFirst([
            'id = :id: and uid =:uid:',
            'bind' => [
                'id' => $id,
                'uid' => $user_id
            ]
        ]);
        if ($model === false) {
            return '_empty-info';
        }
        # 读取点赞 praise ,收藏信息

        return self::call_info($model->toArray(), $user_id);
    }

    /**
     * 处理数据
     * @param array $data
     */
    public static function call_info(array $data, $user_id)
    {
        $praise = \logic\user\praise::info($data['id'], 'article', $user_id);
        $data['praise'] = (int)$praise;

        $collect = \logic\user\collect::is_collect($data['id'], 'article', $user_id);
        $data['collect'] = $collect;
        $data['cover_id'] = \logic\Attachment\attachmentArray::list4id($data['cover_id']);
        return $data;
    }

    /**
     * @param $id
     * @return array|\Phalcon\Mvc\Model
     */
    public function ago_info($id)
    {
        $model = \logic\Article\model\article::ago_info($id);
        if ($model === false) {
            return [];
        }
        return $model;
    }

    /**
     * 文章回复
     * @param type $uid
     * @param type $data
     * @return type
     */
    public function reply($user_id, $data)
    {

        //验证
        $validation = new replyValidation();
        $validation->validate($data);
        if ($validation->getMessage()) {
            return $validation->getMessage();
        }
        # 验证通过 组合数据
        Trace::add('info1', $data);
        $data2 = [];
        $data2['type'] = 'article';
        $data2['content'] = $data['content'];
        $data2['title'] = $data['title'];
        $data2['correlation_id'] = $data['re_id'];
        $data2['reply_reply_id'] = $data['reply_reply_id'];
        $reService = new  \logic\Bbs\correlation();
        return $reService->reply_correlation($user_id, $data2);
    }

    /**
     * 获取文章列表 ,分页的
     * @param type $uid
     * @param type $pageData
     */
    public function lists($where, $now_page, $rows)
    {
        $page = service\Subject::lists($where, $now_page, $rows);
        return $page;
    }

    /**
     * 增加文章
     * @param type $data
     */
    public function add($user_id, array $data)
    {
        $data['user_id'] = $user_id;
        # 过滤
        $filter = new \app\filterTool\ArticleAdd();
        $filter->filter($data);
        //验证
        $validation = new ArticleAdd();
        $validation->add_Validator('content', [
            'message' => 'content',
            'name' => ServerAction::class,
            'data' => [
                'id' => $data['content'],
                'type' => 'subject',
                'user_id' => $user_id
            ],
            'server_action' => 'article@/server/validation'
        ]);
        if (!$validation->validate($data)) {
            return $validation->getMessages();
        }
        $tm233 = $this->transactionManager->get();
        //验证通过 进行插入
        $tm233->begin();
        $ArticleModel = new \app\model\subject();
        if (!$ArticleModel->save($data)) {
            $tm233->rollback();
            return $ArticleModel->getMessage();
        }
        # 进行关联更新
        $re = $this->proxyCS->request_return('article', '/server/correlation', [
            'id' => $data['content'],
            'type' => 'subject',
            'user_id' => $user_id
        ]);
        if (is_array($re) && $re['e']) {
            $tm233->rollback();
            return false;
        }
        $tm233->commit();
        return true;
    }

    /**
     * 查看量 增加+
     * @param $forum_id
     */
    public function viewedadd1($id)
    {
        $info = self::info($id);
        if (is_string($info)) {
            return $info;
        }
        if ($this->session->get('article_viewadd1' . $id)) {
            return true;
        }

        $info->viewed = $info->viewed + 1;
        if ($info->save() === false) {
            $this->session->get('article_viewadd1' . $id, 1);
            return $info->getMessage();
        }
        return true;
    }

    /**
     * 信息
     * @param $id
     */
    public function info($user_id, $id)
    {
        $model = \app\model\article::findFirstById($id);
        if ($model === false) {
            return '_empty-info';
        }

        return $model;
    }
}