<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| 用户管理模块文案
|--------------------------------------------------------------------------
|
| 用户模块的成功提示、错误提示和字段校验提示集中维护在这里，避免 Controller
| 中散落大量硬编码中文字符串。
|
*/
$lang['USER_QUERY_SUCCESS'] = '查询成功';
$lang['USER_NOT_FOUND'] = '用户不存在或已删除';
$lang['USER_FORM_INVALID'] = '请检查表单输入';
$lang['USER_CREATE_SUCCESS'] = '用户创建成功';
$lang['USER_UPDATE_SUCCESS'] = '用户更新成功';
$lang['USER_INVALID_ID'] = '用户 ID 不合法';
$lang['USER_DELETE_SUCCESS'] = '用户已删除';
$lang['USER_OPERATION_FAILED'] = '用户操作失败';
$lang['USER_PASSWORD_RULE'] = '密码至少 8 位，需包含字母和数字';
$lang['USER_PASSWORD_WEAK'] = '密码复杂度不足';
$lang['USER_RESET_PASSWORD_SUCCESS'] = '密码已重置';
$lang['USER_LOGIN_SUCCESS'] = '登录成功';
$lang['USER_LOGOUT_SUCCESS'] = '已退出登录';
$lang['USER_LOGIN_FAILED'] = '用户名或密码错误';
$lang['USER_LOGIN_REQUIRED'] = '请先登录';
$lang['USER_LOGIN_REQUIRED_FIELDS'] = '请输入用户名和密码';
$lang['USER_LOGIN_DISABLED'] = '账号已被禁用，请联系管理员';
$lang['USER_USERNAME_RULE'] = '用户名需以中文或字母开头，4-32 位，可包含中文、字母、数字、下划线';
$lang['USER_USERNAME_EXISTS'] = '用户名已存在';
$lang['USER_REAL_NAME_RULE'] = '姓名不能为空，且不能超过 30 个字符';
$lang['USER_EMAIL_INVALID'] = '邮箱格式不正确';
$lang['USER_MOBILE_INVALID'] = '手机号格式不正确';
$lang['USER_ROLE_INVALID'] = '角色不合法';
$lang['USER_STATUS_INVALID'] = '状态不合法';
$lang['USER_REMARK_RULE'] = '备注不能超过 255 个字符';
/*
| 通用操作提示常量
| 放置跨模块复用的基础提示语，从language文件读取，后续需调整常量内容或扩展多语言时不用分散修改业务代码。
*/
$lang['APP_SUCCESS'] = '成功';
$lang['APP_OPERATION_SUCCESS'] = '操作成功';
$lang['APP_OPERATION_FAILURE'] = '操作失败';
$lang['APP_METHOD_NOT_ALLOWED'] = '请求方法不允许';
$lang['APP_METHOD_POST_REQUIRED'] = '请使用 POST 请求';
$lang['APP_UNKNOWN_ERROR'] = '未知错误';
