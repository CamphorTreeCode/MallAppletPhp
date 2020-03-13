<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ms_setting}}".
 *
 * @property string $store_id
 * @property string $unpaid
 */
class MsSetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ms_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id'], 'required'],
            [['store_id', 'unpaid'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'store_id' => 'Store ID',
            'unpaid' => '未付款自动取消时间',
        ];
    }
}
