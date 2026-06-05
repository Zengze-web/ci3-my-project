<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model 层只负责数据库读写，不直接读取请求参数，也不输出页面。
 * 数据库操作统一使用 CI Query Builder，避免手写拼接 SQL 带来的注入风险。
 */
class User_model extends CI_Model
{
    /**
     * 用户表名。
     *
     * @var string
     */
    private $table = 'users';

    /**
     * 允许排序的字段白名单。
     *
     * 前端传入排序字段时必须命中白名单，避免 order_by 被注入任意 SQL。
     *
     * @var array
     */
    private $orderFields = array('id', 'username', 'created_at', 'updated_at', 'last_login_at');

    /**
     * 分页查询用户列表。
     *
     * @param array $filters 搜索条件
     * @param int   $page    当前页
     * @param int   $perPage 每页数量
     * @return array
     */
    public function paginate($filters, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;

        $this->db->from($this->table);
        $this->apply_filters($filters);
        $total = (int) $this->db->count_all_results();

        $this->db->select('id, username, real_name, email, mobile, role, status, last_login_at, created_at, updated_at');
        $this->db->from($this->table);
        $this->apply_filters($filters);

        $orderBy = isset($filters['order_by']) ? $filters['order_by'] : 'id';
        $orderDir = isset($filters['order_dir']) && strtolower($filters['order_dir']) === 'asc' ? 'asc' : 'desc';

        if ( ! in_array($orderBy, $this->orderFields, TRUE)) {
            $orderBy = 'id';
        }

        $rows = $this->db
            ->order_by($orderBy, $orderDir)
            ->limit($perPage, $offset)
            ->get()
            ->result_array();

        return array(
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        );
    }

    /**
     * 根据 ID 查询单个用户。
     *
     * @param int  $id             用户 ID
     * @param bool $includeDeleted 是否包含已软删除用户
     * @return array|null
     */
    public function find($id, $includeDeleted = FALSE)
    {
        $this->db->from($this->table);
        $this->db->where('id', (int) $id);

        if ( ! $includeDeleted) {
            $this->db->where('deleted_at IS NULL', NULL, FALSE);
        }

        $row = $this->db->get()->row_array();

        return $row ? $row : NULL;
    }

    /**
     * 判断用户名是否已经存在。
     *
     * 用户名采用全局唯一策略，软删除后也不允许复用，避免审计日志和历史数据中出现同名账号。
     *
     * @param string $username 用户名
     * @param int    $ignoreId 更新时忽略当前用户 ID
     * @return bool
     */
    public function username_exists($username, $ignoreId = 0)
    {
        $this->db->from($this->table);
        $this->db->where('username', $username);

        if ($ignoreId > 0) {
            $this->db->where('id !=', (int) $ignoreId);
        }

        return $this->db->count_all_results() > 0;
    }

    /**
     * 创建用户。
     *
     * 密码只保存 password_hash 结果，绝不保存明文密码。
     *
     * @param array $data 已校验过的用户数据
     * @return int 新用户 ID
     */
    public function create($data)
    {
        $now = time();

        $insert = array(
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'real_name' => $data['real_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'role' => $data['role'],
            'status' => $data['status'],
            'remark' => $data['remark'],
            'created_at' => $now,
            'updated_at' => $now,
        );

        $this->db->insert($this->table, $insert);

        return (int) $this->db->insert_id();
    }

    /**
     * 更新用户基础资料。
     *
     * 只更新白名单字段，避免前端提交额外字段篡改 password_hash、deleted_at 等敏感列。
     *
     * @param int   $id   用户 ID
     * @param array $data 已校验过的用户数据
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', (int) $id);
        $this->db->where('deleted_at IS NULL', NULL, FALSE);

        return (bool) $this->db->update($this->table, array(
            'username' => $data['username'],
            'real_name' => $data['real_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'role' => $data['role'],
            'status' => $data['status'],
            'remark' => $data['remark'],
            'updated_at' => time(),
        ));
    }

    /**
     * 重置用户密码。
     *
     * 单独封装密码更新，避免普通资料更新接口误改密码。
     *
     * @param int    $id       用户 ID
     * @param string $password 新密码
     * @return bool
     */
    public function reset_password($id, $password)
    {
        $this->db->where('id', (int) $id);
        $this->db->where('deleted_at IS NULL', NULL, FALSE);

        return (bool) $this->db->update($this->table, array(
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => time(),
        ));
    }

    /**
     * 软删除用户。
     *
     * 软删除可以保留审计线索，也避免误删后无法恢复。
     *
     * @param int $id 用户 ID
     * @return bool
     */
    public function soft_delete($id)
    {
        $now = time();

        $this->db->where('id', (int) $id);
        $this->db->where('deleted_at IS NULL', NULL, FALSE);

        return (bool) $this->db->update($this->table, array(
            'deleted_at' => $now,
            'updated_at' => $now,
        ));
    }

    /**
     * 应用列表筛选条件。
     *
     * 关键字搜索使用 Query Builder 的 like/where，CI 会自动转义参数。
     *
     * @param array $filters 筛选条件
     */
    private function apply_filters($filters)
    {
        $this->db->where('deleted_at IS NULL', NULL, FALSE);

        if ( ! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            if (preg_match('/^1[3-9]\d{9}$/', $keyword)) {
                $this->db->where('mobile', $keyword);
            } elseif (ctype_digit($keyword)) {
                $this->db->where('id', (int) $keyword);
            } else {
                $this->db->group_start();
                $this->db->like('username', $keyword);
                $this->db->or_like('real_name', $keyword);
                $this->db->or_like('email', $keyword);
                $this->db->group_end();
            }
        }

        if ( ! empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }

        if ( ! empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
    }
}
