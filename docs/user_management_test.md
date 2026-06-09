# 用户管理模块接口测试用例

| 用例标题 | 操作步骤 | 预期结果 |
|---|---|---|
| 页面正常打开 | 1. 浏览器访问 `/index.php/users` | 1. 页面正常渲染<br>2. 显示关键字、角色、状态、每页条数、查询、重置、新增用户按钮<br>3. 列表区域自动加载数据<br>4. 浏览器控制台无明显 JavaScript 报错 |
| 用户列表默认查询 | 1. 请求 `GET /index.php/users/api/list` | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `data.rows` 为数组<br>5. `data.total >= 1`<br>6. `data.page = 1`<br>7. `data.per_page = 10`<br>8. 每条数据不包含完整 `email`、`mobile` 字段，只返回 `email_masked`、`mobile_masked` |
| 用户列表分页查询 | 1. 请求 `GET /index.php/users/api/list?page=2&per_page=10` | 1. HTTP 200<br>2. `data.page = 2`<br>3. `data.per_page = 10`<br>4. `data.rows.length <= 10` |
| 每页数量上限校验 | 1. 请求 `GET /index.php/users/api/list?page=1&per_page=1000` | 1. HTTP 200<br>2. `data.per_page = 100`<br>3. 后端限制每页最大 100 条，避免一次查询过大 |
| 按 ID 查询用户 | 1. 请求 `GET /index.php/users/api/list?keyword=3` | 1. HTTP 200<br>2. 返回 ID 为 `3` 的用户<br>3. 不应因为手机号包含数字 `3` 而返回大量无关数据 |
| 按姓名查询用户 | 1. 请求 `GET /index.php/users/api/list?keyword=张三` | 1. HTTP 200<br>2. 返回姓名或用户名中包含 `张三` 的用户 |
| 按完整手机号查询用户 | 1. 请求 `GET /index.php/users/api/list?keyword=13900000003` | 1. HTTP 200<br>2. 返回手机号为 `13900000003` 的用户 |
| 按邮箱查询用户 | 1. 请求 `GET /index.php/users/api/list?keyword=user003@example.com` | 1. HTTP 200<br>2. 返回邮箱匹配的用户 |
| 按角色筛选用户 | 1. 请求 `GET /index.php/users/api/list?role=staff` | 1. HTTP 200<br>2. 返回用户的 `role` 都是 `staff` |
| 按状态筛选用户 | 1. 请求 `GET /index.php/users/api/list?status=active` | 1. HTTP 200<br>2. 返回用户的 `status` 都是 `active` |
| 排序字段白名单校验 | 1. 请求 `GET /index.php/users/api/list?order_by=password_hash&order_dir=asc` | 1. HTTP 200<br>2. 接口不报 SQL 错误<br>3. 后端未命中排序白名单时回退为按 `id` 排序 |
| SQL 注入关键字校验 | 1. 请求 `GET /index.php/users/api/list?keyword=' OR 1=1 --` | 1. HTTP 200<br>2. 接口不报 SQL 错误<br>3. 不出现绕过过滤返回异常数据的情况 |
| 查询存在用户详情 | 1. 请求 `GET /index.php/users/api/3/show` | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `data.user.id = 3`<br>5. 响应中不包含 `password_hash`、`deleted_at` |
| 查询不存在用户详情 | 1. 请求 `GET /index.php/users/api/999999/show` | 1. HTTP 404<br>2. `success = false`<br>3. `code = 20102`<br>4. `message = 用户不存在或已删除` |
| 新增用户成功 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`username=测试用户_新增01`，`real_name=测试新增`，`email=new_user_01@example.com`，`mobile=13912345678`，`role=staff`，`status=active`，`remark=新增接口测试`，`password=Test123456` | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `message = 用户创建成功`<br>5. `data.id` 为新增用户 ID<br>6. 列表可查询到该用户 |
| 新增用户时用户名格式错误 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`username = 123test` | 1. HTTP 400<br>2. `success = false`<br>3. `code = 20101`<br>4. `data.errors.username` 有错误提示 |
| 新增用户时用户名重复 | 1. 请求 `POST /index.php/users/api/store`<br>2. `username` 使用已存在用户名，例如 `张三_001` | 1. HTTP 400<br>2. `success = false`<br>3. `code = 20101`<br>4. `data.errors.username = 用户名已存在` |
| 新增用户时姓名为空 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`real_name = 空字符串` | 1. HTTP 400<br>2. `code = 20101`<br>3. `data.errors.real_name` 有错误提示 |
| 新增用户时邮箱格式错误 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`email = abc` | 1. HTTP 400<br>2. `data.errors.email = 邮箱格式不正确` |
| 新增用户时手机号格式错误 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`mobile = 123456` | 1. HTTP 400<br>2. `data.errors.mobile = 手机号格式不正确` |
| 新增用户时角色非法 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`role = super_admin` | 1. HTTP 400<br>2. `data.errors.role = 角色不合法` |
| 新增用户时状态非法 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`status = locked` | 1. HTTP 400<br>2. `data.errors.status = 状态不合法` |
| 新增用户时密码复杂度不足 | 1. 请求 `POST /index.php/users/api/store`<br>2. 提交参数：`password = 123456` | 1. HTTP 400<br>2. `data.errors.password = 密码至少 8 位，需包含字母和数字` |
| GET 访问新增接口 | 1. 请求 `GET /index.php/users/api/store` | 1. HTTP 405<br>2. `success = false`<br>3. `code = 40000`<br>4. `message = 请求方法不允许` |
| 编辑用户成功 | 1. 请求 `POST /index.php/users/api/{id}/update`<br>2. 提交参数：`username=测试用户_编辑01`，`real_name=测试编辑`，`email=edit_user_01@example.com`，`mobile=13987654321`，`role=manager`，`status=active`，`remark=编辑接口测试`<br>3. 再次查询该用户详情 | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `message = 用户更新成功`<br>5. 再查详情时字段已更新<br>6. 普通编辑不会修改密码 |
| 编辑不存在用户 | 1. 请求 `POST /index.php/users/api/999999/update` | 1. HTTP 404<br>2. `code = 20102`<br>3. `message = 用户不存在或已删除` |
| 编辑用户时用户名重复 | 1. 请求 `POST /index.php/users/api/{id}/update`<br>2. 将 `username` 改成其他用户已占用的用户名 | 1. HTTP 400<br>2. `code = 20101`<br>3. `data.errors.username = 用户名已存在` |
| GET 访问编辑接口 | 1. 请求 `GET /index.php/users/api/{id}/update` | 1. HTTP 405<br>2. `code = 40000` |
| 删除用户成功 | 1. 请求 `POST /index.php/users/api/{id}/delete`<br>2. 再调用列表接口查询该用户<br>3. 检查数据库记录 | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `message = 用户已删除`<br>5. 列表不再展示该用户<br>6. 数据库记录仍存在，`is_deleted = 1`，`deleted_at` 有值 |
| 删除不存在用户 | 1. 请求 `POST /index.php/users/api/999999/delete` | 1. HTTP 200<br>2. `success = true`<br>3. `message = 用户已删除` |
| GET 访问删除接口 | 1. 请求 `GET /index.php/users/api/{id}/delete` | 1. HTTP 405<br>2. `code = 40000`<br>3. 不会删除用户 |
| 重置密码成功 | 1. 请求 `POST /index.php/users/api/{id}/reset-password`<br>2. 提交参数：`password = Newpass123`<br>3. 检查数据库中该用户密码字段 | 1. HTTP 200<br>2. `success = true`<br>3. `code = 0`<br>4. `message = 密码已重置`<br>5. 数据库中 `password_hash` 发生变化，且不是明文 |
| 重置密码时复杂度不足 | 1. 请求 `POST /index.php/users/api/{id}/reset-password`<br>2. 提交参数：`password = 123456` | 1. HTTP 400<br>2. `success = false`<br>3. `code = 20105`<br>4. `message = 密码至少 8 位，需包含字母和数字`<br>5. `data.errors.password = 密码复杂度不足` |
| 重置不存在用户密码 | 1. 请求 `POST /index.php/users/api/999999/reset-password`<br>2. 提交参数：`password = Newpass123` | 1. HTTP 404<br>2. `code = 20102`<br>3. `message = 用户不存在或已删除` |
| GET 访问重置密码接口 | 1. 请求 `GET /index.php/users/api/{id}/reset-password` | 1. HTTP 405<br>2. `code = 40000` |
| CSRF 拦截校验 | 1. 不携带 CSRF token<br>2. 直接请求 `POST /index.php/users/api/store` | 1. CI3 拦截请求<br>2. 写操作不执行 |
| XSS 输入校验 | 1. 新增或编辑用户<br>2. 提交参数：`remark = <script>alert(1)</script>`<br>3. 查看页面展示效果 | 1. 页面不会执行脚本<br>2. 内容按普通文本处理或被安全过滤 |
| 敏感字段泄漏校验 | 1. 请求 `GET /index.php/users/api/list` | 1. 列表不返回完整邮箱和完整手机号<br>2. 列表不返回 `password_hash` |
| 软删除过滤校验 | 1. 删除某个用户<br>2. 再调用列表接口<br>3. 再调用详情接口查询该用户 | 1. 被删除用户不再出现在列表<br>2. 详情接口返回用户不存在或已删除 |

## 备注

- 删除不存在用户当前预期为成功返回，是基于现有代码行为：`delete()` 只校验 ID 是否大于 0，没有先判断用户是否存在。
- 后续建议优化为：删除不存在用户时返回 `USER_NOT_FOUND`，即 `code = 20102`。
- Postman 或 curl 测试写接口时，需要先获取最新 CSRF token。
