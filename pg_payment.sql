/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : pg_payment

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-12-12 16:43:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pg_admin
-- ----------------------------
DROP TABLE IF EXISTS `pg_admin`;
CREATE TABLE `pg_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '用户手机号',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '状态：0禁用，1启用,',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除，0-否，1-是',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
-- Table structure for pg_channel
-- ----------------------------
DROP TABLE IF EXISTS `pg_channel`;
CREATE TABLE `pg_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '支付名称',
  `val` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '支付渠道值',
  `english_name` varchar(50) NOT NULL DEFAULT '' COMMENT '支付渠道英文名称',
  `service_name` varchar(50) NOT NULL DEFAULT '' COMMENT '服务名称',
  `member_id` varchar(30) NOT NULL DEFAULT '' COMMENT '商户号',
  `key` varchar(255) NOT NULL DEFAULT '' COMMENT '支付渠道的key值',
  `pay_url` varchar(70) NOT NULL DEFAULT '' COMMENT '该支付接口的支付地址',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启，1-是，0-否',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除，1-是，0-否',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COMMENT='支付渠道主表';

-- ----------------------------
-- Table structure for pg_channel_info
-- ----------------------------
DROP TABLE IF EXISTS `pg_channel_info`;
CREATE TABLE `pg_channel_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '渠道ID',
  `pay_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式ID',
  `pay_name` varchar(255) NOT NULL DEFAULT '' COMMENT '支付值',
  `min` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '最小支付额度(单位分，该渠道下支付方式)',
  `max` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '最大支付额度(单位分，该渠道下支付方式)',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1-开启，0-关闭',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除，0-否，1-是',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COMMENT='渠道信息表';

-- ----------------------------
-- Table structure for pg_payment
-- ----------------------------
DROP TABLE IF EXISTS `pg_payment`;
CREATE TABLE `pg_payment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号ID',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额(单位分)',
  `card_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付卡类型 1:储蓄卡 2:信用卡',
  `pay_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT 'pay_type：1：网银支付、2：快捷支付、3：快捷H5、4：微信H5支付、5：微信扫码、6：微信公众号、7：支付宝H5支付、8：支付宝扫码支付、9：京东H5支付、10：京东钱包支付、11：京东扫码、12：银联H5支付、13：银联扫码支付、14：QQ钱包支付、15：QQ扫码支付、16：QQH5支付',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态 0：发起支付  1：支付完成  2：支付失败',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '调用支付平台类型；1：银河支付 2：Wispay支付 3：杉德支付 4 全时支付',
  `from_type` varchar(50) NOT NULL DEFAULT '' COMMENT '来源 ',
  `trans_date` varchar(8) NOT NULL DEFAULT '' COMMENT '交易日期(yyyymmdd)',
  `trans_time` varchar(8) NOT NULL DEFAULT '' COMMENT '交易时间(HHmmss)',
  `channel` char(10) NOT NULL DEFAULT '' COMMENT '来源类型 ',
  `created_at` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `order_id_2` (`order_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=615 DEFAULT CHARSET=utf8mb4 COMMENT='支付记录表';

-- ----------------------------
-- Table structure for pg_pay_type
-- ----------------------------
DROP TABLE IF EXISTS `pg_pay_type`;
CREATE TABLE `pg_pay_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '支付类型的名称',
  `val` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型值',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启，0-否，1-是',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除，0-否，1-是',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COMMENT='支付类型表';

-- ----------------------------

