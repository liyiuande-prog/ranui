-- RanUI Blog 数据库结构及演示数据
-- 目标数据库: MySQL 5.7/8.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 表结构: options (系统设置表)
-- ----------------------------
DROP TABLE IF EXISTS `options`;
CREATE TABLE `options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(100) NOT NULL COMMENT '设置名',
  `option_value` longtext DEFAULT NULL COMMENT '设置值',
  `autoload` tinyint(1) DEFAULT '1' COMMENT '是否自动加载',
  PRIMARY KEY (`id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- ----------------------------
-- 演示数据: options
-- ----------------------------
INSERT INTO `options` (`option_name`, `option_value`, `autoload`) VALUES
('site_title', 'RanUI 社区', 1),
('site_keywords', 'RanUI, Ran, Blog, 社区', 1),
('site_description', 'RanUI 官方社区', 1),
('site_logo', '/logo.png', 1),
('site_ico', '/favicon.ico', 1),
('site_icp', '京ICP备88888888号', 1),
('social_qq', '123456', 1),
('social_wechat', 'ranui_official', 1),
('social_bilibili', '', 1),
('social_github', '', 1),
('site_maintenance', '0', 1),
('site_closed', '0', 1),
('upload_max_size_image', '2', 1),
('upload_max_size_video', '20', 1);


-- ----------------------------
-- 表结构: themes (主题表)
-- ----------------------------
DROP TABLE IF EXISTS `themes`;
CREATE TABLE `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '主题目录名 (唯一标识)',
  `version` varchar(20) DEFAULT '1.0.0',
  `is_active` tinyint(1) DEFAULT '0' COMMENT '是否启用',
  `config` longtext DEFAULT NULL COMMENT '主题专属配置(JSON)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='主题管理表';

-- ----------------------------
-- 演示数据: themes
-- ----------------------------
INSERT INTO `themes` (`name`, `version`, `is_active`, `config`) VALUES
('default', '1.0.0', 1, '{}');


-- ----------------------------
-- 表结构: plugins (插件表)
-- ----------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '插件目录名 (唯一标识)',
  `version` varchar(20) DEFAULT '1.0.0',
  `is_active` tinyint(1) DEFAULT '0' COMMENT '是否启用',
  `config` longtext DEFAULT NULL COMMENT '插件专属配置(JSON)',
  `installed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='插件管理表';



-- ----------------------------
-- 表结构: users (用户表)
-- ----------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(32) NOT NULL COMMENT '用户唯一标识 (仅允许字母、数字、_和-)',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `bio` text DEFAULT NULL COMMENT '个人简介',
  `email` varchar(100) NOT NULL COMMENT '邮箱 (用于找回密码)',
  `password` varchar(255) NOT NULL COMMENT '登录密码 (Hash)',
  `password_set` tinyint(1) DEFAULT '1' COMMENT '是否设置过密码',  -- 修复：补充字段类型+逗号
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像URL',  -- 现在能被正确解析
  `role` varchar(20) DEFAULT 'admin' COMMENT '角色: admin(管理员), editor(编辑)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `status` varchar(20) DEFAULT 'active' COMMENT '状态: active/banned/deleted/cancellation_pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户/管理员表';

-- ----------------------------
-- 演示数据: users - 同步补充 password_set 字段值
-- ----------------------------
INSERT INTO `users` (`id`, `uid`, `username`, `email`,`password`,`password_set`,`avatar`,`role`,`created_at`,`status`) VALUES
(1, '1', 'admin', 'admin@ranui.com','$2y$10$DiN1cgLgEFwgjvdhpHZ1c.Mw7GI0G5395D6PnNQ0oiHiJ5B70AsNW','1','/assets/default-avatar.png','admin',CURRENT_TIMESTAMP,'active');


-- ----------------------------
-- 表结构: user_tokens (记住我/API令牌)
-- ----------------------------
DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'Token (Hash)',
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户登录令牌表';


-- ----------------------------
-- 表结构: categories (文章分类)
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `slug` varchar(50) NOT NULL COMMENT 'URL别名 (Slug)',
  `description` TEXT NULL DEFAULT NULL COMMENT '分类描述',
  `sort` int(11) DEFAULT '0' COMMENT '排序权重 (越大越前)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表';

-- ----------------------------
-- 演示数据: categories
-- ----------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort`) VALUES
(1, '默认分类', 'default', '默认分类', 1);


-- ----------------------------
-- 表结构: posts (文章表)
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `circle_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属圈子ID',
  `category_id` int(11) NOT NULL COMMENT '所属分类ID',
  `user_id` int(11) NOT NULL COMMENT '作者ID',
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `slug` varchar(255) DEFAULT NULL COMMENT '文章别名',
  `description` varchar(500) DEFAULT NULL COMMENT '文章摘要',
  `content` longtext NOT NULL COMMENT '文章正文 (HTML/Markdown)',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图片URL',
  `is_video` tinyint(1) DEFAULT '0' COMMENT '是否为视频帖',
  `status` enum('published','draft','archived','deleted') DEFAULT 'published' COMMENT '状态: published(发布), draft(草稿), archived(归档), deleted(删除)',
  `is_pinned` tinyint(1) DEFAULT '0' COMMENT '是否置顶 (1=是, 0=否)',
  `is_digest` tinyint(1) DEFAULT '0' COMMENT '是否精华 (1=是, 0=否)',
  `read_time` varchar(20) DEFAULT '5 min' COMMENT '预估阅读时间',
  `like_count` int(11) DEFAULT '0' COMMENT '点赞数',
  `view_count` int(11) DEFAULT '0' COMMENT '浏览量',
  `comment_count` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `circle_id` (`circle_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章主表';

-- ----------------------------
-- 表结构: post_likes (文章点赞表)
-- ----------------------------

DROP TABLE IF EXISTS `post_likes`;
CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '点赞用户ID',
  `post_id` int(11) NOT NULL COMMENT '帖子ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帖子点赞记录表';

--
-- 表结构: tags (标签表)
--
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `count` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签表';

-- ----------------------------
-- 表结构: post_tags (文章标签关联表)
-- ----------------------------
DROP TABLE IF EXISTS `post_tags`;
CREATE TABLE `post_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签关联表';

-- ----------------------------
-- 表结构: user_blocks (用户屏蔽表)
-- ----------------------------
DROP TABLE IF EXISTS `user_blocks`;
CREATE TABLE IF NOT EXISTS `user_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Who blocked',
  `target_id` int(11) NOT NULL COMMENT 'Who was blocked',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_block` (`user_id`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 表结构: comments (评论表)
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL COMMENT '文章ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `content` varchar(500) NOT NULL COMMENT '评论内容',
  `status` enum('approved','pending','spam') DEFAULT 'approved' COMMENT '状态',
  `parent_id` int(11) DEFAULT '0' COMMENT '父评论ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章评论表';

-- ----------------------------
-- 表结构: comment_likes (评论点赞表)
-- ----------------------------
DROP TABLE IF EXISTS `comment_likes`;
CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 表结构: user_addresses (用户收货地址表)
-- ----------------------------
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '收货人姓名',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `province` varchar(50) NOT NULL COMMENT '省',
  `city` varchar(50) NOT NULL COMMENT '市',
  `area` varchar(50) NOT NULL COMMENT '区/县',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认地址',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户收货地址表';


-- ----------------------------
-- 表结构: user_devices (设备指纹表) - Security Upgrade
-- ----------------------------
DROP TABLE IF EXISTS `user_devices`;
CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_hash` varchar(64) NOT NULL COMMENT '设备指纹Hash',
  `user_agent` varchar(255) DEFAULT NULL,
  `is_trusted` tinyint(1) DEFAULT '0' COMMENT '是否信任设备',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `device_hash` (`device_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户设备指纹表';

-- ----------------------------
-- 表结构: access_logs (访问日志表) - Security Upgrade
-- ----------------------------
DROP TABLE IF EXISTS `access_logs`;
CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'login, register, failed_login',
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `device_hash` varchar(64) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1' COMMENT '1=Success, 0=Fail',
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='安全访问日志表';


SET FOREIGN_KEY_CHECKS = 1;
