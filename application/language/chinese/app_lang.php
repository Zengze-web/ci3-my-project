<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| 通用响应文案
|--------------------------------------------------------------------------
|
| 放置跨模块复用的基础提示语。Controller 只负责流程控制，用户可见文案统一
| 从 language 文件读取，后续需要调整文案或扩展多语言时不用分散修改业务代码。
|
*/
$lang['app_success'] = '成功';
$lang['app_operation_success'] = '操作成功';
$lang['app_operation_failure'] = '操作失败';
$lang['app_method_not_allowed'] = '请求方法不允许';
$lang['app_method_post_required'] = '请使用 POST 请求';
$lang['app_unknown_error'] = '未知错误';
