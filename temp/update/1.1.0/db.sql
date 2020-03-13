DROP TABLE IF EXISTS `hjmall_home_nav`;
CREATE TABLE `hjmall_home_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '图标名称',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '页面路径',
  `open_type` varchar(255) NOT NULL DEFAULT '' COMMENT '打开方式',
  `pic_url` longtext NOT NULL COMMENT '图标url',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序，升序',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='首页导航图标';


DROP TABLE IF EXISTS `hjmall_order_refund`;
CREATE TABLE `hjmall_order_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_detail_id` int(11) NOT NULL,
  `order_refund_no` varchar(255) NOT NULL DEFAULT '' COMMENT '退款单号',
  `type` smallint(6) NOT NULL DEFAULT '1' COMMENT '售后类型：1=退货退款，2=换货',
  `refund_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `desc` varchar(500) NOT NULL DEFAULT '' COMMENT '退款说明',
  `pic_list` longtext COMMENT '凭证图片列表：json格式',
  `status` smallint(1) NOT NULL DEFAULT '0' COMMENT '状态：0=待商家处理，1=同意并已退款，2=已同意换货，3=已拒绝退换货',
  `refuse_desc` varchar(500) NOT NULL DEFAULT '' COMMENT '拒绝退换货原因',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  `response_time` int(11) NOT NULL DEFAULT '0' COMMENT '商家处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='售后订单';

ALTER TABLE `hjmall_postage_rules`
ADD COLUMN `express`  varchar(255) NOT NULL DEFAULT '' COMMENT '快递公司';

ALTER TABLE `hjmall_store` ADD COLUMN `copyright`  varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `hjmall_store` ADD COLUMN `copyright_pic_url`  varchar(1000) NOT NULL DEFAULT '';