<?php

namespace app\modules\mch\models;

use app\models\OrderComment;

/**
 * @property Topic $model
 */
class OrderCommentForm extends Model
{
    public $model;

    public $store_id;
    public $goods_id;
    public $score;
    public $content;
    public $pic_list;
    public $is_hide;
    public $is_virtual;
    public $virtual_user;
    public $virtual_avatar;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'score','virtual_user','is_hide'], 'required'],
            [['goods_id', 'is_hide', 'is_virtual'], 'integer'],
            [['content','virtual_avatar'], 'string', 'max' => 1000],
            [['virtual_user'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'goods_id' => 'Goods ID',
            'user_id' => 'User ID',
            'score' => '评分：1=差评，2=中评，3=好',
            'content' => '评价内容',
            'pic_list' => '图片',
            'is_hide' => '是否隐藏：0=不隐藏，1=隐藏',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'reply_content' => 'Reply Content',
            'is_virtual' => 'Is Virtual',
            'virtual_user' => 'Virtual User',
            'virtual_avatar' => 'Virtual avatar',
        ];
    }

    public function save()
    {
       
       if (!$this->validate())
            return $this->getModelError();
        $this->model->attributes = $this->attributes;
        $this->model->store_id = $this->store_id;
        $this->model->user_id = 0;
        $this->model->order_id = 0;
        $this->model->order_detail_id = 0;
        $this->model->user_id = 0;
        $this->model->is_delete = 0;
        $this->model->addtime = time();
        $this->model->is_virtual = 1;
        $this->model->pic_list = $this->pic_list;

        if ($this->model->save())
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        else
            return $this->getModelError($this->model);
    }
}