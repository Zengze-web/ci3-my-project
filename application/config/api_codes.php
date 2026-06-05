<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| API 错误码配置
|--------------------------------------------------------------------------
|
| Controller 中只传语义化的 code key，例如 USER_NOT_FOUND。
| 对外响应时统一转换为数字 code 和 language 文案，避免业务代码中散落数字码
| 和中文提示。
|
| 数字码结构：A BB CC
| A  = 错误类型：1 消息，2 验证错误，4 访问错误，5 服务错误
| BB = 模块编号：00 通用模块，01 用户模块
| CC = 具体错误：00 接口验证错误，01 参数校验错误，02 数据不存在，
|      03 数据已存在，04 操作失败，05 密码规则错误
|
*/
$config['api_codes'] = array(
    'SUCCESS' => array(
        'code' => '0',
        'lang' => 'app_success',
    ),
    'APP_OPERATION_SUCCESS' => array(
        'code' => '0',
        'lang' => 'app_operation_success',
    ),
    'APP_OPERATION_FAILURE' => array(
        'code' => '50004',
        'lang' => 'app_operation_failure',
    ),
    'APP_METHOD_NOT_ALLOWED' => array(
        'code' => '40000',
        'lang' => 'app_method_not_allowed',
    ),

    'USER_QUERY_SUCCESS' => array(
        'code' => '0',
        'lang' => 'user_query_success',
    ),
    'USER_CREATE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'user_create_success',
    ),
    'USER_UPDATE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'user_update_success',
    ),
    'USER_DELETE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'user_delete_success',
    ),
    'USER_RESET_PASSWORD_SUCCESS' => array(
        'code' => '0',
        'lang' => 'user_reset_password_success',
    ),
    'USER_INVALID_ID' => array(
        'code' => '20100',
        'lang' => 'user_invalid_id',
    ),
    'USER_FORM_INVALID' => array(
        'code' => '20101',
        'lang' => 'user_form_invalid',
    ),
    'USER_NOT_FOUND' => array(
        'code' => '20102',
        'lang' => 'user_not_found',
    ),
    'USER_USERNAME_EXISTS' => array(
        'code' => '20103',
        'lang' => 'user_username_exists',
    ),
    'USER_OPERATION_FAILED' => array(
        'code' => '20104',
        'lang' => 'user_operation_failed',
    ),
    'USER_PASSWORD_WEAK' => array(
        'code' => '20105',
        'lang' => 'user_password_rule',
    ),
);
