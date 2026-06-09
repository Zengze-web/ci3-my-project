<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| API 错误码配置|
|--------------------------------------------------------------------------
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
	/**
	 * 通用成功
	 * 用于没有明确业务场景时的成功响应
	 */
	'SUCCESS' => array(
        'code' => '0',
        'lang' => 'APP_SUCCESS',
    ),
	/**
	 * 通用操作成功
	 * 用于新增、编辑、删除等通用操作成功场景
	 */
    'APP_OPERATION_SUCCESS' => array(
        'code' => '0',
        'lang' => 'APP_OPERATION_SUCCESS',
    ),
	/**
	 * 通用操作失败
	 * 用于未细分业务错误时的失败响应
	 */
    'APP_OPERATION_FAILURE' => array(
        'code' => '50004',
        'lang' => 'APP_OPERATION_FAILURE',
    ),
	/**
	 * 请求方法不允许
	 * 例如接口只允许 POST，但实际使用了 GET 请求
	 */
    'APP_METHOD_NOT_ALLOWED' => array(
        'code' => '40000',
        'lang' => 'APP_METHOD_NOT_ALLOWED',
    ),
	/**
	 * 用户查询成功
	 * 用于用户列表、用户详情等查询成功场景
	 */
    'USER_QUERY_SUCCESS' => array(
        'code' => '0',
        'lang' => 'USER_QUERY_SUCCESS',
    ),
	/**
	 * 用户创建成功
	 * 用于新增用户成功场景
	 */
    'USER_CREATE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'USER_CREATE_SUCCESS',
    ),
	/**
	 * 用户更新成功
	 * 用于编辑用户资料成功场景
	 */
    'USER_UPDATE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'USER_UPDATE_SUCCESS',
    ),
	/**
	 * 用户删除成功
	 * 用于软删除用户成功场景
	 */
    'USER_DELETE_SUCCESS' => array(
        'code' => '0',
        'lang' => 'USER_DELETE_SUCCESS',
    ),
	/**
	 * 用户密码重置成功
	 * 用于管理员重置用户密码成功场景
	 */
    'USER_RESET_PASSWORD_SUCCESS' => array(
        'code' => '0',
        'lang' => 'USER_RESET_PASSWORD_SUCCESS',
    ),
	/**
	 * 用户 ID 无效
	 * 例如 ID 为空、不是数字、或小于等于 0
	 */
    'USER_INVALID_ID' => array(
        'code' => '20100',
        'lang' => 'USER_INVALID_ID',
    ),
	/**
	 * 用户表单校验失败
	 * 例如必填项为空、邮箱格式错误、手机号格式错误等
	 */
    'USER_FORM_INVALID' => array(
        'code' => '20101',
        'lang' => 'USER_FORM_INVALID',
    ),
	/**
	 * 用户不存在或已删除
	 * 例如根据 ID 查询、编辑、重置密码时没有找到有效用户
	 */
    'USER_NOT_FOUND' => array(
        'code' => '20102',
        'lang' => 'USER_NOT_FOUND',
    ),
	/**
	 * 用户名已存在
	 * 用于新增或编辑用户时，用户名与其他用户重复
	 */
    'USER_USERNAME_EXISTS' => array(
        'code' => '20103',
        'lang' => 'USER_USERNAME_EXISTS',
    ),
	/**
	 * 用户操作失败
	 * 用于数据库写入失败、更新失败、删除失败等用户模块通用失败场景
	 */
    'USER_OPERATION_FAILED' => array(
        'code' => '20104',
        'lang' => 'USER_OPERATION_FAILED',
    ),
	/**
	 * 用户密码强度不足
	 * 例如密码长度不足，或未同时包含字母和数字
	 */
    'USER_PASSWORD_WEAK' => array(
        'code' => '20105',
        'lang' => 'USER_PASSWORD_RULE',
    ),
);
