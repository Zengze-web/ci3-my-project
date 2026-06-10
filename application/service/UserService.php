<?php
namespace service;

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'constatnt/UserConstants.php';
require_once APPPATH.'helpers/StringHelper.php';

/**
 * 用户模块业务逻辑。
 */
class UserService
{
    /**
     * CI 超级对象。
     *
     * @var \CI_Controller
     */
    private $CI;

    /**
     * 用户角色配置。
     *
     * @var array
     */
    private $roles = array();

    /**
     * 用户状态配置。
     *
     * @var array
     */
    private $statuses = array();

    /**
     * 提示文案。
     *
     * @var array
     */
    private $messages = array();

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->roles = \UserConstants::getRoles();
        $this->statuses = \UserConstants::getStatuses();
        $this->loadMessages();
    }

    /**
     * 获取用户管理页面选项。
     *
     * @return array
     */
    public function getOptions()
    {
        return array(
            'roles' => $this->roles,
            'statuses' => $this->statuses,
        );
    }

    /**
     * 校验账号密码并写入登录会话。
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @return array
     */
    public function login($username, $password)
    {
        $this->ensureSession();
        $this->ensureUserModel();

        if ($username === '' || $password === '') {
            return array(
                'success' => FALSE,
                'errors' => array('login' => $this->langText('USER_LOGIN_REQUIRED_FIELDS')),
            );
        }

        $user = $this->CI->User_model->findByUsername($username);

        if ( ! $user || ! password_verify($password, $user['password_hash'])) {
            return array(
                'success' => FALSE,
                'errors' => array('login' => $this->langText('USER_LOGIN_FAILED')),
            );
        }

        if ($user['status'] !== 'active') {
            return array(
                'success' => FALSE,
                'disabled' => TRUE,
                'errors' => array('login' => $this->langText('USER_LOGIN_DISABLED')),
            );
        }

        $this->CI->User_model->updateLastLogin($user['id']);
        $this->CI->session->set_userdata('login_user', array(
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'real_name' => $user['real_name'],
            'role' => $user['role'],
        ));

        return array(
            'success' => TRUE,
            'user' => array(
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'real_name' => $user['real_name'],
                'role' => $user['role'],
            ),
        );
    }

    /**
     * 清理登录会话。
     *
     * @return void
     */
    public function logout()
    {
        $this->ensureSession();
        $this->CI->session->unset_userdata('login_user');
    }

    /**
     * 获取分页用户列表。
     *
     * @param array $filters 过滤条件
     * @param int $page 页码
     * @param int $PageSize 每页数量
     * @return array
     */
    public function paginate($filters, $page, $PageSize)
    {
        $this->ensureRedis();
        $this->ensureUserModel();

        $cacheVersion = (int) $this->CI->redis_client->get('users:list:version');
        $cacheKey = 'users:list:'.md5(json_encode(array($filters, $page, $PageSize, $cacheVersion)));
        $cached = $this->CI->redis_client->get($cacheKey);

        if ($cached !== NULL) {
            $cached['redis_available'] = $this->CI->redis_client->isAvailable();
            $cached['from_cache'] = TRUE;
            return $cached;
        }

        $result = $this->CI->User_model->paginate($filters, $page, $PageSize);
        $result['rows'] = $this->safeRows($result['rows']);
        $result['redis_available'] = $this->CI->redis_client->isAvailable();
        $result['from_cache'] = FALSE;

        $this->CI->redis_client->set($cacheKey, $result, 30);

        return $result;
    }

    /**
     * 获取用户详情。
     *
     * @param int $id 用户 ID
     * @return array|null
     */
    public function findDetail($id)
    {
        $this->ensureUserModel();

        $row = $this->CI->User_model->find((int) $id);

        if ( ! $row) {
            return NULL;
        }

        $row = $this->formatUserTimes($row);
        unset($row['password_hash'], $row['deleted_at']);

        return $row;
    }

    /**
     * 创建用户。
     *
     * @param array $data 用户表单数据
     * @return array
     */
    public function create($data)
    {
        $this->ensureUserModel();
        $this->ensureRedis();

        $errors = $this->validatePayload($data, 0, TRUE);

        if ( ! empty($errors)) {
            return array(
                'success' => FALSE,
                'errors' => $errors,
            );
        }

        $id = $this->CI->User_model->create($data);
        $this->clearListCache();

        return array(
            'success' => TRUE,
            'id' => $id,
        );
    }

    /**
     * 更新用户。
     *
     * @param int $id 用户 ID
     * @param array $data 表单数据
     * @return array
     */
    public function update($id, $data)
    {
        $this->ensureUserModel();
        $this->ensureRedis();

        $id = (int) $id;

        if ( ! $this->CI->User_model->find($id)) {
            return array(
                'success' => FALSE,
                'not_found' => TRUE,
            );
        }

        $errors = $this->validatePayload($data, $id, FALSE);

        if ( ! empty($errors)) {
            return array(
                'success' => FALSE,
                'errors' => $errors,
            );
        }

        $this->CI->User_model->update($id, $data);
        $this->clearListCache();

        return array(
            'success' => TRUE,
            'id' => $id,
        );
    }

    /**
     * 删除用户。
     *
     * @param int $id 用户 ID
     * @return array
     */
    public function delete($id)
    {
        $this->ensureUserModel();
        $this->ensureRedis();

        $id = (int) $id;

        if ($id <= 0) {
            return array(
                'success' => FALSE,
                'invalid_id' => TRUE,
            );
        }

        $this->CI->User_model->softDelete($id);
        $this->clearListCache();

        return array(
            'success' => TRUE,
            'id' => $id,
        );
    }

    /**
     * 重置用户密码。
     *
     * @param int $id 用户 ID
     * @param string $password 新密码
     * @return array
     */
    public function resetPassword($id, $password)
    {
        $this->ensureUserModel();

        $id = (int) $id;

        if ($id <= 0 || ! $this->CI->User_model->find($id)) {
            return array(
                'success' => FALSE,
                'not_found' => TRUE,
            );
        }

        if ( ! $this->isStrongPassword($password)) {
            return array(
                'success' => FALSE,
                'weak_password' => TRUE,
            );
        }

        $this->CI->User_model->resetPassword($id, $password);

        return array(
            'success' => TRUE,
            'id' => $id,
        );
    }

    /**
     * 校验表单数据。
     *
     * @param array $data
     * @param int $ignoreId
     * @param bool $requirePassword
     * @return array
     */
    private function validatePayload($data, $ignoreId, $requirePassword)
    {
        $errors = array();

        if ( ! preg_match('/^[\p{Han}A-Za-z][\p{Han}A-Za-z0-9_]{3,31}$/u', $data['username'])) {
            $errors['username'] = $this->langText('USER_USERNAME_RULE');
        } elseif ($this->CI->User_model->usernameExists($data['username'], $ignoreId)) {
            $errors['username'] = $this->langText('USER_USERNAME_EXISTS');
        }

        if ($data['real_name'] === '' || mb_strlen($data['real_name'], 'UTF-8') > 30) {
            $errors['real_name'] = $this->langText('USER_REAL_NAME_RULE');
        }

        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = $this->langText('USER_EMAIL_INVALID');
        }

        if ($data['mobile'] !== '' && ! preg_match('/^1[3-9]\d{9}$/', $data['mobile'])) {
            $errors['mobile'] = $this->langText('USER_MOBILE_INVALID');
        }

        if ( ! array_key_exists($data['role'], $this->roles)) {
            $errors['role'] = $this->langText('USER_ROLE_INVALID');
        }

        if ( ! array_key_exists($data['status'], $this->statuses)) {
            $errors['status'] = $this->langText('USER_STATUS_INVALID');
        }

        if (mb_strlen($data['remark'], 'UTF-8') > 255) {
            $errors['remark'] = $this->langText('USER_REMARK_RULE');
        }

        if ($requirePassword && ! $this->isStrongPassword($data['password'])) {
            $errors['password'] = $this->langText('USER_PASSWORD_RULE');
        }

        return $errors;
    }

    /**
     * 密码复杂度检查。
     *
     * @param string $password
     * @return bool
     */
    private function isStrongPassword($password)
    {
        return strlen($password) >= 8
            && preg_match('/[A-Za-z]/', $password)
            && preg_match('/\d/', $password);
    }

    /**
     * 对返回的用户行做脱敏处理。
     *
     * @param array $rows
     * @return array
     */
    private function safeRows($rows)
    {
        foreach ($rows as &$row) {
            $row = $this->formatUserTimes($row);
            $row['email_masked'] = maskEmail($row['email']);
            $row['mobile_masked'] = maskMobile($row['mobile']);
            unset($row['email'], $row['mobile']);
        }

        return $rows;
    }

    /**
     * 格式化用户时间字段。
     *
     * @param array $row
     * @return array
     */
    private function formatUserTimes($row)
    {
        foreach (array('last_login_at', 'created_at', 'updated_at', 'deleted_at') as $field) {
            if (array_key_exists($field, $row)) {
                $timestamp = (int) $row[$field];
                $row[$field] = $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : NULL;
            }
        }

        return $row;
    }

    /**
     * 读取提示文案。
     *
     * @param string $key
     * @return string
     */
    private function langText($key)
    {
        return isset($this->messages[$key]) ? $this->messages[$key] : $key;
    }

    /**
     * 加载提示文案。
     */
    private function loadMessages()
    {
        $lang = array();
        $path = APPPATH.'constatnt/MessageConstants.php';

        if (is_file($path)) {
            require $path;
            $this->messages = $lang;
        }
    }

    /**
     * 清理用户列表缓存。
     */
    private function clearListCache()
    {
        $this->ensureRedis();
        $this->CI->redis_client->incrementWithTtl('users:list:version', 86400);
    }

    /**
     * 按需加载数据库模型。
     */
    private function ensureUserModel()
    {
        if ( ! isset($this->CI->db)) {
            $this->CI->load->database();
        }

        if ( ! isset($this->CI->User_model)) {
            $this->CI->load->model('User_model');
        }
    }

    /**
     * 按需加载 Redis 客户端。
     */
    private function ensureRedis()
    {
        if ( ! isset($this->CI->redis_client)) {
            $this->CI->load->library('Redis_client');
        }
    }

    /**
     * 按需加载 Session。
     */
    private function ensureSession()
    {
        if ( ! isset($this->CI->session)) {
            $this->CI->load->library('session');
        }
    }
}
