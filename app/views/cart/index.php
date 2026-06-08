<?php include 'app/views/layouts/header.php'; ?>

<section class="container py-4">
    <div class="premium-page-hero mb-4">
        <span class="premium-hero-kicker"><i class="bi bi-cart3"></i> Giỏ hàng API</span>
        <h1>Giỏ hàng của bạn</h1>
        <p>Giỏ hàng được đồng bộ qua Web API, mọi thay đổi số lượng/xóa đều gọi fetch.</p>
    </div>

    <div id="cartAlert"></div>

    <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4">
            <div id="cartWrap"></div>
        </div>
    </div>
</section>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';

function productImageUrl(fileName) {
    if (!fileName) return '';
    if (fileName.startsWith('http')) return fileName;
    return window.API_BASE_URL + '/public/uploads/' + fileName;
}

async function loadCart() {
    const json = await ApiClient.get('/api/cart');
    const wrap = document.getElementById('cartWrap');

    if (!json.success) {
        wrap.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-shield-lock display-3 text-danger"></i>
                <h3 class="fw-black mt-3">Cần đăng nhập để xem giỏ hàng</h3>
                <p class="text-secondary fw-semibold">${json.message}</p>
                <a class="premium-btn premium-btn-primary d-inline-block text-decoration-none" href="<?php echo BASE_URL; ?>/Auth/login">Đăng nhập</a>
            </div>`;
        return;
    }

    const items = json.data.items || [];
    if (items.length === 0) {
        wrap.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-3 text-secondary"></i>
                <h3 class="fw-black mt-3">Giỏ hàng đang trống</h3>
                <p class="text-secondary fw-semibold">Hãy chọn một sản phẩm yêu thích để bắt đầu.</p>
                <a href="<?php echo BASE_URL; ?>/Product/list" class="premium-btn premium-btn-primary d-inline-block text-decoration-none">Mua hàng ngay</a>
            </div>`;
        return;
    }

    let html = `<div class="table-responsive">
        <table class="table align-middle">
            <thead><tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th style="width:150px">Số lượng</th>
                <th>Tổng</th>
                <th></th>
            </tr></thead><tbody>`;

    items.forEach(item => {
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:82px;height:82px;border-radius:22px;background:#f8fafc;display:flex;align-items:center;justify-content:center">
                            <img src="${productImageUrl(item.image || '')}" style="max-width:78%;max-height:78%;object-fit:contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <div class="fw-black">${item.name}</div>
                            <small class="text-secondary fw-semibold">Product ID: ${item.product_id}</small>
                        </div>
                    </div>
                </td>
                <td class="fw-black text-danger">${ApiClient.money(item.price)}</td>
                <td>
                    <input type="number" min="1" class="form-control rounded-pill fw-bold" value="${item.quantity}" onchange="updateQty(${item.product_id}, this.value)">
                </td>
                <td class="fw-black">${ApiClient.money(item.total)}</td>
                <td>
                    <button class="btn btn-outline-danger rounded-pill fw-bold" onclick="removeItem(${item.product_id})">Xóa</button>
                </td>
            </tr>`;
    });

    html += `</tbody></table></div>
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <button class="btn btn-outline-danger rounded-pill fw-bold px-4" onclick="clearCart()">Xóa toàn bộ</button>
            <div class="text-end ms-auto">
                <div class="text-secondary fw-bold">Tổng tiền</div>
                <div class="display-6 fw-black text-danger">${ApiClient.money(json.data.total)}</div>
                <a href="<?php echo BASE_URL; ?>/Cart/checkout" class="premium-btn premium-btn-primary d-inline-block text-decoration-none mt-2 px-5">Đặt hàng</a>
            </div>
        </div>`;

    wrap.innerHTML = html;
}

async function updateQty(productId, quantity) {
    const json = await ApiClient.put('/api/cart/' + productId, {quantity: Number(quantity)});
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    loadCart();
}

async function removeItem(productId) {
    const json = await ApiClient.delete('/api/cart/' + productId);
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    loadCart();
}

async function clearCart() {
    if (!confirm('Xóa toàn bộ giỏ hàng?')) return;
    const json = await ApiClient.delete('/api/cart');
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    loadCart();
}

loadCart();
</script>

<?php include 'app/views/layouts/footer.php'; ?>
