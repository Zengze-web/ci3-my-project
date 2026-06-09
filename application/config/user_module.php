<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| 用户模块配置
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

