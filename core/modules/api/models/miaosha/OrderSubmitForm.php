<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/17
 * Time: 11:48
 */

namespace app\modules\api\models\miaosha;


use app\models\Address;
use app\models\Attr;
use app\models\AttrGroup;
use app\models\Cart;
use app\models\Coupon;
use app\models\Goods;
use app\models\Level;
use app\models\MiaoshaGoods;
use app\models\MsGoods;
use app\models\MsOrder;
use app\models\MsSetting;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderForm;
use app\models\PostageRules;
use app\models\PrinterSetting;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\modules\api\controllers\OrderController;
use app\extensions\PinterOrder;
use app\modules\api\models\Model;
use yii\helpers\VarDumper;

class OrderSubmitForm extends Model
{
    public $store_id;
    public $user_id;
    public $version;

    public $address_id;
    public $cart_id_list;
    public $goods_info;

    public $user_coupon_id;

    public $content;
    public $offline;
    public $address_name;
    public $address_mobile;
    public $shop_id;

    public $use_integral;

    public $form;//自定义表单信息

    public $payment;

    public function rules()
    {
        return [
            [['cart_id_list', 'goods_info', 'content', 'address_name', 'address_mobile'], 'string'],
            [['address_id',], 'required', 'on' => "EXPRESS"],
            [['address_name', 'address_mobile'], 'required', 'on' => "OFFLINE"],
            [['user_coupon_id', 'offline', 'shop_id', 'use_integral','payment'], 'integer'],
            [['offline'], 'default', 'value' => 0],
            [['payment'], 'default', 'value' => 0],
            [['form'],'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'address_id' => '收货地址',
            'address_name' => '收货人',
            'address_mobile' => '联系电话'
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $t = \Yii::$app->db->beginTransaction();
        $express_price = 0;
//        $form = json_decode($this->form,true);
//        $form_list = $form['list'];
//        if($form['is_form'] == 1){
//            foreach($form_list as $index=>$value){
//                if($value['required'] == 1){
//                    if(in_array($value['type'],['radio','checkbox'])){
//                        $is_true = false;
//                        foreach($value['default_list'] as $k=>$v){
//                            if($v['is_selected'] == 1){
//                                $is_true = true;
//                            }
//                        }
//                        if(!$is_true){
//                            return [
//                                'code'=>1,
//                                'msg'=>'请填写'.$form['name'].'，加“*”为必填项',
//                                'name'=>$value['name']
//                            ];
//                        }
//                    }else{
//                        if(!$value['default'] && $value['default'] != 0){
//                            return [
//                                'code'=>1,
//                                'msg'=>'请填写'.$form['name'].'，加“*”为必填项',
//                                'name'=>$value['name']
//                            ];
//                        }
//                    }
//                }
//                if(in_array($value['type'],['radio','checkbox'])){
//                    $d = [];
//                    foreach($value['default_list'] as $k=>$v){
//                        if($v['is_selected'] == 1){
//                            $d[] = $v['name'];
//                        }
//                    }
//                    $form_list[$index]['default'] = implode(',',$d);
//                }
//            }
//        }
        if ($this->offline == 0) {
            $address = Address::findOne([
                'id' => $this->address_id,
                'store_id' => $this->store_id,
                'user_id' => $this->user_id,
            ]);
            if (!$address) {
                return [
                    'code' => 1,
                    'msg' => '收货地址不存在',
                ];
            }

//            $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id);
        }else{
            if(!preg_match_all("/1\d{10}/",$this->address_mobile,$arr)){
                return [
                    'code'=>1,
                    'msg'=>'手机号格式错误'
                ];
            }
        }
        $goods_list = [];
        $total_price = 0;

        $resIntegral = [
            'forehead' => 0,
            'forehead_integral' => 0,
        ];
        $goodsIds = [];
        $store = Store::findOne($this->store_id);
        $data = $this->getGoodsListByGoodsInfo($this->goods_info);
        $goods_list = empty($data['list']) ? [] : $data['list'];
        $total_price = empty($data['total_price']) ? 0 : $data['total_price'];
        $resIntegral = [
            'forehead' => 0,
            'forehead_integral' => 0,
        ];
        foreach ($goods_list as $k => $val) {
            $goods = MsGoods::findOne([
                'id' => $val->goods_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'status' => 1,
            ]);

            // 积分
            $integral = json_decode($goods->integral);
            if ($integral) {
                // 从购物车中获取商品规格信息使用
                $cart = Cart::find()->where([
                    'store_id' => $this->store_id,
                    'user_id' => $this->user_id,
                    'is_delete' => 0,
                    'id' => $val->cart_id,
                ])->one();
                $goods_attr_info = $goods->getAttrInfo(json_decode($cart->attr, true));

                $give = $integral->give;
                if (strpos($give, '%') !== false) {
                    // 百分比
                    $give = trim($give, '%');
                    $goods_list[$k]->give = (int)($val->price * ($give / 100));
                } else {
                    // 固定积分
                    $goods_list[$k]->give = (int)($give * $val->num);
                }
                if ($this->use_integral == '1') {
                    $forehead = $integral->forehead;
                    if (strpos($forehead, '%') !== false) {
                        $forehead = trim($forehead, '%');
                        if ($forehead >= 100) {
                            $forehead = 100;
                        }
                        if ($integral->more == '1') {
                            $resIntegral['forehead_integral'] = (int)(($forehead / 100) * $val->price * $store->integral);
                        } else {
                            $resIntegral['forehead_integral'] = (int)(($forehead / 100) * $val->price / $val->num * $store->integral);
                        }
                    } else {
//                            if ($integral->more == '1') {
//                                $resIntegral['forehead'] = sprintf("%.2f", ($forehead * $val->price));
//                            } else {
//                                $resIntegral['forehead'] = sprintf("%.2f", ($forehead * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price'])));
//                            }
                        if ($integral->more == '1') {
                            $resIntegral['forehead_integral'] = (int)($store->integral * $val->price);
//                    $resIntegral['forehead'] = sprintf("%.2f", ($store->integral * $goodsPrice));
                            if ($val->price > ($forehead * $val->num)) {
                                $resIntegral['forehead_integral'] = (int)(($forehead * $val->num) * $store->integral);
                            }
                        } else {
                            $goodsPrice = $val->price / $val->num;
                            $resIntegral['forehead_integral'] = (int)($store->integral * $goodsPrice);
                            if ($goodsPrice > $forehead) {
                                $resIntegral['forehead_integral'] = (int)($store->integral * $forehead);
                            }
                        }
                    }
//                        $resIntegral['forehead_integral'] = $resIntegral['forehead'] * $store->integral;
                    $resIntegral['forehead'] = sprintf("%.2f", ($resIntegral['forehead_integral'] / $store->integral));
//                        $resIntegral['forehead_integral'] = ceil($resIntegral['forehead']）
                }
            }
            if ($this->offline == 0) {
                if ($goods['full_cut']) {
                    $full_cut = json_decode($goods['full_cut'], true);
                } else {
                    $full_cut = json_decode([
                        'pieces' => 0,
                        'forehead' => 0,
                    ], true);
                }

                if ((empty($full_cut['pieces']) || $val->num < ($full_cut['pieces'] ?: 0)) && (empty($full_cut['forehead']) || $val->price < ($full_cut['forehead'] ?: 0))) {
                    $express_price = PostageRules::getExpressPrice($this->store_id, $address->city_id, $goods, $val->num, $address->province_id);
                }
//                    $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id, $goods,$val->num);
            }
        }

        if (empty($goods_list)) {
            return [
                'code' => 1,
                'msg' => '订单提交失败，所选商品库存不足或已下架',
            ];
        }

        $total_price_1 = $total_price + $express_price;

        // 获取用户当前积分
        $user = User::findOne(['id' => $this->user_id, 'type' => 1, 'is_delete' => 0]);
        if ($user->integral < $resIntegral['forehead_integral']) {
            $resIntegral['forehead_integral'] = $user->integral;
            $resIntegral['forehead'] = sprintf("%.2f", $user->integral / $store->integral);
        }

        $order = new MsOrder();
        $order->store_id = $this->store_id;
        $order->user_id = $this->user_id;
        $order->order_no = $this->getOrderNo();

        //此处计算所有的优惠措施
        $total_price_2 = $total_price; //实际支付金额
        //减去 优惠券（不含运费）

        if ($this->user_coupon_id && $goods->coupon == 1) {
            $coupon = Coupon::find()->alias('c')
                ->leftJoin(['uc' => UserCoupon::tableName()], 'uc.coupon_id=c.id')
                ->where([
                    'uc.id' => $this->user_coupon_id,
                    'uc.is_delete' => 0,
                    'uc.is_use' => 0,
                    'uc.is_expire' => 0,
                    'uc.user_id' => $this->user_id
                ])
                ->select('c.*')->one();
            $goods_total_pay_price = $total_price_2;//原本需支付的商品总价
            if ($coupon && $goods_total_pay_price >= $coupon->min_price) {
                $goods_price = $goods_total_pay_price - $coupon->sub_price;
                $total_price_2 = max(0.01, round($goods_price, 2));
                $order->coupon_sub_price = $goods_total_pay_price - max(0.01, $goods_total_pay_price - $coupon->sub_price);
                UserCoupon::updateAll(['is_use' => 1], ['id' => $this->user_coupon_id]);
                $order->user_coupon_id = $this->user_coupon_id;
            }
        }
        // 减去 积分折扣金额 （不含运费）
        $total_price_2 = $total_price_2 - $resIntegral['forehead'];

        //减去折扣（不含运费）
        if ($goods->is_discount == 1){
            $level = Level::find()->where(['store_id' => $this->store_id, 'level' => \Yii::$app->user->identity->level])->asArray()->one();
            if ($level) {
                $discount = $level['discount'];
            } else {
                $discount = 10;
            }
            $total_price_2 = max(0.01,round($total_price_2 * $discount / 10, 2));
        }

        //计算全局满额包邮
        $postage = Option::get('postage',$this->store_id,'admin',-1);
        if(floatval($postage) > -1 && floatval($postage) <= $total_price){
            $order->express_price_1 = $express_price;
            $express_price = 0;
        }
        $total_price_3 = $total_price_2 + $express_price;//实际支付金额加上运费


        $order->total_price = $total_price_1;
        $order->pay_price = $total_price_3 < 0.01 ? 0.01 : $total_price_3;
        $order->express_price = $express_price;
        $order->discount = $discount;
        $order->addtime = time();
        if ($this->offline == 0) {
            $order->address = $address->province . $address->city . $address->district . $address->detail;
            $order->mobile = $address->mobile;
            $order->name = $address->name;
            $order->address_data = json_encode([
                'province' => $address->province,
                'city' => $address->city,
                'district' => $address->district,
                'detail' => $address->detail,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $order->name = $this->address_name;
            $order->mobile = $this->address_mobile;
            $order->shop_id = $this->shop_id;
        }
        $order->first_price = 0;
        $order->second_price = 0;
        $order->third_price = 0;
        $order->content = $this->content;
        $order->is_offline = $this->offline;
        $order->integral = json_encode($resIntegral, JSON_UNESCAPED_UNICODE);
        $order->goods_id = $goods_list[0]->goods_id;
        $order->attr = json_encode($goods_list[0]->attr_list, JSON_UNESCAPED_UNICODE);
        $order->pic = $goods_list[0]->goods_pic;
        $order->integral_amount = $goods_list[0]->give;
        $order->num = $goods_list[0]->num;

        $msSetting = MsSetting::findOne($this->store_id);
        $unpaid = 1;
        if ($msSetting){
            $unpaid = $msSetting->unpaid;
        }
        $order->limit_time = time()+($unpaid*60);

        if($this->payment == 2){
            $order->pay_type = 2;
            $order->is_pay = 1;
        }
        if($this->payment == 3){
            $order->pay_type = 3;
            $order->is_pay = 0;
        }

        if ($order->save()) {
//            foreach($form_list as $index=>$value){
//                $order_form = new OrderForm();
//                $order_form->store_id = $this->store_id;
//                $order_form->order_id = $order->id;
//                $order_form->key = $value['name'];
//                $order_form->value = $value['default'];
//                $order_form->is_delete = 0;
//                $order_form->save();
//            }

            // 减去当前用户账户积分
            if ($resIntegral['forehead_integral'] > 0) {
                $user->integral -= $resIntegral['forehead_integral'];
                $user->save();
            }
            $goods_total_pay_price = $order->pay_price - $order->express_price;
            $goods_total_price = 0.00;
            foreach ($goods_list as $goods) {
                $goods_total_price += $goods->price;
            }
            foreach ($goods_list as $goods) {
                $attr_id_list = [];
                foreach ($goods->attr_list as $item) {
                    array_push($attr_id_list, $item['attr_id']);
                }
                $_goods = MsGoods::findOne($goods->goods_id);
                if (!$_goods->numSub($attr_id_list, $goods->num)) {
                    $t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => '订单提交失败，商品“' . $_goods->name . '”库存不足',
                        'attr_id_list' => $attr_id_list,
                        'attr_list' => $goods->attr_list,
                    ];
                }
            }
            $printer_setting = PrinterSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0]);
            $type = json_decode($printer_setting->type, true);
            if ($type['order'] && $type['order'] == 1) {
                $printer_order = new PinterOrder($this->store_id, $order->id);
                $res = $printer_order->print_order();
            }
            $t->commit();
            return [
                'code' => 0,
                'msg' => '订单提交成功',
                'data' => (object)[
                    'order_id' => $order->id,
                ],
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }
    }


    /**
     * @param string $goods_info
     * eg.{"goods_id":"22","attr":[{"attr_group_id":1,"attr_group_name":"颜色","attr_id":3,"attr_name":"橙色"},{"attr_group_id":2,"attr_group_name":"尺码","attr_id":2,"attr_name":"M"}],"num":1}
     */
    private function getGoodsListByGoodsInfo($goods_info)
    {
        $goods_info = json_decode($goods_info);
        $miaosha_goods = MiaoshaGoods::findOne([
            'id' => $goods_info->goods_id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'start_time' => date('H'),
        ]);
        if (!$miaosha_goods) {
            return [
                'total_price' => 0,
                'list' => [],
            ];
        }
        $goods = MsGoods::findOne([
            'id' => $miaosha_goods->goods_id,
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'status' => 1,
        ]);
        if (!$goods) {
            return [
                'total_price' => 0,
                'list' => [],
            ];
        }
        $attr_id_list = [];
        foreach ($goods_info->attr as $item) {
            array_push($attr_id_list, $item->attr_id);
        }
        $total_price = 0;
        $goods_attr_info = $goods->getAttrInfo($attr_id_list);

        $attr_list = Attr::find()->alias('a')
            ->select('a.id attr_id,ag.attr_group_name,a.attr_name')
            ->leftJoin(['ag' => AttrGroup::tableName()], 'a.attr_group_id=ag.id')
            ->where(['a.id' => $attr_id_list])
            ->asArray()->all();
        $goods_pic = isset($goods_attr_info['pic']) ? $goods_attr_info['pic'] ?: $goods->cover_pic : $goods->cover_pic;
        $goods_item = (object)[
            'goods_id' => $goods->id,
            'goods_name' => $goods->name,
            'goods_pic' => $goods_pic,
//            'goods_pic' => $goods->getGoodsPic(0)->pic_url,
            'num' => $goods_info->num,
            'price' => doubleval(empty($goods_attr_info['price']) ? $goods->original_price : $goods_attr_info['price']) * $goods_info->num,
            'attr_list' => $attr_list,
            'give' => 0,
        ];

        //秒杀价计算
        $miaosha_data = $this->getMiaoshaData($goods, $attr_id_list);
        if ($miaosha_data) {
            $res = $this->getMiaoshaPrice($miaosha_data, $goods, $attr_id_list, $goods_info->num);
            if ($res !== false) {
                $goods_item->price = $res['total_price'];
                $this->setMiaoshaSellNum($miaosha_data['id'], $attr_id_list, $res['miaosha_price_num']);
            }
        }

        $total_price += $goods_item->price;
        return [
            'total_price' => $total_price,
            'list' => [$goods_item],
        ];
    }

    public function getOrderNo()
    {
        $store_id = empty($this->store_id) ? 0 : $this->store_id;
        $order_no = null;
        while (true) {
            $order_no = 'M'.date('YmdHis') . rand(100000, 999999);
            $exist_order_no = MsOrder::find()->where(['order_no' => $order_no])->exists();
            if (!$exist_order_no)
                break;
        }
        return $order_no;
    }


    /**
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @return array ['id'=>'miaosha_goods id','attr_list'=>[],'miaosha_price'=>'秒杀价格','miaosha_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     */
    private function getMiaoshaData($goods, $attr_id_list = [])
    {
        $miaosha_goods = MiaoshaGoods::findOne([
            'goods_id' => $goods->id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'start_time' => intval(date('H')),
        ]);
        if (!$miaosha_goods)
            return null;
        $attr_data = json_decode($miaosha_goods->attr, true);
        sort($attr_id_list);
        $miaosha_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = $item['attr_id'];
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $miaosha_data = $attr_data_item;
                break;
            }
        }
        $miaosha_data['id'] = $miaosha_goods->id;
        return $miaosha_data;
    }

    /**
     * 获取商品秒杀价格，若库存不足则使用商品原价，若有部分库存，则部分数量使用秒杀价，部分使用商品原价，商品库存不足返回false
     * @param array $miaosha_data ['attr_list'=>[],'miaosha_price'=>'秒杀价格','miaosha_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @param integer $buy_num 购买数量
     *
     * @return false|array
     */
    private function getMiaoshaPrice($miaosha_data, $goods, $attr_id_list, $buy_num)
    {
        $attr_data = json_decode($goods->attr, true);
        sort($attr_id_list);
        $goost_attr_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = intval($item['attr_id']);
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $goost_attr_data = $attr_data_item;
                break;
            }
        }
        $goods_price = $goost_attr_data['price'];
        if (!$goods_price)
            $goods_price = $goods->original_price;

        $miaosha_price = min($miaosha_data['miaosha_price'], $goods_price);

        if ($buy_num > $goost_attr_data['num'])//商品库存不足
        {
            \Yii::warning([
                'res' => '库存不足',
                'm_data' => $miaosha_data,
                'g_data' => $goost_attr_data,
                '$attr_id_list' => $attr_id_list,
            ]);
            return false;
        }

        if ($buy_num <= ($miaosha_data['miaosha_num'] - $miaosha_data['sell_num'])) {
            \Yii::warning([
                'res' => '库存充足',
                'price' => $buy_num * $miaosha_price,
                'm_data' => $miaosha_data,
            ]);
            return [
                'miaosha_price_num' => $buy_num,
                'original_price_num' => 0,
                'total_price' => $buy_num * $miaosha_price
            ];
        }

        $miaosha_num = ($miaosha_data['miaosha_num'] - $miaosha_data['sell_num']);
        $original_num = $buy_num - $miaosha_num;

        \Yii::warning([
            'res' => '部分充足',
            'price' => $miaosha_num * $miaosha_price + $original_num * $goods_price,
            'm_data' => $miaosha_data,
        ]);

        return [
            'miaosha_price_num' => $miaosha_num,
            'original_price_num' => $original_num,
            'total_price' => $miaosha_num * $miaosha_price + $original_num * $goods_price,
        ];
    }

    private function setMiaoshaSellNum($miaosha_goods_id, $attr_id_list, $num)
    {
        $miaosha_goods = MiaoshaGoods::findOne($miaosha_goods_id);
        if (!$miaosha_goods)
            return false;
        sort($attr_id_list);
        $attr_data = json_decode($miaosha_goods->attr, true);
        foreach ($attr_data as $i => $attr_row) {
            $_tmp_attr_id_list = [];
            foreach ($attr_row['attr_list'] as $attr) {
                $_tmp_attr_id_list[] = intval($attr['attr_id']);
            }
            sort($_tmp_attr_id_list);
            if ($_tmp_attr_id_list == $attr_id_list) {
                $attr_data[$i]['sell_num'] = intval($attr_data[$i]['sell_num']) + intval($num);
                break;
            }
        }
        $miaosha_goods->attr = json_encode($attr_data, JSON_UNESCAPED_UNICODE);
        $res = $miaosha_goods->save();
        return $res;
    }

}