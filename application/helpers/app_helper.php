<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 封装公共函数，工具方法
 */

if ( ! function_exists('mask_mobile')) {
    /**
     * 手机号脱敏展示。
     *
     * 后台列表不直接展示完整手机号，降低敏感信息泄漏风险。
     *
     * @param string $mobile 手机号
     * @return string
     */
    function mask_mobile($mobile)
    {
        if ($mobile === '' || $mobile === NULL) {
            return '';
        }

        return preg_replace('/^(\d{3})\d{4}(\d{4})$/', '$1****$2', $mobile);
    }
}

if ( ! function_exists('mask_email')) {
    /**
     * 邮箱脱敏展示。
     *
     * 保留首字符和域名，既能辅助后台人员识别账号，又避免完整邮箱暴露。
     *
     * @param string $email 邮箱
     * @return string
     */
    function mask_email($email)
    {
        if ($email === '' || $email === NULL || strpos($email, '@') === FALSE) {
            return '';
        }

        list($name, $domain) = explode('@', $email, 2);

        return substr($name, 0, 1).'***@'.$domain;
    }
}

if ( ! function_exists('safe_trim')) {
    /**
     * 安全 trim。
     *
     * CI 的 input 类负责处理输入来源，这里只做统一字符串整理，避免 NULL 或数组被误处理。
     *
     * @param mixed $value 原始值
     * @return string
     */
    function safe_trim($value)
    {
        return is_string($value) ? trim($value) : '';
    }
}

