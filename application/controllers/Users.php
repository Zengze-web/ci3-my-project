<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 用户管理控制器。
 *
 * Controller 层只负责接收请求、做参数校验、调用 Model，并返回页面或 JSON。
 */
class Users extends MY_Controller
{
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
	 * db查询所需的类
	 *
	 * @var User_model
	 */
	public $User_model;
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(array('url', 'form', 'app'));
        $this->lang->load('user', 'chinese');
        $this->config->load('user_module', TRUE);
        $config = $this->config->item('user_module');
        $this->roles = $config['user_roles'];
        $this->statuses = $config['user_statuses'];
    }

    /**
     * 用户管理页面。
     *
     * 这里只输出页面基础数据，具体列表通过 Vue 调用接口异步加载。
     */
    public function index()
    {
        $this->load->view('users/index', array(
            'roles' => $this->roles,
            'statuses' => $this->statuses,
            'csrf_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash(),
            'api_base' => site_url('users'),
        ));
    }

    /**
     * 用户列表接口。
     *
     * 支持关键字、角色、状态筛选，并用 Redis 做短时间缓存。
     */
    public function listApi()
    {
        $this->bootApiDependencies();

        list($page, $perPage) = $this->paginationInput();
        $filters = $this->filtersFromRequest();
        $cacheVersion = (int) $this->redis_client->get('users:list:version');
        $cacheKey = 'users:list:'.md5(json_encode(array($filters, $page, $perPage, $cacheVersion)));
        $cached = $this->redis_client->get($cacheKey);

        if ($cached !== NULL) {
            $cached['redis_available'] = $this->redis_client->isAvailable();
            $cached['from_cache'] = TRUE;
            $this->successJson($cached, 'USER_QUERY_SUCCESS');
            return;
        }

        $result = $this->User_model->paginate($filters, $page, $perPage);
        $result['rows'] = $this->safeRows($result['rows']);
        $result['redis_available'] = $this->redis_client->isAvailable();
        $result['from_cache'] = FALSE;

        $this->redis_client->set($cacheKey, $result, 30);
        $this->successJson($result, 'USER_QUERY_SUCCESS');
    }

    /**
     * 用户详情接口。
     *
     * 列表接口会对手机号、邮箱做脱敏；编辑弹窗需要完整基础资料时，通过单条详情接口读取。
     * 密码哈希、软删除时间等敏感或内部字段不会返回给前端。
     *
     * @param int $id 用户 ID
     */
    public function show($id)
    {
        $this->bootApiDependencies();


        $id = (int) $id;
        $row = $this->User_model->find($id);

        if ( ! $row) {
            $this->failJson('USER_NOT_FOUND', array(), 404);
            return;
        }

        $row = $this->formatUserTimes($row);
        unset($row['password_hash'], $row['deleted_at']);
        $this->successJson(array('user' => $row), 'USER_QUERY_SUCCESS');
    }

    /**
     * 创建用户接口。
     *
     * 所有入库字段先经过白名单读取和业务校验，避免无关字段被写入数据库。
     */
    public function store()
    {
        if ( ! $this->requirePost()) {
            return;
        }

        $this->bootApiDependencies();
        $data = $this->payloadFromPost(TRUE);
        $errors = $this->validatePayload($data, 0, TRUE);

        if ( ! empty($errors)) {
            $this->failJson('USER_FORM_INVALID', $errors);
            return;
        }

        $id = $this->User_model->create($data);
        $this->clearListCache();
        $this->successJson(array('id' => $id), 'USER_CREATE_SUCCESS');
    }

    /**
     * 更新用户接口。
     *
     * 只更新基础资料，密码重置走单独接口，降低误操作风险。
     *
     * @param int $id 用户 ID
     */
    public function update($id)
    {
        if ( ! $this->requirePost()) {
            return;
        }

        $this->bootApiDependencies();

        $id = (int) $id;
        $exists = $this->User_model->find($id);

        if ( ! $exists) {
            $this->failJson('USER_NOT_FOUND', array(), 404);
            return;
        }

        $data = $this->payloadFromPost(FALSE);
        $errors = $this->validatePayload($data, $id, FALSE);

        if ( ! empty($errors)) {
            $this->failJson('USER_FORM_INVALID', $errors);
            return;
        }

        $this->User_model->update($id, $data);
        $this->clearListCache();
        $this->successJson(array('id' => $id), 'USER_UPDATE_SUCCESS');
    }

    /**
     * 删除用户接口。
     *
     * 采用软删除，保留数据审计线索，不直接物理删除。
     *
     * @param int $id 用户 ID
     */
    public function delete($id)
    {
        if ( ! $this->requirePost()) {
            return;
        }

        $this->bootApiDependencies();

        $id = (int) $id;

        if ($id <= 0) {
            $this->failJson('USER_INVALID_ID');
            return;
        }

        $this->User_model->softDelete($id);
        $this->clearListCache();
        $this->successJson(array('id' => $id), 'USER_DELETE_SUCCESS');
    }

    /**
     * 重置密码接口。
     *
     * 密码长度和复杂度在这里做基本限制，实际公司环境还可以接入密码策略服务。
     *
     * @param int $id 用户 ID
     */
    public function resetPassword($id)
    {
        if ( ! $this->requirePost()) {
            return;
        }

        $this->bootApiDependencies();

        $id = (int) $id;
        $password = safeTrim($this->input->post('password', TRUE));

        if ($id <= 0 || ! $this->User_model->find($id)) {
            $this->failJson('USER_NOT_FOUND', array(), 404);
            return;
        }

        if ( ! $this->isStrongPassword($password)) {
            $this->failJson(
                'USER_PASSWORD_WEAK',
                array('password' => $this->langText('USER_PASSWORD_WEAK'))
            );
            return;
        }

        $this->User_model->resetPassword($id, $password);
        $this->successJson(array('id' => $id), 'USER_RESET_PASSWORD_SUCCESS');
    }


    /**
     * 从 GET 参数生成筛选条件。
     *
     * 所有字符串都经过 xss_clean 和 trim，排序字段还会在 Model 层再次做白名单校验。
     *
     * @return array
     */
    private function filtersFromRequest()
    {
        return array(
            'keyword' => safeTrim($this->input->get('keyword', TRUE)),
            'role' => safeTrim($this->input->get('role', TRUE)),
            'status' => safeTrim($this->input->get('status', TRUE)),
            'order_by' => safeTrim($this->input->get('order_by', TRUE)),
            'order_dir' => safeTrim($this->input->get('order_dir', TRUE)),
        );
    }

    /**
     * 从 POST 参数读取用户表单数据。
     *
     * @param bool $includePassword 是否读取密码字段
     * @return array
     */
    private function payloadFromPost($includePassword)
    {
        $data = array(
            'username' => safeTrim($this->input->post('username', TRUE)),
            'real_name' => safeTrim($this->input->post('real_name', TRUE)),
            'email' => safeTrim($this->input->post('email', TRUE)),
            'mobile' => safeTrim($this->input->post('mobile', TRUE)),
            'role' => safeTrim($this->input->post('role', TRUE)),
            'status' => safeTrim($this->input->post('status', TRUE)),
            'remark' => safeTrim($this->input->post('remark', TRUE)),
        );

        if ($includePassword) {
            $data['password'] = safeTrim($this->input->post('password', TRUE));
        }

        return $data;
    }

    /**
     * 用户表单校验规则
     *
     * 这里不依赖前端校验，保证即使有人绕过页面直接调接口，后端仍能拦截非法数据。
     *
     * @param array $data            表单数据
     * @param int   $ignoreId        更新时忽略的用户 ID
     * @param bool  $requirePassword 是否必须校验密码
     * @return array
     */
    private function validatePayload($data, $ignoreId, $requirePassword)
    {
        $errors = array();
		//用户名校验,支持中文或字母开头，4-32 位，只能包含中文、字母、数字和下划线；用户名不能与已有账号重复。
        if ( ! preg_match('/^[\p{Han}A-Za-z][\p{Han}A-Za-z0-9_]{3,31}$/u', $data['username'])) {
				$errors['username'] = $this->langText('USER_USERNAME_RULE');

        } elseif ($this->User_model->usernameExists($data['username'], $ignoreId)) {
            $errors['username'] = $this->langText('USER_USERNAME_EXISTS');
        }
		//姓名校验,属于必填项，并且名字长度不能大于30个字节，按照utf编码原则，一个中文3字节，一个英文1字节
        if ($data['real_name'] === '' || mb_strlen($data['real_name'], 'UTF-8') > 30) {
            $errors['real_name'] = $this->langText('USER_REAL_NAME_RULE');
        }
		//邮箱校验，邮箱属于选填，不填没事，填了格式有误的话会报错
        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = $this->langText('USER_EMAIL_INVALID');
        }
		// 手机号校验，属于选填，不填没事，填了必须符合11位手机号格式
        if ($data['mobile'] !== '' && ! preg_match('/^1[3-9]\d{9}$/', $data['mobile'])) {
            $errors['mobile'] = $this->langText('USER_MOBILE_INVALID');
        }
		//角色校验，角色属于必填项，并且提交的角色值必须存在于系统预定义的角色列表中，防止传入非法角色
        if ( ! array_key_exists($data['role'], $this->roles)) {
            $errors['role'] = $this->langText('USER_ROLE_INVALID');
        }
		//状态校验，状态属于必填项，并且提交的状态值必须存在于系统预定义的状态列表中，防止传入非法状态

        if ( ! array_key_exists($data['status'], $this->statuses)) {
            $errors['status'] = $this->langText('USER_STATUS_INVALID');
        }
		//备注校验，备注属于选填项，不填没事，填写时长度不能大于255个字符，按照UTF-8编码原则计算字符长度

        if (mb_strlen($data['remark'], 'UTF-8') > 255) {
            $errors['remark'] = $this->langText('USER_REMARK_RULE');
        }
		//密码校验，密码是否必填由$requirePassword控制，新增用户时通常必填，编辑用户时通常选填；如果需要校验，则密码必须满足强密码规则

        if ($requirePassword && ! $this->isStrongPassword($data['password'])) {
            $errors['password'] = $this->langText('USER_PASSWORD_RULE');
        }

        return $errors;
    }

    /**
     * 判断密码是否满足基础复杂度。
     *
     * @param string $password 密码
     * @return bool
     */
    private function isStrongPassword($password)
    {
        return strlen($password) >= 8
            && preg_match('/[A-Za-z]/', $password)
            && preg_match('/\d/', $password);
    }

    /**
     * 对返回列表做安全处理。
     *
     * 数据库存储完整信息，接口返回给页面时进行脱敏，避免敏感信息泄漏。
     *
     * @param array $rows 原始用户行
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
     * 将数据库中的秒级时间戳格式化为页面展示用时间字符串。
     *
     * @param array $row 用户数据
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
     * 清理用户列表缓存。
     */
    private function clearListCache()
    {
        $this->redis_client->incrementWithTtl('users:list:version', 86400);
    }

	/**
	 * @return void
	 *自测redis连接
	 */
	public function testRedis()
	{
		$redis = new Redis();

		$redis->connect('127.0.0.1', 6379);

		$redis->set('test_key', 'hello redis');

		echo $redis->get('test_key');
	}
}
