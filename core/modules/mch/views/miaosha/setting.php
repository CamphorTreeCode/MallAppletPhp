<?php
defined('YII_RUN') or exit('Access Denied');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 11:36
 */

$urlManager = Yii::$app->urlManager;
$this->title = '秒杀设置';
$this->params['active_nav_group'] = 10;
$this->params['is_book'] = 1;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="form auto-form" method="post" autocomplete="off"
              return="<?= $urlManager->createUrl(['mch/miaosha/setting']) ?>">
            <div class="form-body">
                <div class="form-group row">
                    <div class="form-group-label col-3 text-right">
                        <label class=" col-form-label">未支付订单取消时间</label>
                    </div>
                    <div class="col-5 col-form-label">
                        <div class="input-group">
                            <input class="form-control" type="number" name="model[unpaid]" value="<?=$setting->unpaid?:1?>">
                            <span class="input-group-addon">分钟</span>
                        </div>
                        <div class="text-muted fs-sm">注意：不设置默认一分钟自动取消</div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="form-group-label col-3 text-right">
                    </div>
                    <div class="col-9">
                        <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
