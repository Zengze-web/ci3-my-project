-- 统一 users 表时间字段为 BIGINT，并基于第二条记录批量插入 100 个模拟真实用户。
-- 使用前提：MySQL 8+，且 users 表中存在 id = 2 的模板用户。
-- 执行建议：mysql --default-character-set=utf8mb4 ... < 本脚本，避免中文数据乱码。

USE ci3_admin;

-- 1. 时间相关字段统一为秒级 Unix 时间戳 BIGINT。
ALTER TABLE `users`
    MODIFY `last_login_at` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '最后登录时间戳',
    MODIFY `created_at` BIGINT UNSIGNED NOT NULL COMMENT '创建时间戳',
    MODIFY `updated_at` BIGINT UNSIGNED NOT NULL COMMENT '更新时间戳',
    MODIFY `deleted_at` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT '软删除时间戳';

-- 2. 基于第二条记录插入 100 条模拟真实用户。
--    密码 hash、角色、状态沿用模板记录；姓名、用户名、邮箱、手机号、备注按序号生成。
INSERT INTO `users`
(`username`, `password_hash`, `real_name`, `email`, `mobile`, `role`, `status`, `remark`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`)
WITH RECURSIVE seq(n) AS (
    SELECT 1
    UNION ALL
    SELECT n + 1 FROM seq WHERE n < 100
)
SELECT
    CONCAT(names.`real_name`, '_', LPAD(seq.n, 3, '0')) AS `username`,
    template.`password_hash`,
    names.`real_name`,
    CONCAT('user', LPAD(seq.n, 3, '0'), '@example.com') AS `email`,
    CONCAT('139', LPAD(seq.n, 8, '0')) AS `mobile`,
    template.`role`,
    template.`status`,
    CONCAT('模拟用户_', LPAD(seq.n, 3, '0')) AS `remark`,
    template.`last_login_at`,
    UNIX_TIMESTAMP() AS `created_at`,
    UNIX_TIMESTAMP() AS `updated_at`,
    NULL AS `deleted_at`
FROM (
    SELECT `password_hash`, `real_name`, `role`, `status`, `remark`, `last_login_at`
    FROM `users`
    WHERE `id` = 2
) AS template
JOIN seq
ON 1 = 1
JOIN (
    SELECT 1 AS n, '张三' AS real_name UNION ALL
    SELECT 2, '李四' UNION ALL
    SELECT 3, '王五' UNION ALL
    SELECT 4, '赵六' UNION ALL
    SELECT 5, '钱七' UNION ALL
    SELECT 6, '孙八' UNION ALL
    SELECT 7, '周九' UNION ALL
    SELECT 8, '吴十' UNION ALL
    SELECT 9, '郑明' UNION ALL
    SELECT 10, '王强' UNION ALL
    SELECT 11, '李娜' UNION ALL
    SELECT 12, '刘洋' UNION ALL
    SELECT 13, '陈磊' UNION ALL
    SELECT 14, '杨帆' UNION ALL
    SELECT 15, '黄杰' UNION ALL
    SELECT 16, '赵敏' UNION ALL
    SELECT 17, '周涛' UNION ALL
    SELECT 18, '吴静' UNION ALL
    SELECT 19, '徐超' UNION ALL
    SELECT 20, '孙丽' UNION ALL
    SELECT 21, '胡伟' UNION ALL
    SELECT 22, '朱琳' UNION ALL
    SELECT 23, '高峰' UNION ALL
    SELECT 24, '林雪' UNION ALL
    SELECT 25, '何勇' UNION ALL
    SELECT 26, '郭晨' UNION ALL
    SELECT 27, '马辉' UNION ALL
    SELECT 28, '罗丹' UNION ALL
    SELECT 29, '梁爽' UNION ALL
    SELECT 30, '宋佳' UNION ALL
    SELECT 31, '唐宁' UNION ALL
    SELECT 32, '许健' UNION ALL
    SELECT 33, '韩冰' UNION ALL
    SELECT 34, '冯凯' UNION ALL
    SELECT 35, '邓倩' UNION ALL
    SELECT 36, '曹阳' UNION ALL
    SELECT 37, '彭越' UNION ALL
    SELECT 38, '曾婷' UNION ALL
    SELECT 39, '萧然' UNION ALL
    SELECT 40, '田野' UNION ALL
    SELECT 41, '董浩' UNION ALL
    SELECT 42, '袁媛' UNION ALL
    SELECT 43, '潘亮' UNION ALL
    SELECT 44, '杜娟' UNION ALL
    SELECT 45, '叶青' UNION ALL
    SELECT 46, '魏巍' UNION ALL
    SELECT 47, '苏航' UNION ALL
    SELECT 48, '丁悦' UNION ALL
    SELECT 49, '沈乐' UNION ALL
    SELECT 50, '姜涛' UNION ALL
    SELECT 51, '范宇' UNION ALL
    SELECT 52, '陆瑶' UNION ALL
    SELECT 53, '崔健' UNION ALL
    SELECT 54, '程欣' UNION ALL
    SELECT 55, '廖斌' UNION ALL
    SELECT 56, '邹琳' UNION ALL
    SELECT 57, '秦川' UNION ALL
    SELECT 58, '尹航' UNION ALL
    SELECT 59, '侯亮' UNION ALL
    SELECT 60, '谭敏' UNION ALL
    SELECT 61, '赖强' UNION ALL
    SELECT 62, '白雪' UNION ALL
    SELECT 63, '邱晨' UNION ALL
    SELECT 64, '江南' UNION ALL
    SELECT 65, '夏雨' UNION ALL
    SELECT 66, '钟磊' UNION ALL
    SELECT 67, '石静' UNION ALL
    SELECT 68, '孟浩' UNION ALL
    SELECT 69, '龙飞' UNION ALL
    SELECT 70, '万芳' UNION ALL
    SELECT 71, '段鹏' UNION ALL
    SELECT 72, '雷鸣' UNION ALL
    SELECT 73, '熊俊' UNION ALL
    SELECT 74, '金鑫' UNION ALL
    SELECT 75, '姚远' UNION ALL
    SELECT 76, '贾玲' UNION ALL
    SELECT 77, '安然' UNION ALL
    SELECT 78, '邵阳' UNION ALL
    SELECT 79, '毛宁' UNION ALL
    SELECT 80, '龚雪' UNION ALL
    SELECT 81, '史航' UNION ALL
    SELECT 82, '汤圆' UNION ALL
    SELECT 83, '黎明' UNION ALL
    SELECT 84, '常乐' UNION ALL
    SELECT 85, '武刚' UNION ALL
    SELECT 86, '康宁' UNION ALL
    SELECT 87, '贺兰' UNION ALL
    SELECT 88, '严谨' UNION ALL
    SELECT 89, '牛犇' UNION ALL
    SELECT 90, '温雅' UNION ALL
    SELECT 91, '穆清' UNION ALL
    SELECT 92, '艾琳' UNION ALL
    SELECT 93, '乔森' UNION ALL
    SELECT 94, '路遥' UNION ALL
    SELECT 95, '盛夏' UNION ALL
    SELECT 96, '桂芳' UNION ALL
    SELECT 97, '蓝天' UNION ALL
    SELECT 98, '米雪' UNION ALL
    SELECT 99, '简宁' UNION ALL
    SELECT 100, '卓然'
) AS names
ON names.n = seq.n
ON DUPLICATE KEY UPDATE
    `real_name` = VALUES(`real_name`),
    `email` = VALUES(`email`),
    `mobile` = VALUES(`mobile`),
    `role` = VALUES(`role`),
    `status` = VALUES(`status`),
    `remark` = VALUES(`remark`),
    `updated_at` = VALUES(`updated_at`),
    `deleted_at` = NULL;
