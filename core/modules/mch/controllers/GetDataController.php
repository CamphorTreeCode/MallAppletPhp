<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10
 * Time: 10:29
 */

namespace app\modules\mch\controllers;


use app\models\Order;
use app\models\OrderMessage;

class GetDataController extends Controller
{
    /**
     * 获取订单提示列表
     */
    public function actionOrder()
    {
        // 已下订单
        $order_list = OrderMessage::find()->alias('om')->where([
            'type' => 0,
            'om.store_id' => $this->store->id,
            'om.is_read' => 0,
            'om.is_delete' => 0
        ])->leftJoin(Order::tableName() . ' o', 'o.id=om.order_id')->select([
            'om.id', 'om.addtime', 'o.name','om.is_sound','type'
        ])->orderBy(['om.addtime' => SORT_DESC])->limit(5)->asArray()->all();

        // 售后订单
        $refund_list = OrderMessage::find()->alias('om')->where([
            'type' => 1,
            'om.store_id' => $this->store->id,
            'om.is_read' => 0,
            'om.is_delete' => 0
        ])->leftJoin(Order::tableName() . ' o', 'o.id=om.order_id')->select([
            'om.id', 'om.addtime', 'o.name','om.is_sound'
        ])->orderBy(['om.addtime' => SORT_DESC])->limit(5)->asArray()->all();

        $list = array_merge($order_list,$refund_list);
        

        $id = array();
        foreach ($list as $index => $value) {

            $time = time() - $value['addtime'];

            if ($time < 60) {
                $list[$index]['time'] = $time . '秒前';
            } else if ($time < 3600) {
                $list[$index]['time'] = ceil($time / 60) . '分钟前';
            } else if ($time < 86400) {
                $list[$index]['time'] = ceil($time / 3600) . '小时前';
            } else {
                $list[$index]['time'] = ceil($time / 86400) . '天前';
            }
            $id[] = $value['id'];
        }
        OrderMessage::updateAll(['is_sound'=>1],['in','id',$id]);
        $this->renderJson([
            'code' => 0,
            'msg' => '',
            'data' => $list
        ]);
    }

    /**
     * 删除订单提示
     */
    public function actionMessageDel($id = null)
    {
        OrderMessage::updateAll(['is_read'=>1],['id'=>$id]);
    }
}