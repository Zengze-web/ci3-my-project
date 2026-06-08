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

$lang['USER_USERNAME_RULE'] = '用户名需以中文或字母开头，4-32 位，可包含中文、字母、数字、下划线';
$lang['USER_USERNAME_EXISTS'] = '用户名已存在';
$lang['USER_REAL_NAME_RULE'] = '姓名不能为空，且不能超过 30 个字符';
$lang['USER_EMAIL_INVALID'] = '邮箱格式不正确';
$lang['USER_MOBILE_INVALID'] = '手机号格式不正确';
$lang['USER_ROLE_INVALID'] = '角色不合法';
$lang['USER_STATUS_INVALID'] = '状态不合法';
$lang['USER_REMARK_RULE'] = '备注不能超过 255 个字符';
