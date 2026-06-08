<?php include 'app/views/layouts/header.php'; ?>

<section class="container py-4">
    <div class="premium-page-hero mb-4">
        <span class="premium-hero-kicker"><i class="bi bi-bag-check"></i> Checkout API</span>
        <h1>Hoàn tất đơn hàng</h1>
        <p>Đơn hàng được tạo qua Web API. Sau khi đặt thành công, giỏ hàng được làm trống tự động.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-5">
                <div class="card-body p-4 p-lg-5">
                    <div id="checkoutAlert"></div>

                    <form id="apiCheckoutForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Họ tên</label>
                                <input id="name" class="form-control form-control-lg rounded-4" required value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input id="phone" class="form-control form-control-lg rounded-4" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Địa chỉ giao hàng</label>
                                <textarea id="address" class="form-control rounded-4" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Phương thức thanh toán</label>
                                <select id="paymentMethod" class="form-select form-select-lg rounded-4">
                                    <option value="cod">COD - Thanh toán khi nhận hàng</option>
                                    <option value="bank_transfer">Chuyển khoản mô phỏng</option>
                                    <option value="momo">MoMo mô phỏng</option>
                                    <option value="zalopay">ZaloPay mô phỏng</option>
                                    <option value="vnpay">VNPay mô phỏng</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <textarea id="note" class="form-control rounded-4" rows="2"></textarea>
                            </div>
                        </div>

                        <button class="premium-btn premium-btn-primary mt-4 px-5" type="submit">
                            <i class="bi bi-credit-card me-1"></i> Đặt hàng ngay
                        </button>
                    </form>

                    <pre id="checkoutOutput" class="bg-dark text-white rounded-4 p-3 mt-4 small mb-0" style="display:none"></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-5 position-sticky" style="top:120px">
                <div class="card-body p-4">
                    <h4 class="fw-black mb-3">Tóm tắt đơn hàng</h4>
                    <div id="cartSummary">Đang tải...</div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';

async function loadSummary() {
    const json = await ApiClient.get('/api/cart');
    const box = document.getElementById('cartSummary');

    if (!json.success) {
        box.innerHTML = `<div class="alert alert-danger rounded-4">${json.message}</div>
                         <a href="<?php echo BASE_URL; ?>/Auth/login" class="premium-btn premium-btn-primary d-inline-block text-decoration-none">Đăng nhập</a>`;
        return;
    }

    const items = json.data.items || [];
    if (items.length === 0) {
        box.innerHTML = `<div class="alert alert-warning rounded-4">Giỏ hàng đang trống.</div>
                         <a href="<?php echo BASE_URL; ?>/Product/list" class="btn btn-outline-danger rounded-pill fw-bold">Mua hàng</a>`;
        return;
    }

    box.innerHTML = items.map(i => `
        <div class="d-flex justify-content-between border-bottom py-3">
            <div>
                <div class="fw-black">${i.name}</div>
                <small class="text-secondary fw-semibold">Số lượng: ${i.quantity}</small>
            </div>
            <strong>${ApiClient.money(i.total)}</strong>
        </div>
    `).join('') + `
        <div class="d-flex justify-content-between align-items-end mt-4">
            <span class="fw-black">Tổng tiền</span>
            <span class="h3 text-danger fw-black">${ApiClient.money(json.data.total)}</span>
        </div>`;
}

document.getElementById('apiCheckoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const payload = {
        name: document.getElementById('name').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        address: document.getElementById('address').value.trim(),
        payment_method: document.getElementById('paymentMethod').value,
        note: document.getElementById('note').value.trim()
    };

    const json = await ApiClient.post('/api/orders', payload);
    const out = document.getElementById('checkoutOutput');
    out.style.display = 'block';
    out.textContent = JSON.stringify(json, null, 2);

    if (json.success) {
        document.getElementById('checkoutAlert').innerHTML = `
            <div class="alert alert-success rounded-4 border-0 fw-bold">
                <i class="bi bi-check-circle me-1"></i> ${json.message}. Mã đơn: ${json.data.order_code}
            </div>`;
        ApiClient.toast(json.message, 'success');
        loadSummary();
    } else {
        document.getElementById('checkoutAlert').innerHTML = `<div class="alert alert-danger rounded-4 fw-bold">${json.message}</div>`;
        ApiClient.toast(json.message, 'error');
    }
});

loadSummary();
</script>

<?php include 'app/views/layouts/footer.php'; ?>
