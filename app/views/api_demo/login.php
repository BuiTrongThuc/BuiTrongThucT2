<?php include 'app/views/layouts/header.php'; ?>
<section class="container my-4">
    <div class="p-4 rounded-4 bg-dark text-white shadow-sm mb-4">
        <h1 class="fw-black mb-2">Demo đăng nhập API + JWT</h1>
        <p class="mb-0">Đăng nhập qua <code class="text-white">POST /api/auth/login</code>, token lưu vào <code class="text-white">localStorage</code>.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <label class="form-label fw-bold">Username</label>
                    <input id="username" class="form-control mb-3" value="admin">
                    <label class="form-label fw-bold">Password</label>
                    <input id="password" type="password" class="form-control mb-3" value="123456">
                    <button id="btnLogin" class="btn btn-danger fw-bold w-100 mb-2">Đăng nhập API</button>
                    <button id="btnMe" class="btn btn-outline-danger fw-bold w-100">Gọi GET /api/auth/me</button>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <pre id="output" class="bg-dark text-white p-3 rounded-4" style="min-height:360px"></pre>
        </div>
    </div>
</section>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const out = document.getElementById('output');

document.getElementById('btnLogin').addEventListener('click', async () => {
    const res = await fetch(BASE_URL + '/api/auth/login', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            username: document.getElementById('username').value,
            password: document.getElementById('password').value
        })
    });
    const json = await res.json();
    if (json.success && json.data.token) {
        localStorage.setItem('api_token', json.data.token);
    }
    out.textContent = 'POST /api/auth/login\n\n' + JSON.stringify(json, null, 2);
});

document.getElementById('btnMe').addEventListener('click', async () => {
    const token = localStorage.getItem('api_token') || '';
    const res = await fetch(BASE_URL + '/api/auth/me', {
        headers: {'Authorization': 'Bearer ' + token}
    });
    const json = await res.json();
    out.textContent = 'GET /api/auth/me\nAuthorization: Bearer <token>\n\n' + JSON.stringify(json, null, 2);
});
</script>
<?php include 'app/views/layouts/footer.php'; ?>
