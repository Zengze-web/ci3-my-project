<?php
/**
 * PHP 服务器
 *通过 php -S 127.0.0.1:8000 server.php
 *
 * CI3 需要所有动态请求都进入根目录 index.php；如果直接执行 application
 * 目录下的 Controller/Model/View 文件，就会看到 No direct script access allowed。
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__.$uri;

if ($uri !== '/' && is_file($file)) {
    return FALSE;
}

require __DIR__.'/index.php';
