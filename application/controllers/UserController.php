<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'service/UserService.php';

use service\UserService;

/**
 * 用户管理控制器。
 */
class UserController extends BaseController
{
    /**
     * 用户服务类
     *
     * @var UserService
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    /**
     * 登录页面。
     */
    public function login()
    {

        if ($this->session->userdata('login_user')) {
            redirect('users');
            return;
        }

        $this->load->view('users/login', array(
            'csrf_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash(),
            'login_url' => site_url('users/login-submit'),
            'home_url' => site_url('users'),
        ));
    }

    /**
     * 接收登录表单并返回 JSON
     */
    public function loginSubmit()
    {
        if ( ! $this->requirePost()) {
            return;
        }

        $result = $this->userService->login(
            safeTrim($this->input->post('username', TRUE)),
            safeTrim($this->input->post('password', TRUE))
        );

        if ( ! $result['success']) {
            $this->failJson(
                ! empty($result['disabled']) ? 'USER_LOGIN_DISABLED' : 'USER_LOGIN_FAILED',
                $this->arrayValue($result, 'errors', array()),
                400
            );
            return;
        }

        $this->successJson(
            array('user' => $result['user'], 'redirect' => site_url('users')),
            'USER_LOGIN_SUCCESS'
        );
    }

    /**
     * 退出登录返回登录页
     */
    public function logout()
    {
        $this->userService->logout();
        redirect('users/login');
    }

    /**
     * 用户管理主页面
     */
    public function index()
    {

        if ( ! $this->requireLogin(FALSE)) {
            return;
        }

        $options = $this->userService->getOptions();

        $this->load->view('users/index', array(
            'roles' => $options['roles'],
            'statuses' => $options['statuses'],
            'csrf_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash(),
            'api_base' => site_url('users'),
        ));
    }

    /**
     * 用户列表
     */
    public function listApi()
    {

        if ( ! $this->requireLogin(TRUE)) {
            return;
        }

        list($page, $PageSize) = $this->paginationInput();
        $result = $this->userService->paginate($this->filtersFromRequest(), $page, $PageSize);

        $this->successJson($result, 'USER_QUERY_SUCCESS');
    }

    /**
     * 用户详情
     *
     * @param int $id 用户 ID
     */
    public function show($id)
    {

        if ( ! $this->requireLogin(TRUE)) {
            return;
        }

        $row = $this->userService->findDetail((int) $id);

        if ( ! $row) {
            $this->failJson('USER_NOT_FOUND', array(), 404);
            return;
        }

        $this->successJson(array('user' => $row), 'USER_QUERY_SUCCESS');
    }

    /**
     * 创建用户
     */
    public function store()
    {

        if ( ! $this->requireLogin(TRUE) || ! $this->requirePost()) {
            return;
        }

        $result = $this->userService->create($this->payloadFromPost(TRUE));

        if ( ! $result['success']) {
            $this->failJson('USER_FORM_INVALID', $this->arrayValue($result, 'errors', array()));
            return;
        }

        $this->successJson(array('id' => $result['id']), 'USER_CREATE_SUCCESS');
    }

    /**
     * 更新用户
     *
     * @param int $id 用户 ID
     */
    public function update($id)
    {

        if ( ! $this->requireLogin(TRUE) || ! $this->requirePost()) {
            return;
        }

        $result = $this->userService->update((int) $id, $this->payloadFromPost(FALSE));

        if ( ! $result['success']) {
            if ( ! empty($result['not_found'])) {
                $this->failJson('USER_NOT_FOUND', array(), 404);
                return;
            }

            $this->failJson('USER_FORM_INVALID', $this->arrayValue($result, 'errors', array()));
            return;
        }

        $this->successJson(array('id' => $result['id']), 'USER_UPDATE_SUCCESS');
    }

    /**
     * 删除用户
     *
     * @param int $id 用户 ID
     */
    public function delete($id)
    {

        if ( ! $this->requireLogin(TRUE) || ! $this->requirePost()) {
            return;
        }

        $result = $this->userService->delete((int) $id);

        if ( ! $result['success']) {
            $this->failJson('USER_INVALID_ID');
            return;
        }

        $this->successJson(array('id' => $result['id']), 'USER_DELETE_SUCCESS');
    }

    /**
     * 重置密码
     *
     * @param int $id 用户 ID
     */
    public function resetPassword($id)
    {

        if ( ! $this->requireLogin(TRUE) || ! $this->requirePost()) {
            return;
        }

        $password = safeTrim($this->input->post('password', TRUE));
        $result = $this->userService->resetPassword((int) $id, $password);

        if ( ! $result['success']) {
            if ( ! empty($result['not_found'])) {
                $this->failJson('USER_NOT_FOUND', array(), 404);
                return;
            }

            $this->failJson(
                'USER_PASSWORD_WEAK',
                array('password' => $this->langText('USER_PASSWORD_WEAK'))
            );
            return;
        }

        $this->successJson(array('id' => $result['id']), 'USER_RESET_PASSWORD_SUCCESS');
    }

    /**
     * 从 GET 参数生成筛选条件
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
     * 从 POST 参数读取用户表单数据
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
     * 统一检查登录态
     *
     * @param bool $json 是否返回 JSON
     * @return bool
     */
    private function requireLogin($json)
    {

        if ($this->session->userdata('login_user')) {
            return TRUE;
        }

        if ($json) {
            $this->failJson('USER_LOGIN_REQUIRED', array(), 401);
        } else {
            redirect('users/login');
        }

        return FALSE;
    }

    /**
     * 读取数组字段。
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function arrayValue($array, $key, $default)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }


}
