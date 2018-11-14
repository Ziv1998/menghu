-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018-01-14 12:53:54
-- 服务器版本: 5.5.47-0ubuntu0.14.04.1-log
-- PHP 版本: 5.5.9-1ubuntu4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `demo-admin`
--

-- --------------------------------------------------------

--
-- 表的结构 `tb_flag`
--

CREATE TABLE IF NOT EXISTS `tb_flag` (
  `flagid` int(11) NOT NULL COMMENT '用户类型标志',
  `flagname` varchar(255) DEFAULT NULL COMMENT '用户类型',
  UNIQUE KEY `flagid` (`flagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户类型表';

--
-- 转存表中的数据 `tb_flag`
--

INSERT INTO `tb_flag` (`flagid`, `flagname`) VALUES
(3, '家长');

-- --------------------------------------------------------

--
-- 表的结构 `tb_imuserid`
--

CREATE TABLE IF NOT EXISTS `tb_imuserid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imuserid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `kiss_access_token` varchar(255) DEFAULT NULL,
  `kiss_token_deadline` timestamp NULL DEFAULT NULL,
  `kiss_refresh_token` varchar(255) DEFAULT NULL,
  `kiss_scope` int(11) DEFAULT NULL,
  `schoolid` int(11) DEFAULT NULL,
  `cityid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `flagid` int(11) DEFAULT NULL COMMENT 'flagid',
  `headpic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imuserid_2` (`imuserid`,`userid`),
  KEY `imuserid` (`imuserid`),
  KEY `userid` (`userid`),
  KEY `schoolid` (`schoolid`),
  KEY `cityid` (`cityid`),
  KEY `flagid` (`flagid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- 转存表中的数据 `tb_imuserid`
--

INSERT INTO `tb_imuserid` (`id`, `imuserid`, `userid`, `kiss_access_token`, `kiss_token_deadline`, `kiss_refresh_token`, `kiss_scope`, `schoolid`, `cityid`, `username`, `flagid`, `headpic`) VALUES
(19, 39, 0, '97a44a4fedc7891f0a3ed2e3ad248819', '2018-01-14 06:53:14', 'eb6574b7aaec0ab222b315a51fb17fb9', NULL, 10000, NULL, '胡成浩', 3, 'http://oui227b53.bkt.clouddn.com/cc29dce066208bc69a44e2107a80cf36.png');

-- --------------------------------------------------------

--
-- 表的结构 `tb_link`
--

CREATE TABLE IF NOT EXISTS `tb_link` (
  `linkid` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `serverhost` varchar(255) NOT NULL,
  `routedir` varchar(255) NOT NULL,
  `relatePath` varchar(255) NOT NULL,
  `absolutePath` varchar(255) NOT NULL,
  `extendname` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `qiniu_hash` varchar(255) DEFAULT NULL COMMENT '七牛hash',
  `qiniu_key` varchar(255) DEFAULT NULL,
  `qiniu_http` varchar(255) DEFAULT NULL COMMENT '七牛文件域名',
  `userid` int(11) DEFAULT NULL COMMENT '上传者用户ID',
  PRIMARY KEY (`linkid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

--
-- 转存表中的数据 `tb_link`
--

INSERT INTO `tb_link` (`linkid`, `time`, `serverhost`, `routedir`, `relatePath`, `absolutePath`, `extendname`, `filesize`, `qiniu_hash`, `qiniu_key`, `qiniu_http`, `userid`) VALUES
(53, '2017-12-20 02:16:05', 'test.kiss.com:9000', 'kshop-admin', 'public/static/uploads/1/0/20171220/d82da590a5037d6ea4b7363deee4cd7d.jpg', 'http://test.kiss.com:9000/kshop-adminpublic/static/uploads/1/0/20171220/d82da590a5037d6ea4b7363deee4cd7d.jpg', 'jpg', 10730, 'Fm48l83f6rFytaF8uO_j_P_6Kpg6', 'd82da590a5037d6ea4b7363deee4cd7d.jpg', 'http://oui227b53.bkt.clouddn.com/', 0),
(55, '2017-12-20 02:16:50', 'test.kiss.com:9000', 'kshop-admin', 'public/static/uploads/1/0/20171220/62cd3a9eb1063007df79b4a82602f8a0.jpg', 'http://test.kiss.com:9000/kshop-adminpublic/static/uploads/1/0/20171220/62cd3a9eb1063007df79b4a82602f8a0.jpg', 'jpg', 22641, 'FvpyoEaC1VeAWNxD_Xotyg3o8dk_', '62cd3a9eb1063007df79b4a82602f8a0.jpg', 'http://oui227b53.bkt.clouddn.com/', 0),
(57, '2017-12-20 02:17:28', 'test.kiss.com:9000', 'kshop-admin', 'public/static/uploads/1/0/20171220/79d478d652d03fe065effbbb80b464be.jpg', 'http://test.kiss.com:9000/kshop-adminpublic/static/uploads/1/0/20171220/79d478d652d03fe065effbbb80b464be.jpg', 'jpg', 8648, 'FjZcxo-oQGD20FtZgfB5UOZ2nyt3', '79d478d652d03fe065effbbb80b464be.jpg', 'http://oui227b53.bkt.clouddn.com/', 0),
(59, '2017-12-20 02:21:36', 'test.kiss.com:9000', 'kshop-admin', 'public/static/uploads/1/0/20171220/8b0a534da92fb41e149f1e501a8ae312.jpg', 'http://test.kiss.com:9000/kshop-adminpublic/static/uploads/1/0/20171220/8b0a534da92fb41e149f1e501a8ae312.jpg', 'jpg', 30530, 'Fr70x6dQOhL_9XyLIxDnTe4lmaek', '8b0a534da92fb41e149f1e501a8ae312.jpg', 'http://oui227b53.bkt.clouddn.com/', 0),
(61, '2017-12-20 02:22:04', 'test.kiss.com:9000', 'kshop-admin', 'public/static/uploads/1/0/20171220/a847744d1500179bf9c60c5423d9dc3e.jpg', 'http://test.kiss.com:9000/kshop-adminpublic/static/uploads/1/0/20171220/a847744d1500179bf9c60c5423d9dc3e.jpg', 'jpg', 32796, 'Fn0IBu55F8K_xh7rdGhx5e8FOG0G', 'a847744d1500179bf9c60c5423d9dc3e.jpg', 'http://oui227b53.bkt.clouddn.com/', 0);

-- --------------------------------------------------------

--
-- 表的结构 `tb_school`
--

CREATE TABLE IF NOT EXISTS `tb_school` (
  `schoolid` int(11) NOT NULL AUTO_INCREMENT,
  `schoolname` varchar(255) NOT NULL,
  PRIMARY KEY (`schoolid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10001 ;

--
-- 转存表中的数据 `tb_school`
--

INSERT INTO `tb_school` (`schoolid`, `schoolname`) VALUES
(10000, '系统测试南校区');

-- --------------------------------------------------------

--
-- 表的结构 `tb_type`
--

CREATE TABLE IF NOT EXISTS `tb_type` (
  `typeid` int(11) NOT NULL AUTO_INCREMENT,
  `typename` varchar(255) NOT NULL,
  `iconid` int(11) DEFAULT NULL,
  PRIMARY KEY (`typeid`),
  UNIQUE KEY `typename` (`typename`),
  KEY `iconid` (`iconid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- 转存表中的数据 `tb_type`
--

INSERT INTO `tb_type` (`typeid`, `typename`, `iconid`) VALUES
(5, '推车', 53),
(7, '学习用品', 55),
(9, '儿童玩具', 57),
(11, '童装', 59),
(13, '体育用品', 61);

-- --------------------------------------------------------

--
-- 表的结构 `tb_user`
--

CREATE TABLE IF NOT EXISTS `tb_user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `sex` tinyint(4) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `updatetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logintime` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`userid`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- 转存表中的数据 `tb_user`
--

INSERT INTO `tb_user` (`userid`, `sex`, `username`, `phone`, `updatetime`, `logintime`, `password`) VALUES
(0, NULL, '商城管理员', NULL, '2017-12-20 02:01:23', NULL, NULL),
(9, NULL, 'Default', NULL, '2018-01-12 17:57:23', NULL, NULL),
(11, NULL, NULL, NULL, '2018-01-13 06:55:25', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `tb_vlist`
--

CREATE TABLE IF NOT EXISTS `tb_vlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `other` varchar(255) DEFAULT NULL COMMENT '注释',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='常量表' AUTO_INCREMENT=29 ;

--
-- 转存表中的数据 `tb_vlist`
--

INSERT INTO `tb_vlist` (`id`, `keyword`, `value`, `other`) VALUES
(1, 'kiss_token', 'a5cd033544cdd031a7e00be461d08f53', '保留'),
(3, 'kiss_http', 'http://test.kiss.com', '保留'),
(4, 'kiss_url_getdata', 'sws-admin/public/index.php/oauth/Getdata/sendData', '保留'),
(5, 'kiss_url_gettoken', 'sws-admin/public/index.php/oauth/Oauth/getApp', '保留'),
(6, 'kiss_appid', '15', '保留'),
(7, 'kiss_appkey', 'f37aa4bb308977f1feb66f5cad535dca', '保留'),
(8, 'kiss_log_appid', '1', '第三方授权appid'),
(9, 'kiss_log_appkey', '7e5669ac684c64d6c06de0f1ca4a0f51', '第三方授权appkey'),
(14, 'kiss_log_url_gettoken', 'sws-admin/public/index.php/oauthlog/oauth/sendToken', '第三方授权获取token接口'),
(15, 'kiss_log_url_getimuserid', 'sws-admin/public/index.php/oauthlog/oauth/sendImuserid', '保留'),
(16, 'kiss_log_http', 'http://test.kiss.com', '第三方授权服务器地址'),
(17, 'kiss_log_url_refreshtoken', 'sws-admin/public/index.php/oauthlog/oauth/refreshToken', '第三方授权刷新密钥接口'),
(18, 'qiniu_bucket', 'zhangminegkshop', '七牛仓库'),
(19, 'qiniu_http', 'http://p18lb8k1i.bkt.clouddn.com/', '七牛资源访问域名'),
(20, 'qiniu_accesskey', '8lk2GUEzOV0YRTwMemW4ERreDvnBh2QkCiBgxm4x', '七牛访问密钥'),
(21, 'qiniu_secretkey', 'MibOct0MiFnWgP_mFRhVxIIYO8sdCvhbpMN8MrZo', '七牛密钥'),
(22, 'kiss_getBasicInfo', 'sws-admin/public/index.php/oauthlog/oauth/sendInfo', '第三方授权获取用户信息接口'),
(23, 'version', '1.0.0', '数据库版本'),
(24, 'versioncode', '1', '数据库版本号'),
(25, 'kiss_serverOauth', 'sws-admin/public/index.php/oauthlog/oauth/serverlogin', '服务端授权kiss端接口'),
(27, 'redis_host', 'redis_host', 'redis地址');

-- --------------------------------------------------------

--
-- 表的结构 `tb_worker`
--

CREATE TABLE IF NOT EXISTS `tb_worker` (
  `workid` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `logintime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`workid`),
  KEY `FK_worker_roleid` (`roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `tb_worker`
--

INSERT INTO `tb_worker` (`workid`, `roleid`, `password`, `username`, `logintime`) VALUES
(1, 0, '2ec46df0a0a4e84163026804bd494ceb', '测试1', '2017-12-13 07:55:56');

-- --------------------------------------------------------

--
-- 表的结构 `tb_work_user`
--

CREATE TABLE IF NOT EXISTS `tb_work_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `typeid` int(11) NOT NULL,
  `personnumid` int(11) NOT NULL,
  `priceid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_2` (`userid`,`typeid`,`personnumid`),
  KEY `typeid` (`typeid`),
  KEY `personnumid` (`personnumid`),
  KEY `priceid` (`priceid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `tb_work_user`
--

INSERT INTO `tb_work_user` (`id`, `userid`, `typeid`, `personnumid`, `priceid`) VALUES
(1, 0, 3, 1, 31),
(2, 2, 3, 1, 31);

--
-- 限制导出的表
--

--
-- 限制表 `tb_imuserid`
--
ALTER TABLE `tb_imuserid`
  ADD CONSTRAINT `tb_imuserid_ibfk_1` FOREIGN KEY (`schoolid`) REFERENCES `tb_school` (`schoolid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_imuserid_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `tb_user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_imuserid_ibfk_3` FOREIGN KEY (`flagid`) REFERENCES `tb_flag` (`flagid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `tb_link`
--
ALTER TABLE `tb_link`
  ADD CONSTRAINT `tb_link_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `tb_user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `tb_type`
--
ALTER TABLE `tb_type`
  ADD CONSTRAINT `tb_type_ibfk_1` FOREIGN KEY (`iconid`) REFERENCES `tb_link` (`linkid`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
