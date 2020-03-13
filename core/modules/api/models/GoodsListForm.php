<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/1
 * Time: 23:33
 */

namespace app\modules\api\models;


use app\models\Cat;
use app\models\Goods;
use app\models\GoodsCat;
use app\models\GoodsPic;
use app\models\Order;
use app\models\OrderDetail;
use yii\data\Pagination;

class GoodsListForm extends Model
{
    public $store_id;
    public $keyword;
    public $cat_id;
    public $page;
    public $limit;

    public $sort;
    public $sort_type;


    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['store_id', 'cat_id', 'page', 'limit',], 'integer'],
            [['limit',], 'integer',],
            [['limit',], 'default', 'value' => 12],
            [['sort', 'sort_type',], 'integer',],
            [['sort',], 'default', 'value' => 0],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Goods::find()->alias('g')->where([
            'g.status' => 1,
            'g.is_delete' => 0,
        ]);
        if ($this->store_id)
            $query->andWhere(['g.store_id' => $this->store_id]);
        if ($this->cat_id) {
//            $query->leftJoin(['gc' => GoodsCat::tableName()], 'gc.goods_id=g.id and gc.is_delete = 0');
            $cat = Cat::find()->select('id')->where(['is_delete' => 0, 'parent_id' => $this->cat_id]);
            $gc_query = GoodsCat::find()->where(['or',['cat_id'=>$this->cat_id],['cat_id'=>$cat]]);
            $query->leftJoin(['gc' => $gc_query], 'gc.goods_id=g.id and gc.is_delete = 0');
            $query->andWhere(
                [
                    'OR',
                    ['g.cat_id' => $this->cat_id],
                    ['g.cat_id' => $cat],
                    ['gc.cat_id' => $this->cat_id],
                    ['gc.cat_id' => $cat],
                ]
            );
        }
        if ($this->keyword)
            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        if ($this->sort == 0) {
            //综合，自定义排序+时间最新
            $query->orderBy('g.sort ASC, g.addtime DESC');
        }
        if ($this->sort == 1) {
            //时间最新
            $query->orderBy('g.addtime DESC');
        }
        if ($this->sort == 2) {
            //价格
            if ($this->sort_type == 0) {
                $query->orderBy('g.price ASC');
            } else {
                $query->orderBy('g.price DESC');
            }
        }
        if ($this->sort == 3) {
            //销量
            $query->orderBy([
                '( IF(gn.num, gn.num, 0) + virtual_sales)' => SORT_DESC,
                'g.addtime' => SORT_DESC,
            ]);
        }

        $od_query = OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where(['od.is_delete' => 0, 'o.store_id' => $this->store_id, 'o.is_pay' => 1, 'o.is_delete' => 0])->groupBy('od.goods_id')->select('SUM(od.num) num,od.goods_id');

        $list = $query
            ->leftJoin(['gn' => $od_query], 'gn.goods_id=g.id')
            ->select('g.id,g.name,g.price,g.original_price,g.cover_pic pic_url,gn.num,g.virtual_sales,g.unit')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->groupBy('g.id')->all();

        foreach ($list as $i => $item) {
            if (!$item['pic_url']) {
                $list[$i]['pic_url'] = Goods::getGoodsPicStatic($item['id'])->pic_url;
            }
            $list[$i]['sales'] = $this->numToW($item['num'] + $item['virtual_sales']) . $item['unit'];
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }

    private function numToW($sales)
    {
        if ($sales < 10000) {
            return $sales;
        } else {
            return round($sales / 10000, 2) . 'W';
        }
    }
}