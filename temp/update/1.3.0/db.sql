/* UPDATE V1.3.0 */

CREATE TABLE `hjmall_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '优惠券名称',
  `desc` varchar(2000) NOT NULL DEFAULT '',
  `pic_url` varchar(2000) NOT NULL DEFAULT '',
  `discount_type` smallint(6) NOT NULL DEFAULT '1' COMMENT '优惠券类型：1=折扣，2=满减',
  `min_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最低消费金额',
  `sub_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `discount` decimal(3,1) NOT NULL DEFAULT '10.0' COMMENT '折扣率',
  `expire_type` smallint(1) NOT NULL DEFAULT '1' COMMENT '到期类型：1=领取后N天过期，2=指定有效期',
  `expire_day` int(11) NOT NULL DEFAULT '0' COMMENT '有效天数，expire_type=1时',
  `begin_time` int(11) NOT NULL DEFAULT '0' COMMENT '有效期开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '有效期结束时间',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='优惠券';

CREATE TABLE `hjmall_coupon_auto_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `event` int(11) NOT NULL DEFAULT '1' COMMENT '触发事件：1=分享，2=购买并付款',
  `send_times` int(11) NOT NULL DEFAULT '1' COMMENT '最多发放次数，0表示不限制',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='优惠券自动发放';


DROP TABLE IF EXISTS `hjmall_express`;
CREATE TABLE `hjmall_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT '100',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '数据类型：kdniao=快递鸟',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='快递公司';

INSERT INTO `hjmall_express` VALUES ('1', '顺丰快递', 'SF', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('2', '申通快递', 'STO', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('3', '韵达快递', 'YD', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('4', '圆通速递', 'YTO', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('5', '中通速递', 'ZTO', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('6', '百世快递', 'HTKY', '1', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('7', 'EMS', 'EMS', '2', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('8', '天天快递', 'HHTT', '2', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('9', '邮政平邮/小包', 'YZPY', '2', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('10', '宅急送', 'ZJS', '2', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('11', '国通快递', 'GTO', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('12', '全峰快递', 'QFKD', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('13', '优速快递', 'UC', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('14', '中铁快运', 'ZTKY', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('15', '中铁物流', 'ZTWL', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('16', '亚马逊物流', 'AMAZON', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('17', '城际快递', 'CJKD', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('18', '德邦', 'DBL', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('19', '汇丰物流', 'HFWL', '5', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('20', '百世快运', 'BTWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('21', '安捷快递', 'AJ', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('22', '安能物流', 'ANE', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('23', '安信达快递', 'AXD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('24', '北青小红帽', 'BQXHM', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('25', '百福东方', 'BFDF', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('26', 'CCES快递', 'CCES', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('27', '城市100', 'CITY100', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('28', 'COE东方快递', 'COE', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('29', '长沙创一', 'CSCY', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('30', '成都善途速运', 'CDSTKY', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('31', 'D速物流', 'DSWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('32', '大田物流', 'DTWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('33', '快捷速递', 'FAST', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('34', 'FEDEX联邦(国内件）', 'FEDEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('35', 'FEDEX联邦(国际件）', 'FEDEX_GJ', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('36', '飞康达', 'FKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('37', '广东邮政', 'GDEMS', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('38', '共速达', 'GSD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('39', '高铁速递', 'GTSD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('40', '恒路物流', 'HLWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('41', '天地华宇', 'HOAU', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('42', '华强物流', 'hq568', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('43', '华夏龙物流', 'HXLWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('44', '好来运快递', 'HYLSD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('45', '京广速递', 'JGSD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('46', '九曳供应链', 'JIUYE', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('47', '佳吉快运', 'JJKY', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('48', '嘉里物流', 'JLDT', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('49', '捷特快递', 'JTKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('50', '急先达', 'JXD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('51', '晋越快递', 'JYKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('52', '加运美', 'JYM', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('53', '佳怡物流', 'JYWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('54', '跨越物流', 'KYWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('55', '龙邦快递', 'LB', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('56', '联昊通速递', 'LHT', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('57', '民航快递', 'MHKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('58', '明亮物流', 'MLWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('59', '能达速递', 'NEDA', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('60', '平安达腾飞快递', 'PADTF', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('61', '全晨快递', 'QCKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('62', '全日通快递', 'QRT', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('63', '如风达', 'RFD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('64', '赛澳递', 'SAD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('65', '圣安物流', 'SAWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('66', '盛邦物流', 'SBWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('67', '上大物流', 'SDWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('68', '盛丰物流', 'SFWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('69', '盛辉物流', 'SHWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('70', '速通物流', 'ST', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('71', '速腾快递', 'STWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('72', '速尔快递', 'SURE', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('73', '唐山申通', 'TSSTO', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('74', '全一快递', 'UAPEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('75', '万家物流', 'WJWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('76', '万象物流', 'WXWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('77', '新邦物流', 'XBWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('78', '信丰快递', 'XFEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('79', '希优特', 'XYT', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('80', '新杰物流', 'XJ', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('81', '源安达快递', 'YADEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('82', '远成物流', 'YCWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('83', '义达国际物流', 'YDH', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('84', '越丰物流', 'YFEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('85', '原飞航物流', 'YFHEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('86', '亚风快递', 'YFSD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('87', '运通快递', 'YTKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('88', '亿翔快递', 'YXKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('89', '增益快递', 'ZENY', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('90', '汇强快递', 'ZHQKD', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('91', '众通快递', 'ZTE', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('92', '中邮物流', 'ZYWL', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('93', '速必达物流', 'SUBIDA', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('94', '瑞丰速递', 'RFEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('95', '快客快递', 'QUICK', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('96', 'CNPEX中邮快递', 'CNPEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('97', '鸿桥供应链', 'HOTSCM', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('98', '海派通物流公司', 'HPTEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('99', '澳邮专线', 'AYCA', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('100', '泛捷快递', 'PANEX', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('101', 'PCA Express', 'PCA', '100', 'kdniao', '0');
INSERT INTO `hjmall_express` VALUES ('102', 'UEQ Express', 'UEQ', '100', 'kdniao', '0');


CREATE TABLE `hjmall_sms_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(255) DEFAULT NULL,
  `tpl` varchar(255) DEFAULT NULL,
  `content` varchar(1000) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `addtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='短信发送记录';


CREATE TABLE `hjmall_sms_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `AccessKeyId` varchar(255) DEFAULT NULL COMMENT '阿里云AccessKeyId',
  `AccessKeySecret` varchar(255) DEFAULT NULL COMMENT '阿里云AccessKeySecret',
  `name` varchar(255) DEFAULT NULL COMMENT '短信模板名称',
  `sign` varchar(255) DEFAULT NULL COMMENT '短信模板签名',
  `tpl` varchar(255) DEFAULT NULL COMMENT '短信模板code',
  `msg` varchar(255) DEFAULT NULL COMMENT '短信模板参数',
  `status` smallint(6) NOT NULL DEFAULT '0' COMMENT '开启状态 0--关闭 1--开启',
  `mobile` varchar(255) DEFAULT NULL,
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='短信设置';


CREATE TABLE `hjmall_user_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `coupon_auto_send_id` int(11) NOT NULL DEFAULT '0' COMMENT '自动发放id',
  `begin_time` int(11) NOT NULL DEFAULT '0' COMMENT '有效期开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '有效期结束时间',
  `is_expire` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否已过期：0=未过期，1=已过期',
  `is_use` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否已使用：0=未使用，1=已使用',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='用户-优惠券关系';


ALTER TABLE `hjmall_store`
ADD COLUMN `kdniao_mch_id`  varchar(255) NOT NULL DEFAULT '' COMMENT '快递鸟商户号',
ADD COLUMN `kdniao_api_key`  varchar(255) NOT NULL DEFAULT '' COMMENT '快递鸟api key';

ALTER TABLE `hjmall_order`
ADD COLUMN `coupon_sub_price`  decimal(10,2) NOT NULL DEFAULT 0 COMMENT '优惠券抵消金额';



