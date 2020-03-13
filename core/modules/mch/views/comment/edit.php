<?php
defined('YII_RUN') or exit('Access Denied');

$urlManager = Yii::$app->urlManager;
$this->title = '专题分类编辑'; 
$this->params['active_nav_group'] = 8; 

use yii\widgets\ActiveForm;
use \app\models\Option;
?> 

<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="form auto-form" method="post" return="<?= $urlManager->createUrl(['mch/comment/index']) ?>">
            <div class="form-group row" style="<?php if(!$model->id){echo 'display:none';}else{echo 'display:display'; } ?>"" >
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">ID</label>
                </div>
                <div class="col-sm-6">
                 <div class="col-form-label required"><?= $model->id ?></div>
             </div>
        </div>

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label required">虚拟用户名</label>
            </div>
            <div class="col-sm-6">
                <input class="form-control" name="virtual_user" value="<?= $model->virtual_user ?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label">用户头像</label>
            </div>
            <div class="col-sm-6">
                <div class="upload-group">
                    <div class="input-group">
                        <input class="form-control file-input" name="virtual_avatar"
                        value="<?= $model->virtual_avatar ?>">
                        <span class="input-group-btn">
                            <a class="btn btn-secondary upload-file" href="javascript:" data-toggle="tooltip"
                            data-placement="bottom" title="上传文件">
                                <span class="iconfont icon-cloudupload"></span>
                            </a>
                        </span>
                        <span class="input-group-btn">
                            <a class="btn btn-secondary select-file" href="javascript:" data-toggle="tooltip"
                                data-placement="bottom" title="从文件库选择">
                                <span class="iconfont icon-viewmodule"></span>
                            </a>
                        </span>
                        <span class="input-group-btn">
                            <a class="btn btn-secondary delete-file" href="javascript:" data-toggle="tooltip"
                            data-placement="bottom" title="删除文件">
                            <span class="iconfont icon-close"></span>
                            </a>
                        </span>
                    </div>
                    <div class="upload-preview text-center upload-preview">
                        <span class="upload-preview-tip">100&times;100</span>
                        <img class="upload-preview-img" src="<?= $model->virtual_avatar ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label">商品</label>
            </div>
            <div class="col-sm-6">
                <select class="form-control" name="goods_id">
                    <?php foreach($select as $v): ?>
                        <?php if(strlen($v->name)>40): ?>                    
                            <option value="<?= $v->value?>" <?= $model->goods_id == $v->value ? 'selected' : null ?>><?= mb_substr($v->name,0,40).'...';?></option>
                        <?php else: ?>
                           <option value="<?= $v->value?>" <?= $model->goods_id == $v->value ? 'selected' : null ?>><?= $v->name?></option>
                       <?php endif; ?>
                   <?php endforeach; ?>
               </select>
           </div>
        </div>

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label required">评价</label>
            </div>
            <div class="col-sm-6">
                <textarea class="form-control file-input" name="content" rows="3"><?= $model->content ?></textarea>
            </div>
        </div> 

        <div class="form-group row">
            <div class="form-group-label col-sm-2 text-right">
                <label class="col-form-label">评价图片</label>
            </div>
            <div class="col-sm-6">

                <div class="upload-group multiple short-row">
                    <div class="input-group">
                        <input class="form-control file-input" readonly>
                        <span class="input-group-btn">
                            <a class="btn btn-secondary upload-file" href="javascript:"
                            data-toggle="tooltip"
                            data-placement="bottom" title="上传文件">
                                <span class="iconfont icon-cloudupload"></span>
                            </a>
                        </span>
                        <span class="input-group-btn">
                            <a class="btn btn-secondary select-file" href="javascript:"
                            data-toggle="tooltip"
                            data-placement="bottom" title="从文件库选择">
                                <span class="iconfont icon-viewmodule"></span>
                            </a>
                        </span>
                    </div>
                    <div class="upload-preview-list">
                        <?php if (count(json_decode($model->pic_list)) > 0): ?>
                            <?php foreach (json_decode($model->pic_list) as $item): ?>
                                <div class="upload-preview text-center">
                                    <input type="hidden" class="file-item-input"
                                    name="pic_list[]"
                                    value="<?= $item ?>">
                                    <span class="file-item-delete">&times;</span>
                                    <span class="upload-preview-tip">750&times;750</span>
                                    <img class="upload-preview-img" src="<?= $item ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="upload-preview text-center">
                                <input type="hidden" class="file-item-input" name="pic_list[]">
                                <span class="file-item-delete">&times;</span>
                                <span class="upload-preview-tip">750&times;750</span>
                                <img class="upload-preview-img" src="">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">评分</label>
        </div>
        <div class="col-sm-6">
            <label class="radio-label">
                <input <?= $model->score == 1 ? 'checked' : null ?>
                value="1"
                name="score" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">差评</span>
            </label>
            <label class="radio-label">
                <input <?= $model->score == 2 ? 'checked' : null ?>
                value="2"
                name="score" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">中评</span>
            </label>
            <label class="radio-label">
                <input <?= $model->score == 3 ? 'checked' : null ?>
                value="3"
                name="score" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">好评</span>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
            <label class="col-form-label required">是否隐藏</label>
        </div>
        <div class="col-sm-6">

            <label class="radio-label">
                <input <?= $model->is_hide == 0 ? 'checked' : null ?>
                value="0"
                name="is_hide" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">显示</span>
            </label>
            <label class="radio-label">
                <input <?= $model->is_hide == 1 ? 'checked' : null ?>
                value="1"
                name="is_hide" type="radio" class="custom-control-input">
                <span class="label-icon"></span>
                <span class="label-text">隐藏</span>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <div class="form-group-label col-sm-2 text-right">
        </div>
        <div class="col-sm-6">
            <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
        </div>
    </div>
</form>


</div>
</div>
<script>
</script>