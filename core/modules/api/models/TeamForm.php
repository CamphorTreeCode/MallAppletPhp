<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 16:24
 */

namespace app\modules\api\models;


use app\models\Goods;
use app\models\GoodsPic;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Setting;
use app\models\Share;
use app\models\User;
use app\models\UserShareMoney;
use yii\data\Pagination;
use yii\helpers\VarDumper;

class TeamForm extends Model
{
    public $user_id;
    public $store_id;

    public $status;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit', 'status',], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 10],
            [['status'], 'in', 'range' => [1, 2, 3], 'on' => 'TEAM'],
            [['status'], 'in', 'range' => [-1, 0, 1, 2], 'on' => 'ORDER'],
        ];
    }

    //处理我的团队信息
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $exit = Share::find()->andWhere(['user_id' => $this->user_id, 'is_delete' => 0])->exists();
        $user = User::findOne(['id' => $this->user_id]);
        $share_setting = Setting::findOne(['store_id' => $this->store_id]);
        if ($share_setting->level == 0) {
            return [
                'code' => 1,
                'msg' => '网络异常',
                'data' => []
            ];
        }
        if (!$exit || $user->is_distributor != 1) {
            return [
                'code' => 1,
                'msg' => '网络异常',
                'data' => []
            ];
        }

        $team = self::team($this->store_id, $this->user_id);
        $user_list = $team[1];
        $data = $team[0];


        if ($share_setting->level > 0 && $this->status == 1) {
            $data['list'] = $user_list['f_c'];
        }
        if ($this->status == 2 && $share_setting->level > 1) {
            $data['list'] = $user_list['s_c'];
        }
        if ($this->status == 3 && $share_setting->level > 2) {
            $data['list'] = $user_list['t_c'];
        }
        foreach ($data['list'] as $index => $value) {
            $data['list'][$index]['time'] = date('Y-m-d', $value['addtime']);
            $child_count = User::find()->where(['parent_id' => $value['id'], 'is_delete' => 0])->count();
            $data['list'][$index]['child_count'] = $child_count ? $child_count : 0;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => $data
        ];
    }

    //获取我的团队信息
    public static function team($store_id, $user_id)
    {
        $share_setting = Setting::findOne(['store_id' => $store_id]);
        $list = User::find()->alias('u')
            ->where(['and', ['u.is_delete' => 0, 'u.store_id' => $store_id], ['>', 'u.parent_id', 0]])
            ->leftJoin(Order::tableName() . ' o', "o.is_price=1 and o.user_id=u.id and o.parent_id = u.parent_id")
            ->andWhere([
                'or',
                ['o.is_delete' => 0, 'o.is_cancel' => 0],
                'isnull(o.id)'
            ])
            ->select([
                "sum(case when isnull(o.id) then 0 else o.pay_price end) price",
                'count(o.id) count',
                'u.nickname', 'u.addtime', 'u.parent_id', 'u.id', 'u.avatar_url'
            ])
            ->groupBy('u.id')
            ->asArray()->all();
        $user_list = array();
        $data = [];
        $data['first'] = 0;
        $data['second'] = 0;
        $data['third'] = 0;
        $data['list'] = [];
        $user_list['f_c'] = [];
        $user_list['s_c'] = [];
        $user_list['t_c'] = [];
        //获取用户下线的数量及订单情况
        foreach ($list as $index => $value) {
            if ($value['parent_id'] == $user_id) {
                $data['first']++;
                $user_list['f_c'][] = $value;
                if ($share_setting->level > 1) {
                    foreach ($list as $i => $v) {
                        if ($v['parent_id'] == $value['id']) {
                            $data['second']++;
                            $user_list['s_c'][] = $v;
                            if ($share_setting->level > 2) {
                                foreach ($list as $j => $item) {
                                    if ($item['parent_id'] == $v['id']) {
                                        $data['third']++;
                                        $user_list['t_c'][] = $item;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return [$data, $user_list];
    }

    /**
     * @return array
     * 获取分销订单明细
     */
    public function GetOrder()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $team = self::team($this->store_id, $this->user_id);
        $share_setting = Setting::findOne(['store_id' => $this->store_id]);
        $user_list = $team[1];
        $team_arr = [];
        $team_arr['id'] = [];
        $team_arr['id_1'] = [];
        $team_arr['f_c'] = [];
        $team_arr['s_c'] = [];
        $team_arr['t_c'] = [];
        $team_arr['id_1'][] = (string)$this->user_id;
        foreach ($user_list as $index => $value) {
            foreach ($value as $i => $v) {
                $team_arr['id'][] = $v['id'];
                $team_arr[$index][] = $v['id'];
                if($index != 't_c'){
                    $team_arr['id_1'][] = $v['id'];
                }
            }
        }
        if ($this->limit == -1) {
            $first_price = Order::find()->alias('o')->where([
                'o.is_delete' => 0, 'o.is_cancel' => 0, 'o.store_id' => $this->store_id
            ])->andWhere([
                'or',
                ['and',['in', 'o.user_id', $team_arr['f_c']],['o.parent_id'=>$this->user_id,'o.parent_id_1'=>0]],
                ['o.parent_id' => $this->user_id],
            ])->select(['sum(first_price)'])->scalar();
            $second_price = Order::find()->alias('o')->where([
                'o.is_delete' => 0, 'o.is_cancel' => 0, 'o.store_id' => $this->store_id
            ])->andWhere([
                'or',
                ['and',['in', 'o.user_id', $team_arr['s_c']],['o.parent_id'=>$team_arr['f_c'],'o.parent_id_1'=>0]],
                ['o.parent_id_1' => $this->user_id],
            ])->select(['sum(second_price)'])->scalar();
            $third_price = Order::find()->alias('o')->where([
                'o.is_delete' => 0, 'o.is_cancel' => 0, 'o.store_id' => $this->store_id
            ])->andWhere([
                'or',
                ['and',['in', 'o.user_id', $team_arr['t_c']],['o.parent_id'=>$team_arr['s_c'],'o.parent_id_1'=>0]],
                ['o.parent_id_2' => $this->user_id],
            ])->select(['sum(third_price)'])->scalar();
            $order_money = 0;
            if ($first_price) {
                $order_money += doubleval($first_price);
            }
            if ($second_price) {
                $order_money += doubleval($second_price);
            }
            if ($third_price) {
                $order_money += doubleval($third_price);
            }
            return doubleval(sprintf('%.2f', $order_money));
        }
        $query = Order::find()->alias('o')
            ->select([
                'o.*',
                'u.nickname', 'u.avatar_url'
            ])
            ->where(['o.is_delete' => 0, 'o.is_cancel' => 0, 'o.store_id' => $this->store_id])
            ->joinWith('orderDetail')
            ->leftJoin(User::tableName() . ' u', 'o.user_id=u.id ')
            ->andWhere(['or',
                ['and', ['in', 'o.user_id', $team_arr['id']], ['o.parent_id_1'=>0,'o.parent_id'=>$team_arr['id_1']]],
                ['o.parent_id' => $this->user_id],
                ['o.parent_id_1' => $this->user_id],
                ['o.parent_id_2' => $this->user_id],
            ])->andWhere(['od.is_delete' => 0]);
        if ($this->status == 0) {//待付款
            $query->andWhere(['o.is_pay' => 0]);
        }
        if ($this->status == 1) {//已付款
            $query->andWhere(['o.is_pay' => 1, 'o.is_price' => 0]);
        }
        if ($this->status == 2) {//已完成
            $query->andWhere(['o.is_price' => 1]);
        }
        $query->groupBy('o.id');
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $query->limit($pagination->limit)->offset($pagination->offset);
        $list = $query->orderBy('o.addtime DESC')->asArray()->all();

        $new_list = [];
        foreach ($list as $index => $value) {
            if ($value['parent_id_1'] == 0) {
                if (!in_array($value['user_id'], $team_arr['id']) && !in_array($value['parent_id'],$team_arr['id_1'])) {
                    continue;
                }
            }
            $new_list[$index]['order_no'] = $value['order_no'];
            $new_list[$index]['nickname'] = $value['nickname'];
            $new_list[$index]['avatar_url'] = $value['avatar_url'];
            $new_list[$index]['status'] = "待付款";
            $new_list[$index]['is_price'] = $value['is_price'];
            $refund = OrderRefund::findOne(['order_id' => $value['id'], 'is_delete' => 0, 'store_id' => $this->store_id]);
            if ($value['is_pay'] == 0) {
                $new_list[$index]['status'] = "待付款";
            } elseif ($value['is_pay'] == 1 && $value['is_price'] == 0) {
                $new_list[$index]['status'] = "已付款";
                if ($refund) {
                    if ($refund['status'] == 1)
                        $new_list[$index]['status'] = "已退款";
                    elseif ($refund['status'] == 0) {
                        $new_list[$index]['status'] = '售后申请中';
                    }
                }
            } elseif ($value['is_price'] == 1) {
                $new_list[$index]['status'] = "已完成";
            }
            foreach ($value['orderDetail'] as $i => $v) {
                $new_list[$index]['orderDetail'][$i]['num'] = $v['num'];
                $new_list[$index]['orderDetail'][$i]['name'] = $v['name'];
                $new_list[$index]['orderDetail'][$i]['goods_pic'] = Goods::getGoodsPicStatic($v['goods_id'])->pic_url;
            }

            if ($value['parent_id_1'] == 0) {
                if ($this->user_id == $value['parent_id']) {
                    $new_list[$index]['share_status'] = $share_setting->first_name ? $share_setting->first_name : "一级";
                    $new_list[$index]['share_money'] = $value['first_price'];
                } elseif (in_array($value['user_id'], $team_arr['s_c']) && in_array($value['parent_id'],$team_arr['f_c'])) {
                    $new_list[$index]['share_status'] = $share_setting->second_name ? $share_setting->second_name : "二级";
                    $new_list[$index]['share_money'] = $value['second_price'];
                } elseif (in_array($value['user_id'], $team_arr['t_c']) && in_array($value['parent_id'],$team_arr['s_c'])) {
                    $new_list[$index]['share_status'] = $share_setting->third_name ? $share_setting->third_name : "三级";
                    $new_list[$index]['share_money'] = $value['third_price'];
                }
            } else {
                if ($value['parent_id'] == $this->user_id) {
                    $new_list[$index]['share_status'] = $share_setting->first_name ? $share_setting->first_name : "一级";
                    $new_list[$index]['share_money'] = $value['first_price'];
                } elseif ($value['parent_id_1'] == $this->user_id) {
                    $new_list[$index]['share_status'] = $share_setting->second_name ? $share_setting->second_name : "二级";
                    $new_list[$index]['share_money'] = $value['second_price'];
                } elseif ($value['parent_id_2'] == $this->user_id) {
                    $new_list[$index]['share_status'] = $share_setting->third_name ? $share_setting->third_name : "三级";
                    $new_list[$index]['share_money'] = $value['third_price'];
                }
            }
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => $new_list,
        ];
    }

}