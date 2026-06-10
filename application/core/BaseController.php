<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 项目基础控制器。
 *
 * 所有业务控制器都可以继承这个类，把“统一响应、分页参数、安全响应头”
 * 这类横切逻辑集中放在这里，避免每个 Controller 重复写同样的代码。
 */
class BaseController extends CI_Controller
{
	//提示ide用的
	public $input;
	public $config;
	public $output;

    /**
     * 常量化提示文案。
     *
     * @var array
     */
    private $messages = array();
    /**
     * 构造函数会在每个请求进入业务 Controller 前执行。
     *
     * 这里设置基础安全响应头，减少浏览器嗅探类型、iframe 嵌套等常见风险。
     */
    public function __construct()
    {
        parent::__construct();

        $this->config->load('api_codes', TRUE);
        $this->load->helper('url');
		$this->ensureSession();
        $this->loadMessages();
        $this->output
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('X-Frame-Options: SAMEORIGIN')
            ->set_header('Referrer-Policy: same-origin');
    }

    /**
     * 输出成功 JSON。
     *
     * 前端所有接口都用统一结构，方便 Vue 页面统一处理错误、刷新 CSRF Token。
     *
     * @param array  $data    业务数据
     * @param string $codeKey 语义化响应码键
     */
    protected function successJson($data = array(), $codeKey = 'SUCCESS')
    {
        $this->jsonResponse(TRUE, $codeKey, $data);
    }

    /**
     * 输出失败 JSON。
     *
     * 不直接暴露异常堆栈、SQL 等敏感信息，只返回可展示给用户的安全提示。
     *
     * @param string $codeKey    语义化响应码
     * @param array  $errors     字段级错误
     * @param int    $httpStatus HTTP 状态码
     */
    protected function failJson($codeKey = 'APP_OPERATION_FAILURE', $errors = array(), $httpStatus = 400)
    {
        $this->output->set_status_header($httpStatus);
        $this->jsonResponse(FALSE, $codeKey, array('errors' => $errors));
    }

    /**
     * 校验当前请求必须为 POST。
     *
     * 修改数据的接口必须走 POST，才能配合 CI 的 CSRF 校验拦截跨站请求伪造。
     * Controller 中调用本方法失败后应直接 return，避免继续执行写操作。
     *
     * @return bool
     */
    protected function requirePost()
    {
        if ($this->input->method(TRUE) === 'POST') {
            return TRUE;
        }

        $this->output->set_header('Allow: POST');
        $this->failJson(
            'APP_METHOD_NOT_ALLOWED',
            array('method' => $this->langText('APP_METHOD_POST_REQUIRED')),
            405
        );

        return FALSE;
    }

    /**
     * 读取语言包文案，语言键不存在时返回兜底文案。
     *
     * @param string $key      语言键
     * @param string $fallback 兜底文案
     * @return string
     */
    protected function langText($key, $fallback = '')
    {
        $line = $this->lang->line($key);

        if ($line !== FALSE) {
            return $line;
        }

        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        return $fallback;
    }

    /**
     * 根据语义化 code key 读取完整错误码定义。
     *
     * @param string $codeKey 语义化响应码键
     * @return array
     */
    protected function apiCodeItem($codeKey)
    {
        $codes = $this->config->item('api_codes', 'api_codes');

        if (is_array($codes) && isset($codes[$codeKey])) {
            return $codes[$codeKey];
        }

        if (is_array($codes) && isset($codes['APP_OPERATION_FAILURE'])) {
            return $codes['APP_OPERATION_FAILURE'];
        }

        return array(
            'code' => '50004',
            'lang' => 'APP_UNKNOWN_ERROR',
        );
    }

    /**
     * 根据语义化 code key 读取展示文案。
     *
     * @param string $codeKey 语义化响应码键
     * @return string
     */
    protected function apiMessage($codeKey)
    {
        $item = $this->apiCodeItem($codeKey);

        return $this->langText($item['lang'], $this->langText('APP_UNKNOWN_ERROR', '未知错误'));
    }

    /**
     * 统一 JSON 输出格式。
     *
     * CI 开启 CSRF 后，每次 POST 可能重新生成 token；这里把最新 token 返回给前端，
     * 前端下一次请求继续带上它，避免出现 403。
     *
     * @param bool   $success  是否成功
     * @param string $codeKey  语义化响应码键
     * @param array  $data     响应数据
     */
    private function jsonResponse($success, $codeKey, $data)
    {
        $item = $this->apiCodeItem($codeKey);

        $payload = array(
            'success' => $success,
            'code' => $item['code'],
            'message' => $this->apiMessage($codeKey),
            'data' => $data,
            'csrf' => array(
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ),
        );

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 获取并修正分页参数。
     *
     * 这里限制每页最大数量，避免接口被传入超大 $PageSize后造成数据库压力。
     *
     * @return array
     */
    protected function paginationInput()
    {
        $page = (int) $this->input->get('page', TRUE);
        $PageSize = (int) $this->input->get('per_page', TRUE);
        if ($PageSize <= 0) {
            $PageSize = (int) $this->input->get('page_size', TRUE);
        }

        $page = $page > 0 ? $page : 1;
		$PageSize = $PageSize > 0 ? $PageSize : 10;
		$PageSize = min($PageSize, 100);

        return array($page, $PageSize);
    }

    /**
     * 加载当前重构后的消息常量文件。
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
	 * 登录Session。
	 */
	private function ensureSession()
	{
		if ( ! isset($this->session)) {
			$this->load->library('session');
		}
	}
}
