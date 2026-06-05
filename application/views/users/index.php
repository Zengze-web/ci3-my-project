<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>用户管理</title>
    <style>
        :root {
            --bg: #eef3f7;
            --surface: #ffffff;
            --line: #d8dee8;
            --line-strong: #c2cad6;
            --text: #172033;
            --muted: #697589;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #15803d;
            --danger: #dc2626;
            --warning: #b45309;
            --shadow: 0 12px 28px rgba(23, 32, 51, .08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", Arial, sans-serif;
        }

        [v-cloak] {
            display: none;
        }

        .page {
            min-height: 100vh;
            padding: 28px 24px;
        }

        .shell {
            max-width: 1280px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            padding: 18px 20px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .subtle {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .toolbar,
        .table-panel,
        .pager,
        .empty {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(23, 32, 51, .06);
        }

        .toolbar {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) 160px 160px 130px auto;
            gap: 12px;
            align-items: end;
            padding: 18px;
            margin-bottom: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #334155;
            font-weight: 600;
        }

        .required {
            color: var(--danger);
            margin-left: 3px;
        }

        .field-help {
            margin: -2px 0 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-radius: 6px;
            padding: 10px 11px;
            color: var(--text);
            background: #fbfdff;
            outline: none;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
        }

        textarea {
            min-height: 76px;
            resize: vertical;
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            transition: background .16s ease, border-color .16s ease, color .16s ease;
        }

        .btn:disabled {
            cursor: not-allowed;
            opacity: .58;
        }

        .btn-primary {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: #f8fafc;
            border-color: var(--line-strong);
            color: #334155;
        }

        .btn-secondary:hover:not(:disabled) {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger {
            background: #fff;
            border-color: #fecaca;
            color: var(--danger);
        }

        .btn-danger:hover:not(:disabled) {
            background: #fef2f2;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .table-panel {
            overflow: hidden;
            border-top: 3px solid #3b82f6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
            word-break: break-word;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        tbody tr:nth-child(even) {
            background: #fbfdff;
        }

        tbody tr:hover {
            background: #eff6ff;
        }

        .col-id {
            width: 76px;
        }

        .col-role,
        .col-status {
            width: 96px;
        }

        .col-time {
            width: 156px;
        }

        .col-actions {
            width: 230px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .tag-active {
            background: #dcfce7;
            color: var(--success);
        }

        .tag-disabled {
            background: #fef3c7;
            color: var(--warning);
        }

        .meta {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin: 12px 0 14px;
            color: var(--muted);
        }

        .pill {
            padding: 5px 9px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
        }

        .pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            padding: 12px 16px;
        }

        .empty {
            padding: 48px 16px;
            color: var(--muted);
            text-align: center;
        }

        .toast {
            position: fixed;
            right: 24px;
            top: 24px;
            z-index: 30;
            max-width: min(360px, calc(100vw - 48px));
            padding: 12px 14px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            background: #111827;
            color: #fff;
        }

        .modal-mask {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .46);
        }

        .modal-panel {
            width: min(860px, 100%);
            max-height: calc(100vh - 40px);
            overflow: auto;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .24);
        }

        .modal-head,
        .modal-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
        }

        .modal-foot {
            border-top: 1px solid var(--line);
            border-bottom: 0;
            justify-content: flex-end;
        }

        .modal-title {
            margin: 0;
            font-size: 17px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            align-items: start;
            padding: 18px;
        }

        .form-grid > div {
            min-width: 0;
        }

        .form-grid > div:not(.form-wide) .field-help {
            min-height: 35px;
        }

        .form-wide {
            grid-column: 1 / -1;
        }

        .error {
            min-height: 18px;
            margin-top: 4px;
            color: var(--danger);
            font-size: 12px;
        }

        .hint {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        @media (max-width: 960px) {
            .toolbar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .toolbar .actions {
                justify-content: flex-start;
            }

            .table-panel {
                overflow-x: auto;
            }

            table {
                min-width: 980px;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 14px;
            }

            .topbar,
            .pager {
                align-items: stretch;
                flex-direction: column;
            }

            .toolbar,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                justify-content: flex-start;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div id="userApp" class="page" v-cloak>
    <div class="shell">
        <div class="topbar">
            <div>
                <h1>账号管理</h1>
                <p class="subtle">维护后台账号、角色和启用状态</p>
            </div>
            <button class="btn btn-primary" type="button" @click="openCreate">新增用户</button>
        </div>

        <form class="toolbar" @submit.prevent="loadUsers(1)">
            <div>
                <label for="keyword">关键字</label>
                <input id="keyword" v-model.trim="filters.keyword" type="text" maxlength="50" placeholder="ID、用户名、姓名、邮箱、手机号">
            </div>
            <div>
                <label for="role">角色</label>
                <select id="role" v-model="filters.role">
                    <option value="">全部角色</option>
                    <option v-for="(name, key) in roles" :key="key" :value="key">{{ name }}</option>
                </select>
            </div>
            <div>
                <label for="status">状态</label>
                <select id="status" v-model="filters.status">
                    <option value="">全部状态</option>
                    <option v-for="(name, key) in statuses" :key="key" :value="key">{{ name }}</option>
                </select>
            </div>
            <div>
                <label for="perPage">每页</label>
                <select id="perPage" v-model.number="pagination.per_page" @change="loadUsers(1)">
                    <option :value="10">10 条</option>
                    <option :value="20">20 条</option>
                    <option :value="50">50 条</option>
                </select>
            </div>
            <div class="actions">
                <button class="btn btn-primary" type="submit" :disabled="loading">查询</button>
                <button class="btn btn-secondary" type="button" @click="resetFilters" :disabled="loading">重置</button>
            </div>
        </form>

        <div class="meta">
            <span class="pill">共 {{ pagination.total }} 条</span>
            <span class="pill">第 {{ pagination.page }} / {{ totalPages }} 页</span>
            <span class="pill">Redis：{{ redisAvailable ? '可用' : '未启用或不可用' }}</span>
            <span class="pill">数据来源：{{ fromCache ? '缓存' : '数据库' }}</span>
        </div>

        <div v-if="rows.length > 0" class="table-panel">
            <table>
                <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>用户名</th>
                    <th>姓名</th>
                    <th>邮箱</th>
                    <th>手机号</th>
                    <th class="col-role">角色</th>
                    <th class="col-status">状态</th>
                    <th class="col-time">创建时间</th>
                    <th class="col-actions">操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="row in rows" :key="row.id">
                    <td>{{ row.id }}</td>
                    <td>{{ row.username }}</td>
                    <td>{{ row.real_name }}</td>
                    <td>{{ row.email_masked || '-' }}</td>
                    <td>{{ row.mobile_masked || '-' }}</td>
                    <td>{{ roles[row.role] || row.role }}</td>
                    <td>
                        <span class="tag" :class="row.status === 'active' ? 'tag-active' : 'tag-disabled'">
                            {{ statuses[row.status] || row.status }}
                        </span>
                    </td>
                    <td>{{ row.created_at }}</td>
                    <td>
                        <div class="actions">
                            <button class="btn btn-secondary" type="button" @click="openEdit(row)" :disabled="loading">编辑</button>
                            <button class="btn btn-secondary" type="button" @click="openPassword(row)" :disabled="loading">重置密码</button>
                            <button class="btn btn-danger" type="button" @click="deleteUser(row)" :disabled="loading">删除</button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="empty">
            {{ loading ? '正在加载用户数据...' : '暂无符合条件的用户' }}
        </div>

        <div class="pager">
            <div>当前显示 {{ rows.length }} 条</div>
            <div class="actions">
                <button class="btn btn-secondary" type="button" @click="loadUsers(pagination.page - 1)" :disabled="pagination.page <= 1 || loading">上一页</button>
                <button class="btn btn-secondary" type="button" @click="loadUsers(pagination.page + 1)" :disabled="pagination.page >= totalPages || loading">下一页</button>
            </div>
        </div>
    </div>

    <div v-if="formVisible" class="modal-mask">
        <form class="modal-panel" @submit.prevent="submitForm">
            <div class="modal-head">
                <h2 class="modal-title">{{ form.id ? '编辑用户' : '新增用户' }}</h2>
                <button class="btn btn-secondary" type="button" @click="closeForm">关闭</button>
            </div>
            <div class="form-grid">
                <div>
                    <label for="username">用户名<span class="required">*</span></label>
                    <div class="field-help">必填:支持以中文和字母开头,且只能包含字母、数字和下划线。</div>
                    <input id="username" v-model.trim="form.username" type="text" maxlength="32" autocomplete="off">
                    <div class="error">{{ errors.username }}</div>
                </div>
                <div>
                    <label for="realName">姓名<span class="required">*</span></label>
                    <div class="field-help">必填:请输入用户真实姓名,最多 30 个字符。</div>
                    <input id="realName" v-model.trim="form.real_name" type="text" maxlength="30" autocomplete="off">
                    <div class="error">{{ errors.real_name }}</div>
                </div>
                <div>
                    <label for="email">邮箱</label>
                    <div class="field-help">选填:填写后必须是有效邮箱格式,例如 user@example.com。</div>
                    <input id="email" v-model.trim="form.email" type="email" maxlength="100" autocomplete="off">
                    <div class="error">{{ errors.email }}</div>
                </div>
                <div>
                    <label for="mobile">手机号</label>
                    <div class="field-help">选填:填写后必须是 11 位中国大陆手机号,需以 13-19 号段开头。</div>
                    <input id="mobile" v-model.trim="form.mobile" type="text" maxlength="11" autocomplete="off">
                    <div class="error">{{ errors.mobile }}</div>
                </div>
                <div>
                    <label for="roleForm">角色<span class="required">*</span></label>
                    <div class="field-help">必填:请选择该用户在后台系统中的权限角色。</div>
                    <select id="roleForm" v-model="form.role">
                        <option v-for="(name, key) in roles" :key="key" :value="key">{{ name }}</option>
                    </select>
                    <div class="error">{{ errors.role }}</div>
                </div>
                <div>
                    <label for="statusForm">状态<span class="required">*</span></label>
                    <div class="field-help">必填:启用账号可正常使用;禁用账号保留数据但不建议允许登录。</div>
                    <select id="statusForm" v-model="form.status">
                        <option v-for="(name, key) in statuses" :key="key" :value="key">{{ name }}</option>
                    </select>
                    <div class="error">{{ errors.status }}</div>
                </div>
                <div v-if="!form.id" class="form-wide">
                    <label for="password">初始密码<span class="required">*</span></label>
                    <div class="field-help">新增用户时必填:至少 8 位,必须同时包含字母和数字。</div>
                    <input id="password" v-model.trim="form.password" type="password" maxlength="64" autocomplete="new-password">
                    <div class="error">{{ errors.password }}</div>
                </div>
                <div class="form-wide">
                    <label for="remark">备注</label>
                    <div class="field-help">选填:用于记录账号说明、岗位或交接信息,最多 255 个字符。</div>
                    <textarea id="remark" v-model.trim="form.remark" maxlength="255"></textarea>
                    <div class="error">{{ errors.remark }}</div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn btn-secondary" type="button" @click="closeForm">取消</button>
                <button class="btn btn-primary" type="submit" :disabled="saving">{{ saving ? '保存中...' : '保存' }}</button>
            </div>
        </form>
    </div>

    <div v-if="passwordVisible" class="modal-mask">
        <form class="modal-panel" @submit.prevent="submitPassword">
            <div class="modal-head">
                <h2 class="modal-title">重置密码：{{ currentUser.username }}</h2>
                <button class="btn btn-secondary" type="button" @click="closePassword">关闭</button>
            </div>
            <div class="form-grid">
                <div class="form-wide">
                    <label for="newPassword">新密码</label>
                    <input id="newPassword" v-model.trim="passwordForm.password" type="password" maxlength="64" autocomplete="new-password">
                    <div class="hint">密码不会明文保存，后端使用 password_hash 生成摘要。</div>
                    <div class="error">{{ passwordErrors.password }}</div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn btn-secondary" type="button" @click="closePassword">取消</button>
                <button class="btn btn-primary" type="submit" :disabled="saving">{{ saving ? '提交中...' : '确认重置' }}</button>
            </div>
        </form>
    </div>

    <div v-if="toast" class="toast">{{ toast }}</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3.4.38/dist/vue.global.prod.js"></script>
<script>
    const ROLE_OPTIONS = <?php echo json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const STATUS_OPTIONS = <?php echo json_encode($statuses, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const API_BASE = (() => {
        const path = window.location.pathname.replace(/\/$/, '');
        const indexPos = path.indexOf('/index.php');

        if (indexPos >= 0) {
            return path.substring(0, indexPos) + '/index.php/users';
        }

        if (path.endsWith('/users')) {
            return path;
        }

        return '/index.php/users';
    })();

    Vue.createApp({
        data() {
            return {
                roles: ROLE_OPTIONS,
                statuses: STATUS_OPTIONS,
                csrf: {
                    name: '<?php echo html_escape($csrf_name); ?>',
                    hash: '<?php echo html_escape($csrf_hash); ?>'
                },
                filters: {
                    keyword: '',
                    role: '',
                    status: '',
                    order_by: 'id',
                    order_dir: 'desc'
                },
                pagination: {
                    page: 1,
                    per_page: 10,
                    total: 0
                },
                rows: [],
                loading: false,
                saving: false,
                redisAvailable: false,
                fromCache: false,
                formVisible: false,
                passwordVisible: false,
                currentUser: {},
                form: this.emptyForm(),
                passwordForm: {password: ''},
                errors: {},
                passwordErrors: {},
                toast: ''
            };
        },
        computed: {
            totalPages() {
                return Math.max(1, Math.ceil(this.pagination.total / this.pagination.per_page));
            }
        },
        mounted() {
            this.loadUsers(1);
        },
        methods: {
            emptyForm() {
                return {
                    id: 0,
                    username: '',
                    real_name: '',
                    email: '',
                    mobile: '',
                    role: 'staff',
                    status: 'active',
                    remark: '',
                    password: ''
                };
            },
            async loadUsers(page) {
                this.loading = true;
                this.pagination.page = Math.max(1, page || 1);

                try {
                    const params = Object.assign({}, this.filters, {
                        page: this.pagination.page,
                        per_page: this.pagination.per_page
                    });
                    const response = await this.apiGet(API_BASE + '/api/list?' + new URLSearchParams(params).toString());
                    const data = response.data || {};

                    this.rows = data.rows || [];
                    this.pagination.total = Number(data.total || 0);
                    this.pagination.page = Number(data.page || this.pagination.page);
                    this.pagination.per_page = Number(data.per_page || this.pagination.per_page);
                    this.redisAvailable = !!data.redis_available;
                    this.fromCache = !!data.from_cache;
                } catch (error) {
                    this.notice(error.message || '用户列表加载失败');
                } finally {
                    this.loading = false;
                }
            },
            resetFilters() {
                this.filters = {
                    keyword: '',
                    role: '',
                    status: '',
                    order_by: 'id',
                    order_dir: 'desc'
                };
                this.loadUsers(1);
            },
            openCreate() {
                this.errors = {};
                this.form = this.emptyForm();
                this.formVisible = true;
            },
            async openEdit(row) {
                this.errors = {};
                this.loading = true;

                try {
                    const response = await this.apiGet(API_BASE + '/api/' + row.id + '/show');
                    this.form = Object.assign(this.emptyForm(), response.data.user || {}, {password: ''});
                    this.formVisible = true;
                } catch (error) {
                    this.notice(error.message || '用户详情加载失败');
                } finally {
                    this.loading = false;
                }
            },
            closeForm() {
                this.formVisible = false;
                this.errors = {};
            },
            async submitForm() {
                this.saving = true;
                this.errors = {};

                try {
                    const url = this.form.id
                        ? API_BASE + '/api/' + this.form.id + '/update'
                        : API_BASE + '/api/store';
                    const payload = Object.assign({}, this.form);

                    if (this.form.id) {
                        delete payload.password;
                    }

                    const response = await this.apiPost(url, payload);
                    this.notice(response.message || '保存成功');
                    this.formVisible = false;
                    await this.loadUsers(this.pagination.page);
                } catch (error) {
                    this.errors = error.errors || {};
                    this.notice(error.message || '保存失败');
                } finally {
                    this.saving = false;
                }
            },
            openPassword(row) {
                this.currentUser = row;
                this.passwordForm = {password: ''};
                this.passwordErrors = {};
                this.passwordVisible = true;
            },
            closePassword() {
                this.passwordVisible = false;
                this.passwordErrors = {};
            },
            async submitPassword() {
                this.saving = true;
                this.passwordErrors = {};

                try {
                    const response = await this.apiPost(
                        API_BASE + '/api/' + this.currentUser.id + '/reset-password',
                        this.passwordForm
                    );
                    this.notice(response.message || '密码已重置');
                    this.passwordVisible = false;
                } catch (error) {
                    this.passwordErrors = error.errors || {};
                    this.notice(error.message || '密码重置失败');
                } finally {
                    this.saving = false;
                }
            },
            async deleteUser(row) {
                if (!window.confirm('确认删除用户“' + row.username + '”？删除后列表不再展示。')) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await this.apiPost(API_BASE + '/api/' + row.id + '/delete', {});
                    this.notice(response.message || '用户已删除');
                    await this.loadUsers(this.pagination.page);
                } catch (error) {
                    this.notice(error.message || '删除失败');
                } finally {
                    this.loading = false;
                }
            },
            async apiGet(url) {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'}
                });

                return this.parseResponse(response);
            },
            async apiPost(url, payload) {
                const formData = new FormData();
                formData.append(this.csrf.name, this.csrf.hash);

                Object.keys(payload).forEach((key) => {
                    formData.append(key, payload[key] == null ? '' : payload[key]);
                });

                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json'},
                    body: formData
                });

                return this.parseResponse(response);
            },
            async parseResponse(response) {
                let result;

                try {
                    result = await response.json();
                } catch (error) {
                    throw {code: 'CLIENT_PARSE_ERROR', message: '接口返回格式异常，请检查服务端日志'};
                }

                if (result.csrf) {
                    this.csrf = result.csrf;
                }

                if (!response.ok || !result.success) {
                    throw {
                        code: result.code || 'CLIENT_REQUEST_ERROR',
                        message: result.message || '请求失败',
                        errors: result.data && result.data.errors ? result.data.errors : {}
                    };
                }

                return result;
            },
            notice(message) {
                this.toast = message;
                window.clearTimeout(this.toastTimer);
                this.toastTimer = window.setTimeout(() => {
                    this.toast = '';
                }, 2600);
            }
        }
    }).mount('#userApp');
</script>
</body>
</html>
