<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/16
 * Time: 15:00
 */

namespace app\modules\api\models\recharge;


use app\models\Order;
use app\models\ReOrder;
use app\modules\api\models\Model;

class RecordForm extends Model
{
    public $store_id;
    public $user;

    public $date;


    public function rules()
    {
        return [
            [['date'],'string'],
            [['date'],'default','value'=>function(){
                return date('Y-m', time());
            }]
        ];
    }

    public function search()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        //搜索置顶月份的充值记录及余额消费记录
        $date = $this->date;
        $start = strtotime($date);
        $end = strtotime(date('Y-m-t', $start)) + 86400;
        $list = ReOrder::find()->where([
            'store_id' => $this->store_id, 'is_delete' => 0, 'is_pay' => 1, 'user_id' => $this->user->id
        ])->andWhere([
                'and',
                ['>=', 'addtime', $start],
                ['<', 'addtime', $end]
            ])->orderBy(['addtime' => SORT_DESC])->asArray()->all();
        $new_list = [];
        $time_arr = [];
        foreach($list as $index=>$value){
            $arr = [];
            $arr['date'] = date('Y-m-d H:i:s',$value['addtime']);
            $arr['flag'] = '0';
            $arr['price'] = '+'.(floatval($value['pay_price']) + floatval($value['send_price']));
            $new_list[] = $arr;
            $time_arr[] = $arr['date'];
        }
        $order_list = Order::find()->where([
            'store_id'=>$this->store_id,'is_delete'=>0,'is_cancel'=>0,'is_pay'=>1,'user_id'=>$this->user->id,'pay_type'=>3
        ])->andWhere([
            'and',
            ['>=','addtime',$start],
            ['<','addtime',$end]
        ])->orderBy(['addtime'=>SORT_DESC])->asArray()->all();
        foreach($order_list as $index=>$value){
            $arr = [];
            $arr['date'] = date('Y-m-d H:i:s',$value['addtime']);
            $arr['flag'] = 1;
            $arr['price'] = '-'.floatval($value['pay_price']);
            $arr['order_no'] = $value['order_no'];
            $new_list[] = $arr;
            $time_arr[] = $arr['date'];
        }
        array_multisort($time_arr, SORT_DESC, $new_list);

        return [
            'list'=>$new_list,
            'date'=>$date
        ];

    }
}