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
$lang['user_query_success'] = '查询成功';
$lang['user_not_found'] = '用户不存在或已删除';
$lang['user_form_invalid'] = '请检查表单输入';
$lang['user_create_success'] = '用户创建成功';
$lang['user_update_success'] = '用户更新成功';
$lang['user_invalid_id'] = '用户 ID 不合法';
$lang['user_delete_success'] = '用户已删除';
$lang['user_password_rule'] = '密码至少 8 位，需包含字母和数字';
$lang['user_password_weak'] = '密码复杂度不足';
$lang['user_reset_password_success'] = '密码已重置';

$lang['user_username_rule'] = '用户名需以字母开头，4-32 位，可包含字母、数字、下划线';
$lang['user_username_exists'] = '用户名已存在';
$lang['user_real_name_rule'] = '姓名不能为空，且不能超过 30 个字符';
$lang['user_email_invalid'] = '邮箱格式不正确';
$lang['user_mobile_invalid'] = '手机号格式不正确';
$lang['user_role_invalid'] = '角色不合法';
$lang['user_status_invalid'] = '状态不合法';
$lang['user_remark_rule'] = '备注不能超过 255 个字符';
