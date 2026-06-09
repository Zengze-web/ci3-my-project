-- 用户管理模块建表脚本
-- 设计要点：
-- 1. username 全局唯一，软删除后也不复用，避免审计和历史数据混淆。
-- 2. password_hash 只保存 PHP password_hash() 的结果，不保存明文密码。
-- 3. is_deleted + deleted_at 用于软删除，列表查询统一过滤未删除数据。
-- 4. 常用筛选字段建立组合索引，降低后台列表查询压力。
-- ==============================================
CREATE DATABASE IF NOT EXISTS ci3_admin DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ci3_admin;

-- 用户表
CREATE TABLE IF NOT EXISTS `users` (
									   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
	                                   `username` VARCHAR(32) NOT NULL COMMENT '用户名，字母开头，支持字母数字下划线',
	                                   `password_hash` VARCHAR(255) NOT NULL COMMENT '密码哈希，禁止保存明文密码',
	                                   `real_name` VARCHAR(30) NOT NULL COMMENT '姓名',
	                                   `email` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '邮箱',
	                                   `mobile` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '手机号',
	                                   `role` VARCHAR(20) NOT NULL DEFAULT 'staff' COMMENT '角色：admin/manager/staff',
	                                   `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT '状态：active/disabled',
	                                   `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注',
	                                   `last_login_at` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '最后登录时间戳',
	                                   `created_at` BIGINT UNSIGNED NOT NULL COMMENT '创建时间戳',
	                                   `updated_at` BIGINT UNSIGNED NOT NULL COMMENT '更新时间戳',
	                                   `deleted_at` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '软删除时间戳',
	                                   `is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '判断是否删除',
	                                   PRIMARY KEY (`id`),
	                                   UNIQUE KEY `uk_users_username` (`username`),
	                                   KEY `idx_users_list` (`is_deleted`, `status`, `role`, `created_at`),
	                                   KEY `idx_users_created_at` (`created_at`),
	                                   KEY `idx_users_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台用户表';

-- 初始化管理员账号（重复执行不会报错，只会更新时间）
INSERT INTO `users`
(`username`, `password_hash`, `real_name`, `email`, `mobile`, `role`, `status`, `remark`, `created_at`, `updated_at`)
VALUES
	('admin', '$2y$10$EF5dYRhfkHoXt5G1tdJuDepvGqemGGu7PQVsZpCpRisRwjzugJkfe', '系统管理员', 'admin@example.com', '13800138000', 'admin', 'active', '初始化管理员，默认密码 Admin@123456，上线前必须修改', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
	`updated_at` = VALUES(`updated_at`);
