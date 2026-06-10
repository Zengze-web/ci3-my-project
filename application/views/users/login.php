<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>用户登录</title>
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #eef3f7;
            color: #172033;
            font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", Arial, sans-serif;
        }

        [v-cloak] {
            display: none;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-panel {
            width: 420px;
            max-width: 100%;
            padding: 34px 34px 28px;
            border: 1px solid #d8dee8;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(23, 32, 51, .08);
        }

        .login-title {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .login-subtitle {
            margin: 0 0 26px;
            color: #697589;
        }

        .login-actions {
            margin-top: 6px;
        }

        .login-actions .el-button {
            width: 100%;
        }
    </style>
</head>
<body>
<div id="loginApp" class="login-page" v-cloak>
    <div class="login-panel">
        <h1 class="login-title">用户管理系统</h1>
        <p class="login-subtitle">请输入账号密码登录后台</p>

        <el-form ref="loginForm" :model="form" :rules="rules" label-position="top" @submit.native.prevent>
            <el-form-item label="用户名" prop="username">
                <el-input
                    v-model.trim="form.username"
                    autocomplete="username"
                    maxlength="32"
                    placeholder="请输入用户名">
                </el-input>
            </el-form-item>

            <el-form-item label="密码" prop="password">
                <el-input
                    v-model.trim="form.password"
                    type="password"
                    autocomplete="current-password"
                    maxlength="64"
                    placeholder="请输入密码"
                    show-password
                    @keyup.enter.native="submitLogin">
                </el-input>
            </el-form-item>

            <div class="login-actions">
                <el-button type="primary" :loading="loading" @click="submitLogin">登录</el-button>
            </div>
        </el-form>
    </div>
</div>

<script src="https://unpkg.com/vue@2/dist/vue.js"></script>
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    new Vue({
        el: '#loginApp',
        data: function () {
            return {
                loading: false,
                loginUrl: '<?php echo html_escape($login_url); ?>',
                homeUrl: '<?php echo html_escape($home_url); ?>',
                csrf: {
                    name: '<?php echo html_escape($csrf_name); ?>',
                    hash: '<?php echo html_escape($csrf_hash); ?>'
                },
                form: {
                    username: '',
                    password: ''
                },
                rules: {
                    username: [
                        { required: true, message: '请输入用户名', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入密码', trigger: 'blur' }
                    ]
                }
            };
        },
        methods: {
            submitLogin: function () {
                this.$refs.loginForm.validate((valid) => {
                    if (!valid || this.loading) {
                        return;
                    }

                    this.loading = true;
                    const formData = new FormData();
                    formData.append('username', this.form.username);
                    formData.append('password', this.form.password);
                    formData.append(this.csrf.name, this.csrf.hash);

                    axios.post(this.loginUrl, formData, {
                        headers: {'Accept': 'application/json'}
                    }).then((response) => {
                        const result = response.data || {};

                        if (result.csrf) {
                            this.csrf = result.csrf;
                        }

                        if (!result.success) {
                            this.$message.error(result.message || '登录失败');
                            return;
                        }

                        this.$message.success(result.message || '登录成功');
                        window.location.href = (result.data && result.data.redirect) ? result.data.redirect : this.homeUrl;
                    }).catch((error) => {
                        const result = error.response && error.response.data ? error.response.data : {};

                        if (result.csrf) {
                            this.csrf = result.csrf;
                        }

                        this.$message.error(result.message || '登录失败');
                    }).finally(() => {
                        this.loading = false;
                    });
                });
            }
        }
    });
</script>
</body>
</html>
