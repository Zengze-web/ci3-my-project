# 用户管理模块自测流程

## 1. 环境检查

- PHP 版本满足 CI3 要求。
- MySQL 可以连接，`database/users.sql` 已执行，默认数据库名为 `ci3_admin`。
- Redis 为可选项：未安装 Redis 时，页面仍可正常查询数据库。
- 浏览器能访问 `/index.php/users`。

数据库连通性检查：

```bash
php tools/check_db.php
```

如果 MySQL 8 返回 `The server requested authentication method unknown to the client`，先在 MySQL 管理工具中执行 `database/create_dev_user.sql`，再执行：

```bash
php tools/check_db.php --user=ci3_dev --pass=Ci3Local@2026 --database=ci3_admin
```

## 2. 语法检查

在项目根目录执行：

```bash
php -l application/controllers/Users.php
php -l application/models/User_model.php
php -l application/core/MY_Controller.php
php -l application/libraries/Redis_client.php
php -l application/views/users/index.php
php -l application/migrations/001_create_users_table.php
```

预期结果：全部显示 `No syntax errors detected`。

## 3. 页面功能自测

1. 打开 `/index.php/users`。
2. 页面能正常显示账号管理标题、查询条件、列表区域和新增按钮。
3. 点击“查询”，列表能返回数据。
4. 修改每页条数，分页信息能同步变化。
5. 输入用户名、姓名、邮箱或手机号关键字，点击“查询”，列表按条件过滤。
6. 切换角色、状态筛选，列表按条件过滤。
7. 点击“重置”，查询条件清空，列表回到第一页。

## 4. 新增用户自测

1. 点击“新增用户”。
2. 用户名输入 `test_user`，姓名输入 `测试用户`。
3. 邮箱输入 `test@example.com`，手机号输入 `13900000000`。
4. 角色选择 `员工`，状态选择 `启用`。
5. 密码输入 `Test@123456`。
6. 点击“保存”。

预期结果：

- 页面提示创建成功。
- 列表刷新后出现新用户。
- 数据库 `password_hash` 字段为 hash 字符串，不是明文密码。
- 列表展示的手机号和邮箱为脱敏值。

## 5. 编辑用户自测

1. 点击刚创建用户的“编辑”。
2. 修改姓名、角色、状态或备注。
3. 点击“保存”。

预期结果：

- 页面提示更新成功。
- 列表刷新后展示新数据。
- 普通编辑不会修改密码字段。

## 6. 重置密码自测

1. 点击“重置密码”。
2. 输入弱密码 `123456`，提交。
3. 输入强密码 `Newpass123`，再次提交。

预期结果：

- 弱密码被后端拦截，返回“密码复杂度不足”。
- 强密码提交成功。
- 数据库中的 `password_hash` 发生变化，仍不是明文。

## 7. 删除用户自测

1. 点击“删除”。
2. 确认删除。

预期结果：

- 页面提示删除成功。
- 列表不再展示该用户。
- 数据库记录仍存在，`deleted_at` 有值。

## 8. 安全自测

### SQL 注入

在关键字输入：

```text
' OR 1=1 --
```

预期结果：接口正常返回，不报 SQL 错误，不出现绕过筛选的异常数据。

### XSS

新增或编辑备注输入：

```html
<script>alert(1)</script>
```

预期结果：页面不执行脚本，内容按普通文本处理。

### CSRF

不携带 CSRF Token 直接 POST 新增用户接口。

预期结果：CI 返回 403，写操作不会执行。

### 请求方法

在浏览器直接访问：

```text
/index.php/users/api/1/delete
```

预期结果：返回请求方法不允许，不会删除用户。

### 敏感信息

查看列表接口响应。

预期结果：列表响应中不包含完整 `email`、`mobile` 字段，只包含脱敏展示字段。

## 9. Redis 自测

1. Redis 可用时，连续两次点击“查询”。
2. 查看页面上的“数据来源”提示。
3. 新增或更新用户后再次查询。

预期结果：

- 第二次查询可能显示来自缓存。
- 新增或更新后版本号变化，列表不会长期展示旧数据。
- Redis 不可用时，页面仍可正常展示数据库查询结果。

## 10. 性能检查

执行以下 SQL 查看索引：

```sql
SHOW INDEX FROM users;
```

预期结果：

- 存在 `uk_users_username`。
- 存在状态、角色、软删除组合索引。
- 常用列表查询可通过索引过滤软删除和枚举筛选条件。
