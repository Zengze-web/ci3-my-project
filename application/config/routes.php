<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// 默认控制器
// 当你直接访问网站根目录（例如 http://localhost:8000/）
// 系统自动去找名为 "users" 的控制器
$route['default_controller'] = 'users';
// 404 页面重写
$route['404_override'] = '';
// 是否自动把 URI 里的横线 - 转成下划线 _
// FALSE = 不自动转换
// TRUE  = 访问 hello-world 会自动找 hello_world 控制器/方法
$route['translate_uri_dashes'] = FALSE;

/*
| 用户管理模块路由
| 页面和接口拆开：页面负责展示，接口负责 JSON 数据交互。
*/
$route['users'] = 'users/index';
// 用户列表接口（GET 查询）
$route['users/api/list'] = 'users/listApi';
// 单个用户详情接口（根据 ID 查询）
$route['users/api/(:num)/show'] = 'users/show/$1';
// 新增用户接口（POST 提交）
$route['users/api/store'] = 'users/store';
// 更新用户接口（根据 ID 修改）
$route['users/api/(:num)/update'] = 'users/update/$1';
// 删除用户接口（根据 ID 删除）
$route['users/api/(:num)/delete'] = 'users/delete/$1';
// 删除用户接口（根据 ID 删除）
$route['users/api/(:num)/reset-password'] = 'users/resetPassword/$1';
