<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| 用户模块配置
|--------------------------------------------------------------------------
|
| 角色、状态这类枚举统一放在配置文件中，后续公司要求调整文案或增加角色时，
| 不需要散落修改 Controller、Model、View。
|
*/
$config['user_roles'] = array(
    'admin' => '管理员',
    'manager' => '经理',
    'staff' => '员工',
);

$config['user_statuses'] = array(
    'active' => '启用',
    'disabled' => '禁用',
);

$config['user_default_password'] = 'User@123456';

