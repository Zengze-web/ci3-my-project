<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Redis 客户端封装。
 *
 * 如果当前机器没有 Redis 服务或 PHP redis 扩展，本类会自动降级，不影响数据库主流程。
 */
class Redis_client
{
    /**
     * CI 超级对象。
     *
     * @var CI_Controller
     */
    private $CI;

    /**
     * Redis 原生客户端。
     *
     * @var Redis|null
     */
    private $redis = NULL;

    /**
     * 当前 Redis 是否可用。
     *
     * @var bool
     */
    private $available = FALSE;

    /**
     * key 前缀，用于隔离不同项目的数据。
     *
     * @var string
     */
    private $prefix = '';

    /**
     * 初始化 Redis 连接。
     *
     * 构造函数中不会向外抛异常，避免 Redis 不可用时影响页面访问。
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('redis', TRUE);

        $config = $this->CI->config->item('redis');
        $this->prefix = isset($config['redis_prefix']) ? $config['redis_prefix'] : '';

        if ( ! class_exists('Redis')) {
            log_message('debug', 'PHP redis extension is not installed, Redis_client disabled.');
            return;
        }

        try {
            $redis = new Redis();
            $connected = $redis->connect(
                $config['redis_host'],
                (int) $config['redis_port'],
                (float) $config['redis_timeout']
            );

            if ( ! $connected) {
                return;
            }

            if ( ! empty($config['redis_password'])) {
                $redis->auth($config['redis_password']);
            }

            if (isset($config['redis_database'])) {
                $redis->select((int) $config['redis_database']);
            }

            $this->redis = $redis;
            $this->available = TRUE;
        } catch (Exception $e) {
            log_message('error', 'Redis connect failed: '.$e->getMessage());
        }
    }

    /**
     * 判断 Redis 当前是否可用。
     *
     * @return bool
     */
    public function is_available()
    {
        return $this->available;
    }

    /**
     * 读取缓存。
     *
     * @param string $key 业务 key
     * @return mixed|null
     */
    public function get($key)
    {
        if ( ! $this->available) {
            return NULL;
        }

        $value = $this->redis->get($this->key($key));

        return $value === FALSE ? NULL : json_decode($value, TRUE);
    }

    /**
     * 写入缓存。
     *
     * @param string $key        业务 key
     * @param mixed  $value      缓存内容
     * @param int    $ttlSeconds 过期时间，单位秒
     * @return bool
     */
    public function set($key, $value, $ttlSeconds = 300)
    {
        if ( ! $this->available) {
            return FALSE;
        }

        $payload = json_encode($value, JSON_UNESCAPED_UNICODE);

        return (bool) $this->redis->setex($this->key($key), (int) $ttlSeconds, $payload);
    }

    /**
     * 删除缓存。
     *
     * @param string $key 业务 key
     * @return bool
     */
    public function delete($key)
    {
        if ( ! $this->available) {
            return FALSE;
        }

        return (bool) $this->redis->del($this->key($key));
    }

    /**
     * 递增计数并设置过期时间。
     *
     * 常用于登录失败次数、短信发送次数等限流场景。
     *
     * @param string $key        业务 key
     * @param int    $ttlSeconds 过期时间
     * @return int
     */
    public function increment_with_ttl($key, $ttlSeconds)
    {
        if ( ! $this->available) {
            return 0;
        }

        $fullKey = $this->key($key);
        $count = (int) $this->redis->incr($fullKey);

        if ($count === 1) {
            $this->redis->expire($fullKey, (int) $ttlSeconds);
        }

        return $count;
    }

    /**
     * 生成带项目前缀的 Redis key。
     *
     * @param string $key 业务 key
     * @return string
     */
    private function key($key)
    {
        return $this->prefix.$key;
    }
}

