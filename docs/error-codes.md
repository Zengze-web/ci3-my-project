# API 错误码说明

## 1. 设计目标

接口响应中的 `message` 是给用户看的展示文案，已经通过 CodeIgniter language 文件统一管理。

接口响应中的 `code` 是给前端和调用方判断业务结果用的稳定标识。前端不应该通过中文 `message` 判断逻辑，因为文案后续可能调整；应该通过 `code` 判断。

统一响应格式：

```json
{
  "success": false,
  "code": "20102",
  "message": "用户不存在或已删除",
  "data": {
    "errors": {}
  }
}
```

## 2. 错误码结构

错误码采用 `A BB CC` 的 5 位结构：

- `A`：1 位错误类型
- `BB`：2 位模块编号
- `CC`：2 位具体错误类型

错误类型：

- `1`：消息类型
- `2`：验证错误
- `4`：访问错误
- `5`：服务错误

模块编号：

- `00`：通用模块
- `01`：用户模块

具体错误类型：

- `00`：接口验证错误
- `01`：参数校验错误
- `02`：数据不存在
- `03`：数据已存在
- `04`：操作失败
- `05`：密码规则错误

成功统一返回：

```text
0
```

## 3. 当前错误码

| code key | code | 文案 key | 说明 |
| --- | --- | --- | --- |
| `SUCCESS` | `0` | `app_success` | 通用成功 |
| `APP_OPERATION_SUCCESS` | `0` | `app_operation_success` | 通用操作成功 |
| `APP_OPERATION_FAILURE` | `50004` | `app_operation_failure` | 通用操作失败 |
| `APP_METHOD_NOT_ALLOWED` | `40000` | `app_method_not_allowed` | 请求方法不允许 |
| `USER_QUERY_SUCCESS` | `0` | `user_query_success` | 用户查询成功 |
| `USER_CREATE_SUCCESS` | `0` | `user_create_success` | 用户创建成功 |
| `USER_UPDATE_SUCCESS` | `0` | `user_update_success` | 用户更新成功 |
| `USER_DELETE_SUCCESS` | `0` | `user_delete_success` | 用户删除成功 |
| `USER_RESET_PASSWORD_SUCCESS` | `0` | `user_reset_password_success` | 用户密码重置成功 |
| `USER_INVALID_ID` | `20100` | `user_invalid_id` | 用户 ID 不合法 |
| `USER_FORM_INVALID` | `20101` | `user_form_invalid` | 用户表单校验失败 |
| `USER_NOT_FOUND` | `20102` | `user_not_found` | 用户不存在或已删除 |
| `USER_USERNAME_EXISTS` | `20103` | `user_username_exists` | 用户名已存在 |
| `USER_OPERATION_FAILED` | `20104` | `user_operation_failed` | 用户操作失败 |
| `USER_PASSWORD_WEAK` | `20105` | `user_password_rule` | 密码复杂度不足 |

## 4. 代码使用方式

Controller 中只传语义化 code key：

```php
$this->fail_json('USER_NOT_FOUND', array(), 404);
```

最终返回给前端：

```json
{
  "success": false,
  "code": "20102",
  "message": "用户不存在或已删除",
  "data": {
    "errors": {}
  }
}
```

这样可以保证：

- Controller 不硬编码中文提示。
- Controller 不直接散落数字错误码。
- 前端可以稳定判断 `code`。
- 文案调整时只改 language 文件。
