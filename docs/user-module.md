# 用户管理模块说明

## 1. 模块目标

本模块基于 CodeIgniter 3、MySQL、Redis 和 Vue 实现后台用户管理，提供用户列表、筛选、新增、编辑、软删除、重置密码等功能。

代码按 CI3 常见分层组织：

- `application/controllers/Users.php`：接收请求、参数校验、统一调用 Model 和返回 JSON。
- `application/models/User_model.php`：只负责数据库读写，统一使用 Query Builder，避免拼接 SQL。
- `application/libraries/Redis_client.php`：封装 Redis 连接、读写、删除、计数，Redis 不可用时自动降级。
- `application/core/MY_Controller.php`：封装统一 JSON 响应、分页参数、安全响应头、POST 方法校验。
- `application/views/users/index.php`：Vue 页面，负责后台列表、筛选、弹窗表单和接口交互。
- `application/config/api_codes.php`：集中维护 API code key、数字错误码和 language 文案 key 的映射。
- `application/language/chinese/app_lang.php`、`application/language/chinese/user_lang.php`：集中维护通用响应文案和用户模块提示文案，避免 Controller 中硬编码用户可见字符串。
- `database/users.sql`：数据库、用户表建表脚本和初始化管理员数据。
- `database/create_dev_user.sql`：本地开发账号脚本，用于处理 MySQL 8 与旧版 PHP mysqli 的认证兼容问题。
- `tools/check_db.php`：本地数据库连通性检查脚本。

## 2. 数据库设计

表名：`users`

核心字段：

- `id`：自增主键。
- `username`：用户名，唯一索引，软删除后也不复用。
- `password_hash`：密码哈希，使用 PHP `password_hash()` 生成。
- `real_name`、`email`、`mobile`：用户基础信息。
- `role`、`status`：角色和状态，枚举值在 `application/config/user_module.php` 中统一配置。
- `last_login_at`：预留登录模块使用，数据库存秒级 Unix 时间戳。
- `created_at`、`updated_at`、`deleted_at`：创建、更新、软删除时间，数据库存秒级 Unix 时间戳。
- `is_deleted`：软删除标记，`0` 表示有效，`1` 表示已删除。

索引设计：

- `PRIMARY KEY (id)`：主键查询。
- `UNIQUE KEY uk_users_username (username)`：保证账号唯一。
- `KEY idx_users_list (is_deleted, status, role, created_at)`：支撑后台未删除数据、状态、角色筛选和列表排序。
- `KEY idx_users_created_at (created_at)`：支撑创建时间排序或扩展筛选。
- `KEY idx_users_deleted_at (deleted_at)`：支撑软删除过滤。

## 3. 安全设计

- SQL 注入：Model 全部使用 CI Query Builder，`order_by` 字段使用白名单校验。
- XSS：输入通过 CI Input 的 `xss_clean` 基础处理，页面输出使用 Vue 插值和 `html_escape`。
- CSRF：`application/config/config.php` 已开启 CSRF，Vue 每次 POST 带 token，后端 JSON 返回最新 token。
- 敏感信息泄漏：列表接口不返回完整手机号和邮箱，只返回脱敏后的 `mobile_masked`、`email_masked`。
- 密码安全：数据库不保存明文密码，只保存 `password_hash`；重置密码接口独立封装。
- 请求方法限制：新增、更新、删除、重置密码接口必须 POST，禁止 GET 触发写操作。
- 软删除：删除用户只写入 `is_deleted = 1` 和 `deleted_at`，保留审计线索，不做物理删除。
- 安全响应头：统一设置 `X-Content-Type-Options`、`X-Frame-Options`、`Referrer-Policy`。
- Cookie：已开启 `cookie_httponly`，降低脚本读取 Cookie 的风险。
- Redis 降级：Redis 扩展或服务不可用时只影响缓存，不影响主业务流程。

## 4. Redis 使用

当前使用 Redis 缓存用户列表，减少重复查询压力。

缓存策略：

- 列表缓存 key 包含筛选条件、分页参数和列表版本号。
- 列表缓存 TTL 为 30 秒。
- 新增、更新、删除后递增 `users:list:version`，旧缓存自然失效。
- Redis key 使用 `application/config/redis.php` 中的 `redis_prefix` 隔离项目。

## 5. 文案与配置分离

用户可见的成功提示、失败提示和字段校验提示统一放在 CodeIgniter 的 language 文件中；Controller 只保留语义化 code key，例如 `USER_QUERY_SUCCESS`、`USER_NOT_FOUND`。

这样做有三个目的：

- 降低维护成本：修改提示语时不需要进入业务流程代码中逐个查找。
- 符合 CI3 框架习惯：使用框架自带的 language 机制，而不是照搬 Java 风格重新写一套常量类。
- 预留扩展空间：后续如果需要英文界面或统一错误码，可以在不改变 Controller 主流程的情况下继续扩展。

## 6. 错误码设计

接口响应增加 `code` 字段，`code` 是给前端和调用方判断业务结果的稳定标识，`message` 是给用户阅读的展示文案。

Controller 中只传语义化 code key，例如 `USER_NOT_FOUND`，由 `application/config/api_codes.php` 转换为数字错误码和 language 文案 key。这样既避免硬编码中文，也避免在业务代码中散落数字错误码。

当前错误码采用 5 位结构：`A BB CC`。

- `A`：错误类型，`2` 表示验证错误，`4` 表示访问错误，`5` 表示服务错误。
- `BB`：模块编号，`01` 表示用户模块。
- `CC`：具体错误类型，`02` 表示数据不存在。

例如 `20102` 表示：用户模块的数据不存在错误，即“用户不存在或已删除”。

详细说明见 `docs/error-codes.md`。

## 7. 部署步骤

1. 创建数据库和用户表：执行 `database/users.sql`，默认数据库名为 `ci3_admin`。
2. 配置 MySQL 连接：默认使用 `ci3_dev / Ci3Local@2026 / ci3_admin`，也可以通过 `CI_DB_HOST`、`CI_DB_USER`、`CI_DB_PASS`、`CI_DB_NAME` 环境变量覆盖。
3. 如果遇到 `The server requested authentication method unknown to the client`，执行 `database/create_dev_user.sql` 创建本地开发账号。
4. 使用 `php tools/check_db.php --user=ci3_dev --pass=Ci3Local@2026 --database=ci3_admin` 检查数据库连接。
5. 可选配置 Redis：修改 `application/config/redis.php`，安装 PHP redis 扩展并启动 Redis 服务。
6. 访问页面：`/index.php/users`。
7. 初始化账号：`admin`，初始化密码：`Admin@123456`。
8. 首次登录或交付演示后，应立即修改初始化密码。

## 8. 自测流程

见 `docs/self-test.md`。
