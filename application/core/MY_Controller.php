<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 项目基础控制器。
 *
 * 所有业务控制器都可以继承这个类，把“统一响应、分页参数、安全响应头”
 * 这类横切逻辑集中放在这里，避免每个 Controller 重复写同样的代码。
 */
class MY_Controller extends CI_Controller
{
    /**
     * 构造函数会在每个请求进入业务 Controller 前执行。
     *
     * 这里设置基础安全响应头，减少浏览器嗅探类型、iframe 嵌套等常见风险。
     */
    public function __construct()
    {
        parent::__construct();

        $this->lang->load('app', 'chinese');

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
     * @param string $message 提示信息
     */
    protected function success_json($data = array(), $message = NULL)
    {
        $message = $message === NULL ? $this->lang_text('app_operation_success') : $message;
        $this->json_response(TRUE, $message, $data);
    }

    /**
     * 输出失败 JSON。
     *
     * 不直接暴露异常堆栈、SQL 等敏感信息，只返回可展示给用户的安全提示。
     *
     * @param string $message    错误提示
     * @param array  $errors     字段级错误
     * @param int    $httpStatus HTTP 状态码
     */
    protected function fail_json($message = NULL, $errors = array(), $httpStatus = 400)
    {
        $message = $message === NULL ? $this->lang_text('app_operation_failure') : $message;
        $this->output->set_status_header($httpStatus);
        $this->json_response(FALSE, $message, array('errors' => $errors));
    }

    /**
     * 校验当前请求必须为 POST。
     *
     * 修改数据的接口必须走 POST，才能配合 CI 的 CSRF 校验拦截跨站请求伪造。
     * Controller 中调用本方法失败后应直接 return，避免继续执行写操作。
     *
     * @return bool
     */
    protected function require_post()
    {
        if ($this->input->method(TRUE) === 'POST') {
            return TRUE;
        }

        $this->output->set_header('Allow: POST');
        $this->fail_json(
            $this->lang_text('app_method_not_allowed'),
            array('method' => $this->lang_text('app_method_post_required')),
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
    protected function lang_text($key, $fallback = '')
    {
        $line = $this->lang->line($key);

        return $line === FALSE ? $fallback : $line;
    }

    /**
     * 统一 JSON 输出格式。
     *
     * CI 开启 CSRF 后，每次 POST 可能重新生成 token；这里把最新 token 返回给前端，
     * 前端下一次请求继续带上它，避免出现 403。
     *
     * @param bool   $success  是否成功
     * @param string $message  提示信息
     * @param array  $data     响应数据
     */
    private function json_response($success, $message, $data)
    {
        $payload = array(
            'success' => $success,
            'message' => $message,
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
     * 这里限制每页最大数量，避免接口被传入超大 per_page 后造成数据库压力。
     *
     * @return array
     */
    protected function pagination_input()
    {
        $page = (int) $this->input->get('page', TRUE);
        $perPage = (int) $this->input->get('per_page', TRUE);

        $page = $page > 0 ? $page : 1;
        $perPage = $perPage > 0 ? $perPage : 10;
        $perPage = min($perPage, 100);

        return array($page, $perPage);
    }
}
