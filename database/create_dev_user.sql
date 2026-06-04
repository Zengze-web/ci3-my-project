-- 本地开发数据库账号脚本
-- 适用于 MySQL 8 + 较旧 PHP mysqli 客户端出现以下错误的场景：
-- The server requested authentication method unknown to the client
--
-- 说明：
-- 1. 该账号只用于本地开发。
-- 2. 上线环境请改成公司分配的数据库账号，不要使用本文件里的示例密码。
-- 3. 如果你使用图形化工具执行，请先确认当前登录账号有 CREATE USER / GRANT 权限。

CREATE USER IF NOT EXISTS 'ci3_dev'@'127.0.0.1'
  IDENTIFIED BY 'Ci3Local@2026';

CREATE USER IF NOT EXISTS 'ci3_dev'@'localhost'
  IDENTIFIED BY 'Ci3Local@2026';

ALTER USER 'ci3_dev'@'127.0.0.1'
  IDENTIFIED WITH mysql_native_password BY 'Ci3Local@2026';

ALTER USER 'ci3_dev'@'localhost'
  IDENTIFIED WITH mysql_native_password BY 'Ci3Local@2026';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX
  ON `ci3_admin`.*
  TO 'ci3_dev'@'127.0.0.1';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX
  ON `ci3_admin`.*
  TO 'ci3_dev'@'localhost';

FLUSH PRIVILEGES;
