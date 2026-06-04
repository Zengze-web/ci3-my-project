<?php
/**
 * 本地数据库连通性检查脚本。
 *
 * 只用于开发自测，不依赖 CodeIgniter 启动流程，不会写入数据库。
 */
$options = getopt('', array('host::', 'user::', 'pass::', 'database::'));

$host = isset($options['host']) ? $options['host'] : (getenv('CI_DB_HOST') ?: '127.0.0.1');
$user = isset($options['user']) ? $options['user'] : (getenv('CI_DB_USER') ?: 'root');
$pass = isset($options['pass']) ? $options['pass'] : getenv('CI_DB_PASS');
$name = isset($options['database']) ? $options['database'] : (getenv('CI_DB_NAME') ?: 'ci3_admin');
$pass = $pass === false ? '' : $pass;

$mysqli = @new mysqli($host, $user, $pass, $name);

if ($mysqli->connect_errno) {
    fwrite(STDERR, 'CONNECT_FAILED: '.$mysqli->connect_error.PHP_EOL);
    exit(1);
}

$mysqli->set_charset('utf8mb4');
$result = $mysqli->query('SELECT COUNT(*) AS total FROM users');

if ( ! $result) {
    fwrite(STDERR, 'QUERY_FAILED: '.$mysqli->error.PHP_EOL);
    exit(2);
}

$row = $result->fetch_assoc();
echo 'CONNECT_OK database='.$name.' users='.$row['total'].PHP_EOL;
