<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 用户模块常量封装
 */
class UserConstants
{
    const DEFAULT_PASSWORD = 'User@123456';

    public static function getRoles()
    {
        return array(
            'admin' => '管理员',
            'manager' => '总经理',
            'staff' => '用户',
        );
    }

    public static function getStatuses()
    {
        return array(
            'active' => '启用',
            'disabled' => '禁用',
        );
    }

    public static function getDefaultPassword()
    {
        return self::DEFAULT_PASSWORD;
    }
}
