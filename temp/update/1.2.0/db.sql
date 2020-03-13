CREATE TABLE `hjmall_qrcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `qrcode_bg` longtext NOT NULL COMMENT '背景图片',
  `avatar_size` varchar(255) NOT NULL COMMENT '头像大小{"w":"","h":""}',
  `avatar_position` varchar(255) NOT NULL COMMENT '头像坐标{"x":"","y":""}',
  `qrcode_size` varchar(255) NOT NULL COMMENT '二维码宽度',
  `qrcode_position` varchar(255) NOT NULL COMMENT '二维码坐标{"x":"","y":""}',
  `font_position` varchar(255) NOT NULL COMMENT '字体坐标{"x":"","y":""}',
  `font` longtext NOT NULL COMMENT '字体设置\r\n{\r\n  "size":大小,\r\n  "color":"r,g,b"\r\n}',
  `preview` longtext NOT NULL COMMENT '预览图',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='海报图的设置';

CREATE TABLE `hjmall_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first` decimal(10,2) NOT NULL DEFAULT '0.00',
  `second` decimal(10,2) NOT NULL DEFAULT '0.00',
  `third` decimal(10,2) NOT NULL DEFAULT '0.00',
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城id',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '分销层级 0--不开启 1--一级分销 2--二级分销 3--三级分销',
  `condition` int(11) NOT NULL DEFAULT '0' COMMENT '成为下线条件 0--首次点击分享链接 1--首次下单 2--首次付款',
  `share_condition` int(11) NOT NULL DEFAULT '0' COMMENT '成为分销商的条件 \r\n0--无条件\r\n1--申请',
  `content` longtext COMMENT '分销佣金 的 用户须知',
  `pay_type` smallint(1) NOT NULL DEFAULT '0' COMMENT '提现方式 0--支付宝转账  1--微信企业支付',
  `min_money` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '最小提现额度',
  `agree` longtext COMMENT '分销协议',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商佣金设置';

CREATE TABLE `hjmall_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL COMMENT '审核状态 0--未审核 1--审核通过 2--审核不通过',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商';

CREATE TABLE `hjmall_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '申请状态 0--申请中 1--确认申请 2--已打款 3--驳回',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `pay_time` int(11) NOT NULL COMMENT '付款',
  `type` smallint(1) NOT NULL DEFAULT '0' COMMENT '支付方式 0--微信支付  1--支付宝 2--手动',
  `mobile` varchar(255) DEFAULT NULL COMMENT '支付宝账号',
  `name` varchar(255) DEFAULT NULL COMMENT '支付宝姓名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='提现表';


CREATE TABLE `hjmall_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rgb` varchar(255) NOT NULL COMMENT 'rgb颜色码 例如："0，0，0"',
  `color` varchar(255) NOT NULL COMMENT '16进制颜色码例如：#ffffff',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='颜色库';

ALTER TABLE `hjmall_user`
ADD COLUMN `is_distributor`  int NOT NULL DEFAULT 0 COMMENT '是否是分销商 0--不是 1--是 2--申请中' AFTER `store_id`,
ADD COLUMN `parent_id`  int NOT NULL DEFAULT 0 COMMENT '父级ID' AFTER `is_distributor`,
ADD COLUMN `time`  int NOT NULL DEFAULT 0 COMMENT '成为分销商的时间' AFTER `parent_id`,
ADD COLUMN `total_price`  decimal(10,2) NOT NULL DEFAULT 0 COMMENT '累计佣金' AFTER `time`,
ADD COLUMN `price`  decimal(10,2) NOT NULL DEFAULT 0 COMMENT '可提现佣金' AFTER `total_price`;


ALTER TABLE `hjmall_store`
ADD COLUMN `delivery_time`  int(11) NOT NULL DEFAULT 10 COMMENT '收货时间' AFTER `copyright_url`,
ADD COLUMN `after_sale_time`  int(11) NOT NULL DEFAULT 7 COMMENT '售后时间' AFTER `delivery_time`;

ALTER TABLE `hjmall_order`
ADD COLUMN `is_price`  smallint(1) NOT NULL DEFAULT 0 COMMENT '是否发放佣金' AFTER `is_delete`,
ADD COLUMN `parent_id`  int(11) NOT NULL DEFAULT 0 COMMENT '用户上级ID' AFTER `is_price`,
ADD COLUMN `first_price`  decimal(10,2) NOT NULL COMMENT '一级佣金' AFTER `parent_id`,
ADD COLUMN `second_price`  decimal(10,2) NOT NULL COMMENT '二级佣金' AFTER `first_price`,
ADD COLUMN `third_price`  decimal(10,2) NOT NULL COMMENT '三级佣金' AFTER `second_price`;


DROP TABLE IF EXISTS `hjmall_order_comment`;
CREATE TABLE `hjmall_order_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_detail_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` decimal(10,1) NOT NULL COMMENT '评分：1=差评，2=中评，3=好',
  `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '评价内容',
  `pic_list` longtext COMMENT '图片',
  `is_hide` smallint(6) NOT NULL DEFAULT '0' COMMENT '是否隐藏：0=不隐藏，1=隐藏',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单评价';

ALTER TABLE `hjmall_store`
ADD COLUMN `use_wechat_platform_pay`  smallint NOT NULL DEFAULT 0 COMMENT '是否使用公众号支付：0=否，1=是';

ALTER TABLE `hjmall_order`
ADD COLUMN `is_comment`  smallint(1) NOT NULL DEFAULT 0 COMMENT '是否已评价：0=未评价，1=已评价' AFTER `confirm_time`;
ALTER TABLE `hjmall_order`
ADD COLUMN `apply_delete`  smallint(1) NOT NULL DEFAULT 0 COMMENT '是否申请取消订单：0=否，1=申请取消订单' AFTER `is_comment`;

DROP TABLE IF EXISTS `hjmall_article`;
CREATE TABLE `hjmall_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `article_cat_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id：1=关于我们，2=服务中心',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` longtext COMMENT '内容',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序：升序',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统文章';


INSERT INTO `hjmall_color` VALUES ('1', '{\"r\":\"51\",\"g\":\"51\",\"b\":\"51\"}', '#333333', '0', '0');
INSERT INTO `hjmall_color` VALUES ('2', '{\"r\":\"255\",\"g\":\"69\",\"b\":\"68\"}', '#ff4544', '0', '0');
INSERT INTO `hjmall_color` VALUES ('3', '{\"r\":\"255\",\"g\":\"255\",\"b\":\"255\"}', '#ffffff', '0', '0');
INSERT INTO `hjmall_color` VALUES ('4', '{\"r\":\"239\",\"g\":\"174\",\"b\":\"57\"}', '#EFAE39', '0', '0');
INSERT INTO `hjmall_color` VALUES ('6', '{\"r\":\"88\",\"g\":\"228\",\"b\":\"88\"}', '#58E458', '0', '0');
INSERT INTO `hjmall_color` VALUES ('7', '{\"r\":\"9\",\"g\":\"122\",\"b\":\"220\"}', '#097ADC', '0', '0');
INSERT INTO `hjmall_color` VALUES ('8', '{\"r\":\"164\",\"g\":\"62\",\"b\":\"228\"}', '#A43EE4', '0', '0');
