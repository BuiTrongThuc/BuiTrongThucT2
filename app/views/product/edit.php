<?php include 'app/views/layouts/header.php'; ?>

<section class="container py-4" style="max-width: 900px;">
    <div class="premium-page-hero mb-4">
        <span class="premium-hero-kicker"><i class="bi bi-box-seam"></i> Product API</span>
        <h1><?php echo !empty($product) ? 'Sửa sản phẩm' : 'Thêm sản phẩm'; ?></h1>
        <p>Biểu mẫu này gửi dữ liệu qua Web API, không xử lý thêm/sửa trực tiếp bằng form PHP truyền thống.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-5">
        <div class="card-body p-4 p-lg-5">
            <div id="formAlert"></div>

            <form id="apiProductForm">
                <input type="hidden" id="productId" value="<?php echo !empty($product) ? (int)$product->id : ''; ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input id="name" class="form-control form-control-lg rounded-4" required
                               value="<?php echo !empty($product) ? htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Giá</label>
                        <input id="price" type="number" class="form-control form-control-lg rounded-4" required
                               value="<?php echo !empty($product) ? htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select id="categoryId" class="form-select form-select-lg rounded-4"></select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên file ảnh</label>
                        <input id="image" class="form-control form-control-lg rounded-4" placeholder="iphone.jpg"
                               value="<?php echo !empty($product) ? htmlspecialchars($product->image ?? '', ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Mô tả</label>
                        <textarea id="description" class="form-control rounded-4" rows="5"><?php echo !empty($product) ? htmlspecialchars($product->description ?? '', ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <button class="premium-btn premium-btn-primary" type="submit">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <?php echo !empty($product) ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm'; ?>
                    </button>
                    <a class="btn btn-outline-secondary rounded-pill fw-bold px-4 d-inline-flex align-items-center" href="<?php echo BASE_URL; ?>/Product/list">
                        Hủy
                    </a>
                </div>
            </form>

            <pre id="apiOutput" class="bg-dark text-white rounded-4 p-3 mt-4 small mb-0" style="max-height:260px;overflow:auto;display:none"></pre>
        </div>
    </div>
</section>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';
const selectedCategory = '<?php echo !empty($product) ? (int)$product->category_id : ''; ?>';

async function loadCategories() {
    const json = await ApiClient.get('/api/categories');
    const select = document.getElementById('categoryId');
    select.innerHTML = '<option value="">-- Chọn danh mục --</option>';

    if (json.success && Array.isArray(json.data)) {
        json.data.forEach(c => {
            select.innerHTML += `<option value="${c.id}" ${String(c.id) === String(selectedCategory) ? 'selected' : ''}>${c.name}</option>`;
        });
    }
}

document.getElementById('apiProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('productId').value;
    const payload = {
        name: document.getElementById('name').value.trim(),
        description: document.getElementById('description').value.trim(),
        price: Number(document.getElementById('price').value),
        category_id: Number(document.getElementById('categoryId').value),
        image: document.getElementById('image').value.trim()
    };

    const json = id
        ? await ApiClient.put('/api/products/' + id, payload)
        : await ApiClient.post('/api/products', payload);

    const out = document.getElementById('apiOutput');
    out.style.display = 'block';
    out.textContent = JSON.stringify(json, null, 2);

    if (json.success) {
        document.getElementById('formAlert').innerHTML = `<div class="alert alert-success rounded-4 border-0 fw-bold">${json.message}</div>`;
        ApiClient.toast(json.message, 'success');
        setTimeout(() => location.href = '<?php echo BASE_URL; ?>/Product/list', 800);
    } else {
        document.getElementById('formAlert').innerHTML = `<div class="alert alert-danger rounded-4 fw-bold">${json.message}</div>`;
        ApiClient.toast(json.message, 'error');
    }
});

loadCategories();
</script>

<?php include 'app/views/layouts/footer.php'; ?>
