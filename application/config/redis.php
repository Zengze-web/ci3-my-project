<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Redis 配置
|--------------------------------------------------------------------------
|
| Redis 在本模块中作为增强组件使用，例如缓存用户列表、登录失败次数限流等。
| 如果本机暂时没有安装 Redis 服务或 PHP redis 扩展，业务主流程仍然可以运行。
|
*/
$config['redis_host'] = '127.0.0.1';
$config['redis_port'] = 6379;
$config['redis_timeout'] = 1.0;
$config['redis_password'] = '';
$config['redis_database'] = 0;
$config['redis_prefix'] = 'ci3_user:';

