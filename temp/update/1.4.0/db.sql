/* update v1.4.0 */

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `hjmall_home_block`;
CREATE TABLE `hjmall_home_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `data` longtext,
  `addtime` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COMMENT='首页自定义版块';

ALTER TABLE `hjmall_store`
ADD COLUMN `cat_style`  smallint NOT NULL DEFAULT 1 COMMENT '分类页面样式：1=无侧栏，2=有侧栏';

ALTER TABLE `hjmall_store`
ADD COLUMN `home_page_module`  longtext NULL COMMENT '首页模块布局';

ALTER TABLE `hjmall_cat`
ADD COLUMN `big_pic_url`  longtext NULL COMMENT '分类大图';

ALTER TABLE `hjmall_setting`
ADD COLUMN `first_name`  varchar(255) NULL,
ADD COLUMN `second_name`  varchar(255) NULL,
ADD COLUMN `third_name`  varchar(255) NULL,
ADD COLUMN `pic_url_1`  longtext NULL,
ADD COLUMN `pic_url_2`  longtext NULL;

ALTER TABLE `hjmall_goods`
ADD COLUMN `sort`  int(11) NULL COMMENT '排序  升序';
