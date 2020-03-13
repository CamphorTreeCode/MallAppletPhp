<?php

/**
 * Created by PhpStorm.
 * User: zc
 * Date: 2018/2/5
 * Time: 14:19
 */
namespace app\modules\api\models\quick;
use app\models\Goods;
use app\models\GoodsCat;
use app\modules\api\models\Model;
use app\models\Cat;


class QuickForm extends Model
{
    public $user_id;
    public $store_id;

    public function goods()
    {
//        查分类
        $list = Cat::find()
            ->select(['id','parent_id','name'])
            ->where([
                'store_id'=>$this->store_id,
                'is_delete'=>0,
                'is_show'=>1,
                'parent_id'=>0,
            ])->orderBy('sort ASC')
            ->asArray()->all();
//二级分类
        foreach ($list as $key => &$value)
        {
            $twolist = Cat::find()
                ->select(['id','parent_id','name'])
                ->where([
                    'store_id'=>$this->store_id,
                    'is_delete'=>0,
                    'is_show'=>1,
                    'parent_id'=>$value['id'],
                ])->asArray()->all();
            $value['twolist'] = $twolist;
        }
        //            一级商品
        foreach ($list as $key => &$value) {
            $goods = GoodsCat::find()
                ->where([
                    'store_id' => $this->store_id,
                    'is_delete' => 0,
                    'cat_id' => $value['id'],
                ])->with(['goods' => function ($query) {
                    $query->where([
                        'store_id' => $this->store_id,
                        'status' => 1,
                        'is_delete' => 0,
                        'quick_purchase' => 1,
                    ]);
                }])
                ->asArray()->all();
            foreach ($goods as $key1 => $value1) {
                if($value1['goods'] != null){
                    $value['goods'][] = $value1['goods'];
                }
            }
        }
        foreach ($list as $key => &$value) {
            foreach ($value['twolist'] as $key1 => &$value1) {
                $twogoods = GoodsCat::find()
                    ->where([
                        'store_id' => $this->store_id,
                        'is_delete' => 0,
                        'cat_id' => $value1['id'],
                    ])->with(['goods' => function ($query) {
                        $query->where([
                            'store_id' => $this->store_id,
                            'status' => 1,
                            'is_delete' => 0,
                            'quick_purchase' => 1,
                        ]);
                    }])
                    ->asArray()->all();
                foreach ($twogoods as $key2 => $value2) {
                    if($value2['goods'] != null){
                        $value['twogoods'][] = $value2['goods'];
                    }
                }
            }
        }
        foreach ($list as $key => &$value) {
            unset($value['twolist']);
            if(isset($value['goods']) && isset($value['twogoods'])){
                $value['goods'] = array_merge($value['goods'],$value['twogoods']);
            }else if(isset($value['twogoods'])){
                $value['goods'] = $value['twogoods'];
            }else if(isset($value['goods'])){
                $value['goods'] = $value['goods'];
            }else{
                $value['goods'] = [];
            }
        }
        foreach ($list as $key => &$value){
            unset($value['twogoods']);
            foreach($value['goods'] as $key1 => &$value1){
                $value1['num'] = 0;
            }
        }
        return [
            'code' => 0,
            'data' => [
                'list' => $list,
            ],
        ];
    }
}