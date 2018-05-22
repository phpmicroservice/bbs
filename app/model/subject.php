<?php

namespace app\model;

use pms\Mvc\Model;

class subject extends Model
{


    /**
     * 下一篇
     * @return string
     */
    public static function next_info($id)
    {

    }

    /**
     * 编辑文章
     * @param type $uid
     * @param type $id
     * @param type $data
     */
    public static function edit_admin($id, $data)
    {
        Trace::add('info', func_get_args());
        //验证
        $validation = new Validation();
        $validation->validate($data);
        if ($validation->getMessage()) {
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

    /**
     * 获取文章列表 ,分页的
     * @param type $uid
     * @param type $pageData
     */
    public static function lists($where, $pageData)
    {

        $page = service\Article::lists($where, $pageData['now_page'], $pageData['rows']);
        $array = $page->items->toArray();
        foreach ($array as &$value) {

            if ($value['uid'] == 0) {
                $user_info = [
                    'nickname' => 'admin'
                ];

            } else {
                $user_info = \logic\user\User::user_info($value['uid']);
            }
            $value['author'] = $user_info['nickname'];
        }
        $page->items = $array;
        return $page;
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
     * 增加文章
     * @param type $data
     */
    public function add($data)
    {
        //验证
        $validation = new Validation();
        $validation->validate($data);
        if ($validation->getMessage()) {
            return $validation->getMessage();
        }
        //验证通过 进行插入
        $ArticleModel = new model\article();
        $this->transactionManager->get();
        $attachmentArray = new \logic\Attachment\attachmentArray();
        if ($data['cover_id']) {
            $data['cover_id'] = $attachmentArray->many(1, 'article_cover', 0, $data['cover_id']);
        }

        $data['attachment'] = $attachmentArray->many(1, 'article_attachment', 0, $data['attachment']);
        $re = $ArticleModel->save($data);
        if ($re === false) {
            $this->transactionManager->rollback();
            $Message = $ArticleModel->getMessage();
            return $Message;
        }
        $this->transactionManager->commit();
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
     * @param $id
     */
    public function info($id)
    {
        $model = \logic\Article\model\article::findFirstById($id);
        if ($model === false) {
            return '_empty-info';
        }
        # 读取附件信息
        $model->cover_id = \logic\Attachment\attachmentArray::list4id($model->cover_id);
        $model->attachment = \logic\Attachment\attachmentArray::list4id($model->attachment);


        return $model;
    }
}