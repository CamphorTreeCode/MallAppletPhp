ALTER TABLE `hjmall_goods`
ADD COLUMN `service`  varchar(2000) NOT NULL DEFAULT '' COMMENT '商品服务选项';

ALTER TABLE `hjmall_store`
ADD COLUMN `copyright_url`  varchar(1000) NOT NULL DEFAULT '' COMMENT '版权的超链接';
