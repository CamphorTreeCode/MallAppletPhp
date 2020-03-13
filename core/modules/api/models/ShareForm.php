<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/9
 * Time: 16:04
 */

namespace app\modules\api\models;
use app\models\FormId;
use app\models\Setting;
use app\models\User;

/**
 * @property \app\models\Share $share
 */
class ShareForm extends Model
{
    public $share;
    public $store_id;
    public $user_id;

    public $name;
    public $mobile;
    public $agree;
    public $form_id;

    /**
     * @return array
     * 场景说明：NONE_CONDITION--无条件
     *           APPLY--需要申请
     */
    public function rules()
    {
        return [
            [['name','mobile','agree'],'required','on'=>'APPLY'],
            [['agree'],'integer'],
            [['name','mobile','form_id'],'trim'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'name'=>'真实姓名',
            'mobile'=>'手机号'
        ];
    }
    public function save()
    {
        if($this->validate()){
            $t = \Yii::$app->db->beginTransaction();
            if(($this->agree || $this->agree == 0) && $this->agree != 1){
                return [
                    'code'=>1,
                    'msg'=>'请先阅读并确认分销申请协议'
                ];
            }
            $this->share->attributes = $this->attributes;
            if($this->share->isNewRecord){
                $this->share->is_delete = 0;
                $this->share->addtime = time();
                $this->share->store_id = $this->store_id;
            }
            $this->share->user_id = \Yii::$app->user->identity->id;
            $user = User::findOne(['id'=>\Yii::$app->user->identity->id,'store_id'=>$this->store_id]);
            $share_setting = Setting::findOne(['store_id' => $this->store_id]);
            if($share_setting->share_condition != 2){
                $user->is_distributor = 2;
                $this->share->status = 0;
                $user->time = 0;
            }else{
                $user->is_distributor = 1;
                $this->share->status = 1;
                $user->time = time();
            }
            if(!$user->save()){
                $t->rollBack();
                return [
                    'code'=>1,
                    'msg'=>'网络异常'
                ];
            }
            if($this->share->save()){
                FormId::addFormId([
                    'store_id'=>$this->store_id,
                    'user_id'=>$user->id,
                    'wechat_open_id'=>$user->wechat_open_id,
                    'form_id'=>$this->form_id,
                    'type'=>'form_id',
                    'order_no' => 'apply' . md5("id={$this->cash->id}&store_id={$this->store_id}")
                ]);
                $t->commit();
                return [
                    'code'=>0,
                    'msg'=>'成功'
                ];
            }else{
                $t->rollBack();
                return [
                    'code'=>1,
                    'msg'=>'网络异常',
                    'data'=>$this->errors,
                ];
            }
        }else{
            return $this->getModelError();
        }
    }

    /**
     * @return array
     * 获取佣金相关信息
     */
    public function getPrice()
    {
        $list = User::find()->alias('u')
            ->where(['u.is_delete'=>0,'u.store_id'=>$this->store_id,'u.id'=>$this->user_id])
            ->leftJoin('{{%cash}} c','c.user_id=u.id and c.is_delete=0')
            ->select([
                'u.total_price','u.price',
                'sum(case when c.status = 2 then c.price else 0 end) cash_price',
                'sum(case when c.status = 1 then c.price else 0 end) un_pay',
                'sum(case when c.status < 3 then c.price else 0 end) total_cash'
            ])->groupBy('c.user_id')->asArray()->one();
        return $list;
    }

    /**
     * @return array|null|\yii\db\ActiveRecord
     *
     */
    public function getCash()
    {
        $list = User::find()->alias('u')
            ->where(['u.is_delete'=>0,'u.store_id'=>$this->store_id,'u.id'=>$this->user_id])
            ->leftJoin('{{%cash}} c','c.user_id=u.id and c.is_delete=0')
            ->select([
                'u.total_price','u.price',
                'sum(case when c.status = 2 then c.price else 0 end) cash_price',
                'sum(case when c.status = 1 then c.price else 0 end) un_pay'
            ])->groupBy('c.user_id')->asArray()->one();
        return $list;
    }

}