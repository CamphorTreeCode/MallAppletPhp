<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 17:46
 */

namespace app\behaviors;


use app\extensions\PinterOrder;
use app\models\Goods;
use app\models\Level;
use app\models\MiaoshaGoods;
use app\models\MsOrder;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PrinterSetting;
use app\models\Setting;
use app\models\Store;
use app\models\User;
use app\models\UserShareMoney;
use Codeception\Module\Memcache;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * 检查订单过期未付款、超时自动确认收货、分销佣金发放等
 * @property \app\models\Store $store;
 * @property \app\models\Setting $share_setting;
 *
 */
class OrderBehavior extends Behavior
{
    public $only_routes = [
        'mch/order/*',
        'mch/share/*',
        'mch/miaosha/*',
        'api/order/*',
        'api/share/*',
        'api/miaosha/*',
    ];

    public $store_id;
    public $store;
    public $share_setting;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }


    public function beforeAction($e)
    {
        if (DoNotDoIt::doNotDoIt($this->only_routes))
            return;
        \Yii::warning('----ORDER BEHAVIOR----');
        $order_behavior_running = 'order_behavior_running';
        if (\Yii::$app->cache->get($order_behavior_running)) {
            return true;
        }
        \Yii::$app->cache->set($order_behavior_running, true, 60);

        $this->store_id = isset(\Yii::$app->controller->store) ? \Yii::$app->controller->store->id : 0;
        if (!$this->store_id) {
            \Yii::$app->cache->delete($order_behavior_running);
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);

        $time = time();
        if ($this->store->over_day > 0) {
            $over_day = $time - ($this->store->over_day * 3600);
            // 用户积分恢复
            $npOrder = Order::find()->where([
                'is_pay' => 0, 'store_id' => $this->store_id, 'is_cancel' => 0
            ])->andWhere(['<=', 'addtime', $over_day])->all();
            foreach ($npOrder AS $key => $value) {
                $integral = json_decode($value['integral'])->forehead_integral;
                $user = User::findOne(['id' => $value['user_id']]);
                $user->integral += $integral ? $integral : 0;
                $user->save();
                //库存恢复
                $order_detail_list = OrderDetail::find()->where(['order_id' => $value['id'], 'is_delete' => 0])->all();
                foreach ($order_detail_list as $order_detail) {
                    $goods = Goods::findOne($order_detail->goods_id);
                    $attr_id_list = [];
                    foreach (json_decode($order_detail->attr) as $item)
                        array_push($attr_id_list, $item->attr_id);
                    $goods->numAdd($attr_id_list, $order_detail->num);
                }
                //订单超过设置的未支付时间，自动取消
                $value->is_cancel = 1;
                $value->save();
            }
//            $count_p = Order::updateAll([
//                'is_cancel' => 1,
//            ], 'is_pay=0 and is_cancel=0 and addtime<=:addtime and store_id=:store_id',
//                [':addtime' => $over_day, ':store_id' => $this->store_id]);
        }
        $delivery_time = $time - ($this->store->delivery_time * 86400);
        $sale_time = $time - ($this->store->after_sale_time * 86400);
        //订单超过设置的确认收货时间，自动确认收货
        /*
        $count = Order::updateAll([
            'is_confirm' => 1, 'confirm_time' => time()],
            'is_delete=0 and is_send=1 and send_time <= :send_time and store_id=:store_id and is_confirm=0',
            [':send_time' => $delivery_time, ':store_id' => $this->store_id]);
        */
        $order_confirm = Order::find()->where([
            'is_delete' => 0, 'is_send' => 1, 'store_id' => $this->store_id, 'is_confirm' => 0
        ])->andWhere(['<=', 'send_time', $delivery_time])->asArray()->all();

        foreach ($order_confirm as $k => $v) {
            Order::updateAll(['is_confirm' => 1, 'confirm_time' => time()], ['id' => $v['id']]);
            $printer_setting = PrinterSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0]);
            $type = json_decode($printer_setting->type, true);
            if ($type['confirm'] && $type['confirm'] == 1) {
                $printer_order = new PinterOrder($this->store_id, $v['id']);
                \Yii::warning('==>>' . '打印');
                $res = $printer_order->print_order();
            }
        }

        //超过设置的售后时间且没有在售后的订单
        $order_list = Order::find()->alias('o')
            ->where([
                'and',
                ['o.is_delete' => 0, 'o.is_send' => 1, 'o.is_confirm' => 1, 'o.store_id' => $this->store_id, 'o.is_sale' => 0],
                ['<=', 'o.confirm_time', $sale_time],
            ])
            ->leftJoin(OrderRefund::tableName() . ' r', "r.order_id = o.id and r.is_delete = 0")
            ->select(['o.*'])->groupBy('o.id')
            ->andWhere([
                'or',
                'isnull(r.id)',
                ['r.type' => 2],
                ['in', 'r.status', [2, 3]]
            ])
            ->offset(0)->limit(20)->asArray()->all();
        foreach ($order_list as $index => $value) {
            \Yii::warning('==>' . $value['id']);
            Order::updateAll(['is_sale' => 1], ['id' => $value['id']]);
            $this->share_money($value['id']);
            $this->give_integral($value['id']);
        }
        //会员等级
        $user_id_arr = Order::find()->select('user_id')->where(['is_delete' => 0, 'store_id' => $this->store_id, 'is_confirm' => 1, 'is_send' => 1])
            ->andWhere(['<=', 'confirm_time', $sale_time])->groupBy('user_id')->asArray()->all();

        foreach ($user_id_arr as $index => $value) {
            $user = User::findOne(['id' => $value, 'store_id' => $this->store_id]);
            $order_money = Order::find()->where(['store_id' => $this->store_id, 'user_id' => $user->id, 'is_delete' => 0])
                ->andWhere(['is_pay' => 1, 'is_confirm' => 1, 'is_send' => 1])->andWhere(['<=', 'confirm_time', $sale_time])->select([
                    'sum(pay_price)'
                ])->scalar();
            if (!$order_money) {
                $order_money = 0;
            }
            $next_level = Level::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])
                ->andWhere(['<=', 'money', $order_money])->orderBy(['level' => SORT_DESC, 'id' => SORT_DESC])->asArray()->one();
            if ($user->level < $next_level['level']) {
                $user->level = $next_level['level'];
                $user->save();
            }
        }
        \Yii::$app->cache->delete($order_behavior_running);

        $this->checkMsNoPayOrderTimeout($e); //处理未在规定时间内付款的秒杀订单
    }

    /**
     * @param $parent_id
     * @param $money
     * @return array
     *
     */
    private function money($parent_id, $money)
    {
        if ($parent_id == 0) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent = User::findOne(['id' => $parent_id]);
        if (!$parent) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent->total_price += $money;
        $parent->price += $money;
        if ($parent->save()) {
            return [
                'code' => 0,
                'parent_id' => $parent->parent_id
            ];
        } else {
            return [
                'code' => 1,
                'parent_id' => 0
            ];
        }
    }

    /**
     * @param $parent_id
     * @param $percent
     * @param $price
     * @return array
     * 已废弃
     */
    public static function shareMoney($parent_id, $percent, $price)
    {
        if ($parent_id == 0) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent = User::findOne(['id' => $parent_id]);
        if (!$parent) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent->total_price += ($price * $percent / 100);
        $parent->price += ($price * $percent / 100);

        if ($parent->save()) {
            return [
                'code' => 0,
                'parent_id' => $parent->parent_id
            ];
        } else {
            return [
                'code' => 1,
                'parent_id' => 0
            ];
        }
    }

    /**
     * @param $id
     * 佣金发放
     */
    private function share_money($id)
    {
        $order = Order::findOne($id);
        \Yii::warning('==>' . $id . '1');
        if ($this->share_setting->level == 0) {
            return;
        }
        \Yii::warning('==>' . $id . '2');
        if ($order->is_price != 0) {
            return;
        }
//        \Yii::warning('==>'.$id.'3');
        //一级佣金发放
        if ($this->share_setting->level >= 1) {
//            \Yii::warning('==>'.$id.'4');
            $user_1 = User::findOne($order->parent_id);
            if (!$user_1) {
                return;
            }
//            \Yii::warning('==>'.$id.'5');
            $user_1->total_price += $order->first_price;
            $user_1->price += $order->first_price;
            $user_1->save();
//            \Yii::warning('==>'.$id.'6');
            UserShareMoney::set($order->first_price, $user_1->id, $order->id, 0, 1, $this->store_id);
//            \Yii::warning('==>'.$id.'7');
            $order->is_price = 1;
            $order->save();
        }
//        \Yii::warning('==>'.$id.'8');
//        \Yii::warning('==>'.$id.'9');
        //二级佣金发放
        if ($this->share_setting->level >= 2) {
            $user_2 = User::findOne($order->parent_id_1);
            if (!$user_2) {
                if ($user_1->parent_id != 0 && $order->parent_id_1 == 0) {
                    $res = self::money($user_1->parent_id, $order->second_price);
                    UserShareMoney::set($order->second_price, $user_1->parent_id, $order->id, 0, 2, $this->store_id);
                    if ($res['parent_id'] != 0 && $this->share_setting->level == 3) {
                        $res = self::money($res['parent_id'], $order->third_price);
                        UserShareMoney::set($order->third_price, $res['parent_id'], $order->id, 0, 3, $this->store_id);
                    }
                }
                return;
            }
            $user_2->total_price += $order->second_price;
            $user_2->price += $order->second_price;
            $user_2->save();
            UserShareMoney::set($order->second_price, $user_2->id, $order->id, 0, $this->store_id);
        }
        //三级佣金发放
        if ($this->share_setting->level >= 3) {
            $user_3 = User::findOne($order->parent_id_2);
            if (!$user_3) {
                if ($user_2->parent_id != 0 && $order->parent_id_2 == 0) {
                    self::money($user_2->parent_id, $order->third_price);
                    UserShareMoney::set($order->third_price, $user_2->parent_id, $order->id, 0, 3, $this->store_id);
                }
                return;
            }
            $user_3->total_price += $order->third_price;
            $user_3->price += $order->third_price;
            $user_3->save();
            UserShareMoney::set($order->third_price, $user_3->id, $order->id, 0, $this->store_id);
        }
    }

    /**
     * 积分发放
     */
    private function give_integral($id)
    {
        $give = Order::findOne($id);
        if ($give['give_integral'] != 0) {
            return;
        }
        $integral = OrderDetail::find()
            ->andWhere(['order_id' => $give['id'], 'is_delete' => 0])
            ->select([
                'sum(integral)'
            ])->scalar();

        $giveUser = User::findOne(['id' => $give['user_id']]);
        $giveUser->integral += $integral;
        $giveUser->total_integral += $integral;
        $giveUser->save();
        $give->give_integral = 1;
        $give->save();
    }


    /**
     * 处理未在规定时间内付款的秒杀订单
     * @param ActionEvent $event
     * @return bool
     */
    private function checkMsNoPayOrderTimeout($event)
    {
        $order_max = 100;//每次最多处理的个数，防止运行太久
        $cache_key = 'pt_no_pay_order_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);
        /** @var Store $store */
//        $store = isset($event->action->controller->store) ? $event->action->controller->store : null;
//        var_dump($store);die();
//        if (!$store) {
//            \Yii::$app->cache->set($cache_key, false);
//            return true;
//        }

//        if (!$store->over_day || $store < 0)
//            return true;
//        $expire_time = intval($store->over_day) * 3600;
        /** @var PtOrder[] $order_list */
        $order_list = MsOrder::find()->where([
            'AND',
            [
                'is_pay' => 0,
                'is_cancel' => 0,
            ],
            ['<=', 'limit_time', time()],
//            ['<=', 'addtime', time() - $expire_time],
        ])->limit($order_max)->all();
        foreach ($order_list as $order) {
            $order->is_cancel = 1;
            $order->save(false);
            if ($order->save(false)) {
                $miaosha_goods = MiaoshaGoods::findOne([
                    'goods_id' => $order->goods_id,
                    'is_delete' => 0,
                    'start_time' => intval(date('H', $order->addtime)),
                    'open_date' => date('Y-m-d', $order->addtime),
                ]);
                $attr_data = json_decode($miaosha_goods->attr, true);
                foreach ($attr_data as $i => $attr_data_item) {
                    $attr_data[$i]['sell_num'] = $attr_data_item['sell_num'] - $order->num;
                }
                $miaosha_goods->attr = json_encode($attr_data, JSON_UNESCAPED_UNICODE);
                $miaosha_goods->save();
            }
        }

        \Yii::$app->cache->set($cache_key, false);
        return true;
    }

//    private function setMiaoshaSellNum($miaosha_goods_id, $attr_id_list, $num)
//    {
//        $miaosha_goods = MiaoshaGoods::findOne($miaosha_goods_id);
//        if (!$miaosha_goods)
//            return false;
//        sort($attr_id_list);
//        $attr_data = json_decode($miaosha_goods->attr, true);
//        foreach ($attr_data as $i => $attr_row) {
//            $_tmp_attr_id_list = [];
//            foreach ($attr_row['attr_list'] as $attr) {
//                $_tmp_attr_id_list[] = intval($attr['attr_id']);
//            }
//            sort($_tmp_attr_id_list);
//            if ($_tmp_attr_id_list == $attr_id_list) {
//                $attr_data[$i]['sell_num'] = intval($attr_data[$i]['sell_num']) + intval($num);
//                break;
//            }
//        }
//        $miaosha_goods->attr = json_encode($attr_data, JSON_UNESCAPED_UNICODE);
//        $res = $miaosha_goods->save();
//        return $res;
//    }

}