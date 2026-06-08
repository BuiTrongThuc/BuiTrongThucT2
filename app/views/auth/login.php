<?php include 'app/views/layouts/header.php'; ?>

<div class="premium-auth-shell">
    <div class="premium-auth-card" style="position:relative;overflow:hidden">
        <div style="position:absolute;inset:-40%;background:radial-gradient(circle at 50% 20%,rgba(255,18,56,.18),transparent 28%),radial-gradient(circle at 70% 80%,rgba(34,211,238,.12),transparent 26%);pointer-events:none"></div>

        <div class="premium-auth-top position-relative">
            <div class="premium-auth-icon">
                <i class="bi bi-fingerprint"></i>
            </div>
            <h2>Đăng nhập</h2>
            <p class="text-secondary fw-semibold mb-0">Truy cập tài khoản bằng Web API + JWT.</p>
        </div>

        <div class="p-4 p-md-5 pt-4 position-relative">
            <div id="apiLoginAlert"></div>

            <form id="apiLoginForm" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên đăng nhập</label>
                    <input type="text" id="username" class="form-control" value="admin" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu</label>
                    <input type="password" id="password" class="form-control" value="123456" required>
                </div>

                <button type="submit" class="uv-btn uv-btn-red w-100 mt-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập API
                </button>
            </form>

            <div class="d-flex justify-content-between mt-3 small">
                <a href="<?php echo BASE_URL; ?>/Auth/register" class="text-danger fw-bold">Đăng ký</a>
                <a href="<?php echo BASE_URL; ?>/Auth/forgotPassword" class="text-danger fw-bold">Quên mật khẩu?</a>
            </div>

            <pre id="apiLoginOutput" class="bg-dark text-white rounded-4 p-3 mt-4 small mb-0" style="max-height:220px;overflow:auto;display:none"></pre>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';

document.getElementById('apiLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const payload = {
        username: document.getElementById('username').value.trim(),
        password: document.getElementById('password').value
    };

    const json = await ApiClient.post('/api/auth/login', payload);
    const out = document.getElementById('apiLoginOutput');
    out.style.display = 'block';
    out.textContent = JSON.stringify(json, null, 2);

    if (json.success && json.data && json.data.token) {
        ApiClient.setAuth(json.data.token, json.data.user);
        document.getElementById('apiLoginAlert').innerHTML = `
            <div class="alert alert-success rounded-4 border-0 fw-bold">
                <i class="bi bi-check-circle me-1"></i> Đăng nhập thành công.
            </div>
        `;
        setTimeout(() => {
            if (json.data.user && json.data.user.role === 'admin') {
                window.location.href = '<?php echo BASE_URL; ?>/Admin/center';
            } else {
                window.location.href = '<?php echo BASE_URL; ?>/Product/list';
            }
        }, 700);
    } else {
        document.getElementById('apiLoginAlert').innerHTML = `
            <div class="alert alert-danger rounded-4 fw-bold">${json.message || 'Đăng nhập thất bại'}</div>
        `;
    }
});
</script>

<?php include 'app/views/layouts/footer.php'; ?>
