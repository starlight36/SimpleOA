/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50515
Source Host           : localhost:3306
Source Database       : oa

Target Server Type    : MYSQL
Target Server Version : 50515
File Encoding         : 65001

Date: 2012-05-29 17:08:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oa_form`
-- ----------------------------
DROP TABLE IF EXISTS `oa_form`;
CREATE TABLE `oa_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `process_id` int(11) NOT NULL COMMENT '关联的流程ID',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '表单名称',
  `form_key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci COMMENT '表单简介',
  PRIMARY KEY (`id`),
  KEY `fk_form_process_id` (`process_id`),
  CONSTRAINT `fk_form_process_id` FOREIGN KEY (`process_id`) REFERENCES `oa_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='表单表';

-- ----------------------------
-- Records of oa_form
-- ----------------------------
INSERT INTO `oa_form` VALUES ('3', '1', '常规请假单', 'leave_application', '常规的请假申请，请填写此表单，注意事由等必须表述清晰。');

-- ----------------------------
-- Table structure for `oa_form_field`
-- ----------------------------
DROP TABLE IF EXISTS `oa_form_field`;
CREATE TABLE `oa_form_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '字段说明',
  `form_id` int(11) NOT NULL COMMENT '所属的表单的ID',
  `field_key` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段键名',
  `type` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段类型',
  `config` text COLLATE utf8_unicode_ci,
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必填字段',
  `default_value` text COLLATE utf8_unicode_ci COMMENT '字段默认值',
  `validator` text COLLATE utf8_unicode_ci COMMENT '有效性规则验证器',
  PRIMARY KEY (`id`),
  KEY `fk_form_field_form_id` (`form_id`),
  KEY `idx_form_field_key` (`field_key`) USING BTREE,
  CONSTRAINT `fk_form_field_form_id` FOREIGN KEY (`form_id`) REFERENCES `oa_form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='字段表';

-- ----------------------------
-- Records of oa_form_field
-- ----------------------------
INSERT INTO `oa_form_field` VALUES ('4', '申请人', '请假申请人姓名', '3', 'applicant', 'text', '', '1', '', '');
INSERT INTO `oa_form_field` VALUES ('5', '开始时间', '请假开始的时间', '3', 'start_time', 'datetime', '', '1', '', '');
INSERT INTO `oa_form_field` VALUES ('6', '返岗时间', '预计请假结束销假的时间', '3', 'end_time', 'datetime', '', '1', '', '');
INSERT INTO `oa_form_field` VALUES ('7', '请假类型', '选择所请假期的类型', '3', 'leave_type', 'radio', '年假,事假,病假,实习生返校', '1', '', '');
INSERT INTO `oa_form_field` VALUES ('8', '请假原因', '详细描述请假的原因', '3', 'reason', 'richtext', '', '1', '', '');

-- ----------------------------
-- Table structure for `oa_organization`
-- ----------------------------
DROP TABLE IF EXISTS `oa_organization`;
CREATE TABLE `oa_organization` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组织ID序号',
  `parent_id` int(11) DEFAULT '0' COMMENT '父组织序号, 0为根节点',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '组织结构名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '组织结构说明',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组织结构状态: 0 - 正常; 1 - 已删除.',
  PRIMARY KEY (`id`),
  KEY `fk_organization_parent_id` (`parent_id`),
  CONSTRAINT `fk_organization_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `oa_organization` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='组织结构表';

-- ----------------------------
-- Records of oa_organization
-- ----------------------------
INSERT INTO `oa_organization` VALUES ('0', null, '系统管理员', '系统内建的组织机构，不隶属于任何企业部门，仅用于系统内部管理。', '0');
INSERT INTO `oa_organization` VALUES ('1', '0', '斯科特信息技术有限公司', '斯科特信息技术有限公司', '0');
INSERT INTO `oa_organization` VALUES ('2', '1', '总裁办', '决策部门', '0');
INSERT INTO `oa_organization` VALUES ('3', '1', '行政部', '处理日常行政工作的部门', '0');
INSERT INTO `oa_organization` VALUES ('4', '1', '企业解决方案事业部', '构建企业业务解决方案', '0');
INSERT INTO `oa_organization` VALUES ('5', '1', '欧美外包事业部', '面向欧美的外包事业部', '0');
INSERT INTO `oa_organization` VALUES ('6', '1', '日韩外包事业部', '面向日韩的外包事业部', '0');
INSERT INTO `oa_organization` VALUES ('7', '1', '销售部', '软件产品销售部门', '0');
INSERT INTO `oa_organization` VALUES ('8', '1', '客户服务部', '负责面向客户的服务', '0');
INSERT INTO `oa_organization` VALUES ('9', '3', '人力资源部', '负责公司人力资源事务', '0');
INSERT INTO `oa_organization` VALUES ('10', '3', '财务部', '企业财务部门', '0');
INSERT INTO `oa_organization` VALUES ('11', '3', '信息部', '内部信息技术支持和管理部门', '0');
INSERT INTO `oa_organization` VALUES ('12', '3', '审计部', '公司内部审计部门', '0');
INSERT INTO `oa_organization` VALUES ('13', '8', '电话客服部', '提供电话支持的部门', '0');

-- ----------------------------
-- Table structure for `oa_process`
-- ----------------------------
DROP TABLE IF EXISTS `oa_process`;
CREATE TABLE `oa_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '工作流ID',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '工作流名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '工作流描述',
  `cate_id` int(11) NOT NULL COMMENT '所属分类的ID',
  `create_time` int(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '工作流状态: 0 - 未启用; 1 - 已启用; 2 - 已废弃.',
  PRIMARY KEY (`id`),
  KEY `fk_process_cate_id` (`cate_id`),
  CONSTRAINT `fk_process_cate_id` FOREIGN KEY (`cate_id`) REFERENCES `oa_process_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='流程表';

-- ----------------------------
-- Records of oa_process
-- ----------------------------
INSERT INTO `oa_process` VALUES ('1', '常规请假', '用于请假申请的业务', '1', '0', '1');

-- ----------------------------
-- Table structure for `oa_process_acl`
-- ----------------------------
DROP TABLE IF EXISTS `oa_process_acl`;
CREATE TABLE `oa_process_acl` (
  `process_id` int(11) NOT NULL COMMENT '关联的流程ID',
  `role_id` int(11) NOT NULL COMMENT '可访问的角色ID',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '访问权限类型标识符: 0 - 无权限, 1 - 监督流程, 2 - 创建任务 3 - 两者兼具; ',
  PRIMARY KEY (`process_id`,`role_id`),
  KEY `fk_process_acl_process_id` (`process_id`),
  KEY `fk_process_acl_role_id` (`role_id`),
  CONSTRAINT `fk_process_acl_process_id` FOREIGN KEY (`process_id`) REFERENCES `oa_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_process_acl_role_id` FOREIGN KEY (`role_id`) REFERENCES `oa_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='流程访问控制表';

-- ----------------------------
-- Records of oa_process_acl
-- ----------------------------
INSERT INTO `oa_process_acl` VALUES ('1', '0', '3');
INSERT INTO `oa_process_acl` VALUES ('1', '7', '2');

-- ----------------------------
-- Table structure for `oa_process_category`
-- ----------------------------
DROP TABLE IF EXISTS `oa_process_category`;
CREATE TABLE `oa_process_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '流程分类名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '流程分类简介',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='流程分类';

-- ----------------------------
-- Records of oa_process_category
-- ----------------------------
INSERT INTO `oa_process_category` VALUES ('0', '废弃流程', '归档已经废弃但不能被删除的流程');
INSERT INTO `oa_process_category` VALUES ('1', '行政事务', '行政事务相关的流程');
INSERT INTO `oa_process_category` VALUES ('2', '业务分类', '业务分类');

-- ----------------------------
-- Table structure for `oa_process_node`
-- ----------------------------
DROP TABLE IF EXISTS `oa_process_node`;
CREATE TABLE `oa_process_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '流程节点名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '流程节点介绍',
  `node_key` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '流程键名',
  `process_id` int(11) NOT NULL COMMENT '关联的流程ID',
  `actor` int(11) NOT NULL DEFAULT '0' COMMENT '参与者角色, 0为系统角色',
  `goto_exp` text COLLATE utf8_unicode_ci COMMENT '后继表达式, 用于描述接下来执行的流程',
  `action_exp` text COLLATE utf8_unicode_ci NOT NULL COMMENT '操作表达式: 允许的流程操作,如 办结, 退回, 跳转, 结束',
  PRIMARY KEY (`id`),
  KEY `fk_process_node_process_id` (`process_id`),
  KEY `fk_process_node_role_id` (`actor`),
  CONSTRAINT `fk_process_node_process_id` FOREIGN KEY (`process_id`) REFERENCES `oa_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_process_node_role_id` FOREIGN KEY (`actor`) REFERENCES `oa_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='流程节点';

-- ----------------------------
-- Records of oa_process_node
-- ----------------------------
INSERT INTO `oa_process_node` VALUES ('2', '填写请假单', '请假者首先应当填写请假单', 'start', '1', '0', 'GO valid_leave', 'complete,jump,return,close');
INSERT INTO `oa_process_node` VALUES ('3', '审批请假', '核实请假事由，准予或者驳回请假申请', 'valid_leave', '1', '0', 'GO end', 'complete,return');

-- ----------------------------
-- Table structure for `oa_role`
-- ----------------------------
DROP TABLE IF EXISTS `oa_role`;
CREATE TABLE `oa_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色ID序号',
  `org_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属的组织',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `description` text COLLATE utf8_unicode_ci COMMENT '角色描述',
  `status` tinyint(4) DEFAULT NULL COMMENT '角色状态: 0 - 正常; 1 - 已删除.',
  PRIMARY KEY (`id`),
  KEY `fk_role_org_id` (`org_id`),
  CONSTRAINT `fk_role_org_id` FOREIGN KEY (`org_id`) REFERENCES `oa_organization` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色表';

-- ----------------------------
-- Records of oa_role
-- ----------------------------
INSERT INTO `oa_role` VALUES ('0', '0', '系统管理员', '系统管理员角色，系统内建用于进行系统全局管理的角色。', '0');
INSERT INTO `oa_role` VALUES ('1', '2', '总经理', '总经理角色', '0');
INSERT INTO `oa_role` VALUES ('3', '2', '副总经理', '副总经理角色', '0');
INSERT INTO `oa_role` VALUES ('4', '2', '总经理助理', '总经理助理角色', '0');
INSERT INTO `oa_role` VALUES ('5', '3', '行政部经理', '行政部经理', '0');
INSERT INTO `oa_role` VALUES ('6', '13', '电话接线员', '负责接听电话，和客户直接接触。', '0');
INSERT INTO `oa_role` VALUES ('7', '4', '开发工程师', '企业解决方案事业部的开发工程师', '0');

-- ----------------------------
-- Table structure for `oa_task`
-- ----------------------------
DROP TABLE IF EXISTS `oa_task`;
CREATE TABLE `oa_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `process_id` int(11) NOT NULL COMMENT '使用的流程的ID',
  `creator` int(11) NOT NULL COMMENT '任务创建者ID',
  `time` int(13) DEFAULT NULL COMMENT '创建时间',
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT '任务标题',
  `variable` text COLLATE utf8_unicode_ci COMMENT '任务计数器变量',
  `current_node` text COLLATE utf8_unicode_ci COMMENT '当前任务节点',
  `current_status` tinyint(4) DEFAULT '0' COMMENT '当前任务的状态: 0 - 准备; 1 - 进行中, 2 - 已完成',
  PRIMARY KEY (`id`),
  KEY `fk_task_process_id` (`process_id`),
  KEY `fk_task_creator_id` (`creator`),
  CONSTRAINT `fk_task_creator_id` FOREIGN KEY (`creator`) REFERENCES `oa_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_process_id` FOREIGN KEY (`process_id`) REFERENCES `oa_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='任务表';

-- ----------------------------
-- Records of oa_task
-- ----------------------------
INSERT INTO `oa_task` VALUES ('1', '1', '1', '1338085481', '测试任务', null, 'end', '2');
INSERT INTO `oa_task` VALUES ('2', '1', '1', '1338101676', '旅行年假申请', 'b:0;', 'end', '2');
INSERT INTO `oa_task` VALUES ('3', '1', '3', '1338280996', '申请年假', 'a:0:{}', 'end', '2');

-- ----------------------------
-- Table structure for `oa_task_data`
-- ----------------------------
DROP TABLE IF EXISTS `oa_task_data`;
CREATE TABLE `oa_task_data` (
  `task_id` int(11) NOT NULL COMMENT '任务ID',
  `form_id` int(11) NOT NULL COMMENT '表单ID',
  `field_key` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '字段标识符',
  `field_value` longtext COLLATE utf8_unicode_ci COMMENT '字段值',
  PRIMARY KEY (`task_id`,`form_id`,`field_key`),
  KEY `fk_task_data_task_id` (`task_id`),
  KEY `fk_task_data_form_id` (`form_id`),
  KEY `fk_task_data_field_key` (`field_key`),
  CONSTRAINT `fk_task_data_field_key` FOREIGN KEY (`field_key`) REFERENCES `oa_form_field` (`field_key`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_data_form_id` FOREIGN KEY (`form_id`) REFERENCES `oa_form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_data_task_id` FOREIGN KEY (`task_id`) REFERENCES `oa_task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='任务数据项表';

-- ----------------------------
-- Records of oa_task_data
-- ----------------------------
INSERT INTO `oa_task_data` VALUES ('1', '3', 'applicant', '111');
INSERT INTO `oa_task_data` VALUES ('1', '3', 'end_time', '5/31/2012 16:44:58');
INSERT INTO `oa_task_data` VALUES ('1', '3', 'leave_type', '实习生返校');
INSERT INTO `oa_task_data` VALUES ('1', '3', 'reason', '<strong>1111</strong>');
INSERT INTO `oa_task_data` VALUES ('1', '3', 'start_time', '5/28/2012 16:44:56');
INSERT INTO `oa_task_data` VALUES ('2', '3', 'applicant', '刘思贤');
INSERT INTO `oa_task_data` VALUES ('2', '3', 'end_time', '5/31/2012 11:57:08');
INSERT INTO `oa_task_data` VALUES ('2', '3', 'leave_type', '年假');
INSERT INTO `oa_task_data` VALUES ('2', '3', 'reason', '<span style=\"font-weight: bold;\">请假</span><br />');
INSERT INTO `oa_task_data` VALUES ('2', '3', 'start_time', '5/29/2012 11:57:05');
INSERT INTO `oa_task_data` VALUES ('3', '3', 'applicant', '刘思贤');
INSERT INTO `oa_task_data` VALUES ('3', '3', 'end_time', '6/1/2012 16:43:37');
INSERT INTO `oa_task_data` VALUES ('3', '3', 'leave_type', '年假');
INSERT INTO `oa_task_data` VALUES ('3', '3', 'reason', '申请年假啊<br />');
INSERT INTO `oa_task_data` VALUES ('3', '3', 'start_time', '5/23/2012 16:43:32');

-- ----------------------------
-- Table structure for `oa_task_procedure`
-- ----------------------------
DROP TABLE IF EXISTS `oa_task_procedure`;
CREATE TABLE `oa_task_procedure` (
  `procedure_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL COMMENT '工作任务ID',
  `process_node_id` int(11) NOT NULL DEFAULT '0' COMMENT '工作流程节点',
  `actor` int(11) NOT NULL COMMENT '参与者',
  `time` int(11) DEFAULT NULL COMMENT '执行时间',
  `result` text COLLATE utf8_unicode_ci NOT NULL COMMENT '处理结果',
  `message` text COLLATE utf8_unicode_ci COMMENT '处理意见',
  PRIMARY KEY (`procedure_id`),
  KEY `fk_task_procedure_task_id` (`task_id`),
  KEY `fk_task_procedure_node_id` (`process_node_id`),
  KEY `fk_task_procedure_actor` (`actor`),
  CONSTRAINT `fk_task_procedure_actor` FOREIGN KEY (`actor`) REFERENCES `oa_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_procedure_node_id` FOREIGN KEY (`process_node_id`) REFERENCES `oa_process_node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_procedure_task_id` FOREIGN KEY (`task_id`) REFERENCES `oa_task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of oa_task_procedure
-- ----------------------------
INSERT INTO `oa_task_procedure` VALUES ('1', '1', '2', '1', '1338210842', 'complete', '提交申请');
INSERT INTO `oa_task_procedure` VALUES ('2', '1', '3', '1', '1338211306', 'return', '请假多请点啊');
INSERT INTO `oa_task_procedure` VALUES ('3', '1', '2', '1', '1338211644', 'complete', '已修改');
INSERT INTO `oa_task_procedure` VALUES ('4', '1', '3', '1', '1338212087', 'complete', '批准了！');
INSERT INTO `oa_task_procedure` VALUES ('6', '2', '2', '1', '1338264056', 'complete', '申请请假，望批准');
INSERT INTO `oa_task_procedure` VALUES ('7', '2', '3', '1', '1338264072', 'complete', '同意');
INSERT INTO `oa_task_procedure` VALUES ('8', '3', '2', '3', '1338281069', 'complete', '请假单已填写');
INSERT INTO `oa_task_procedure` VALUES ('9', '3', '3', '1', '1338281743', 'complete', '批准请假');

-- ----------------------------
-- Table structure for `oa_user`
-- ----------------------------
DROP TABLE IF EXISTS `oa_user`;
CREATE TABLE `oa_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `number` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '员工号',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户真实姓名',
  `password` char(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '登录密码，32位MD5 加密两轮',
  `email` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '电子邮箱',
  `gender` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别：0 - 男; 1 - 女.',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户状态: 0 - 正常; 1 - 停用; 2 - 离职;',
  `create_time` int(13) DEFAULT NULL COMMENT '创建时间: unix时间戳.',
  `last_login_time` int(13) DEFAULT NULL COMMENT '最后登录时间, unix时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number_UNIQUE` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';

-- ----------------------------
-- Records of oa_user
-- ----------------------------
INSERT INTO `oa_user` VALUES ('1', 'sa', '超级管理员', '14e1b600b1fd579f47433b88e8d85291', 'webmaster@localhost', '0', '2012-05-09', '0', '0', '1338281101');
INSERT INTO `oa_user` VALUES ('2', 'admin', '管理员', '0b77520f93de693bdab0060746e38165', 'admin@localhost', '0', '2012-05-10', '0', '1336636240', '1336637223');
INSERT INTO `oa_user` VALUES ('3', 'dev1', '刘思贤', 'd9b1d7db4cd6e70935368a1efb10e377', 'starlight36@163.com', '0', '1991-07-17', '0', '1338280253', '1338281759');

-- ----------------------------
-- Table structure for `oa_user_role_relation`
-- ----------------------------
DROP TABLE IF EXISTS `oa_user_role_relation`;
CREATE TABLE `oa_user_role_relation` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `create_time` int(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_relation_user_id` (`user_id`),
  KEY `fk_relation_role_id` (`role_id`),
  CONSTRAINT `fk_relation_role_id` FOREIGN KEY (`role_id`) REFERENCES `oa_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_relation_user_id` FOREIGN KEY (`user_id`) REFERENCES `oa_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户角色关系表';

-- ----------------------------
-- Records of oa_user_role_relation
-- ----------------------------
INSERT INTO `oa_user_role_relation` VALUES ('1', '0', '1336636158');
INSERT INTO `oa_user_role_relation` VALUES ('2', '0', '1336700173');
INSERT INTO `oa_user_role_relation` VALUES ('3', '7', '1338280253');
