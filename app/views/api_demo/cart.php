<?php include 'app/views/layouts/header.php'; ?>
<section class="container my-4">
    <div class="p-4 rounded-4 bg-danger text-white shadow-sm mb-4">
        <h1 class="fw-black mb-2">Demo API giỏ hàng</h1>
        <p class="mb-0">Cần đăng nhập API trước, sau đó gọi API giỏ hàng bằng token.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Product ID</label>
                    <input id="productId" class="form-control" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Số lượng</label>
                    <input id="quantity" class="form-control" value="1">
                </div>
                <div class="col-md-3">
                    <button id="btnAdd" class="btn btn-danger w-100 fw-bold">Thêm vào giỏ qua API</button>
                </div>
                <div class="col-md-3">
                    <button id="btnView" class="btn btn-outline-danger w-100 fw-bold">Xem giỏ hàng</button>
                </div>
            </div>
        </div>
    </div>

    <pre id="output" class="bg-dark text-white p-3 rounded-4"></pre>
</section>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const out = document.getElementById('output');

async function api(path, options = {}) {
    const token = localStorage.getItem('api_token') || '';
    options.headers = Object.assign({'Content-Type':'application/json', 'Authorization':'Bearer ' + token}, options.headers || {});
    const res = await fetch(BASE_URL + path, options);
    return await res.json();
}

document.getElementById('btnAdd').addEventListener('click', async () => {
    const json = await api('/api/cart', {
        method: 'POST',
        body: JSON.stringify({
            product_id: document.getElementById('productId').value,
            quantity: document.getElementById('quantity').value
        })
    });
    out.textContent = 'POST /api/cart\n\n' + JSON.stringify(json, null, 2);
});

document.getElementById('btnView').addEventListener('click', async () => {
    const json = await api('/api/cart');
    out.textContent = 'GET /api/cart\n\n' + JSON.stringify(json, null, 2);
});
</script>
<?php include 'app/views/layouts/footer.php'; ?>
