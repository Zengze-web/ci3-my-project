<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 创建用户表。
 *
 * Migration 用于把数据库结构变更纳入代码管理，避免只靠人工导 SQL。
 */
class Migration_Create_users_table extends CI_Migration
{
    /**
     * 执行升级。
     *
     * users 表采用软删除设计，用户名全局唯一，密码只保存 hash。
     */
    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'comment' => '用户ID',
            ),
            'username' => array(
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => FALSE,
                'comment' => '用户名',
            ),
            'password_hash' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE,
                'comment' => '密码哈希',
            ),
            'real_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => FALSE,
                'comment' => '姓名',
            ),
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => '',
                'comment' => '邮箱',
            ),
            'mobile' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '',
                'comment' => '手机号',
            ),
            'role' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'staff',
                'comment' => '角色',
            ),
            'status' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'active',
                'comment' => '状态',
            ),
            'remark' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => '',
                'comment' => '备注',
            ),
            'last_login_at' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'null' => TRUE,
                'comment' => '最后登录时间戳',
            ),
            'created_at' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'null' => FALSE,
                'comment' => '创建时间戳',
            ),
            'updated_at' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'null' => FALSE,
                'comment' => '更新时间戳',
            ),
            'deleted_at' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => TRUE,
                'null' => TRUE,
                'comment' => '软删除时间戳',
            ),
            'is_deleted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'comment' => '判断是否删除',
            ),
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('created_at');
        $this->dbforge->add_key('deleted_at');
        $this->dbforge->create_table('users', TRUE, array(
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ));

        $this->db->query('ALTER TABLE `users` ADD UNIQUE KEY `uk_users_username` (`username`)');
        $this->db->query('ALTER TABLE `users` ADD KEY `idx_users_list` (`is_deleted`, `status`, `role`, `created_at`)');
    }

    /**
     * 执行回滚。
     */
    public function down()
    {
        $this->dbforge->drop_table('users', TRUE);
    }
}
