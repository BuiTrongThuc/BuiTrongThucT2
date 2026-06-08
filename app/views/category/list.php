<?php include 'app/views/layouts/header.php'; ?>

<section class="container py-4">
    <div class="api-category-hero mb-4">
        <div>
            <span>Category Web API</span>
            <h1>Quản lý danh mục</h1>
            <p>Danh mục được tải bằng <code>GET /api/categories</code>; thêm/sửa/xóa bằng API.</p>
        </div>
        <button class="btn btn-light rounded-pill fw-bold px-4" onclick="openCategoryForm()">Thêm danh mục API</button>
    </div>

    <div id="categoryAlert"></div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div id="categoryWrap">Đang tải...</div>
        </div>
    </div>
</section>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="categoryModalTitle">Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryIdValue">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên danh mục</label>
                        <input id="categoryName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả</label>
                        <textarea id="categoryDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <pre id="categoryOutput" class="bg-dark text-white rounded-4 p-3 small mb-0"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-pill fw-bold">Lưu bằng API</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.api-category-hero{background:linear-gradient(135deg,#d70018,#111827);color:#fff;padding:30px;border-radius:28px;display:flex;justify-content:space-between;align-items:center;gap:18px;box-shadow:0 22px 52px rgba(15,23,42,.16)}
.api-category-hero span{background:rgba(255,255,255,.16);border-radius:999px;padding:7px 12px;font-weight:950}
.api-category-hero h1{font-weight:950;margin:10px 0 8px}
.api-category-hero p{margin:0;color:rgba(255,255,255,.88);font-weight:700}
.api-category-hero code{color:white;background:rgba(255,255,255,.16);border-radius:999px;padding:2px 8px}
</style>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';
let categoryModal;

document.addEventListener('DOMContentLoaded', () => {
    categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    loadCategoriesApi();
});

async function loadCategoriesApi() {
    const json = await ApiClient.get('/api/categories');
    const wrap = document.getElementById('categoryWrap');

    if (!json.success) {
        wrap.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
        return;
    }

    let html = `<div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>ID</th><th>Tên</th><th>Mô tả</th><th style="width:240px">Thao tác</th></tr></thead><tbody>`;
    json.data.forEach(c => {
        html += `<tr>
            <td class="fw-bold">${c.id}</td>
            <td class="fw-bold">${c.name}</td>
            <td>${c.description || ''}</td>
            <td>
                <button class="btn btn-warning btn-sm rounded-pill fw-bold" onclick='openCategoryForm(${JSON.stringify(c)})'>Sửa API</button>
                <button class="btn btn-outline-danger btn-sm rounded-pill fw-bold" onclick="deleteCategoryApi(${c.id})">Xóa API</button>
            </td>
        </tr>`;
    });
    html += `</tbody></table></div>`;
    wrap.innerHTML = html;
}

function openCategoryForm(category = null) {
    document.getElementById('categoryIdValue').value = category ? category.id : '';
    document.getElementById('categoryName').value = category ? category.name : '';
    document.getElementById('categoryDescription').value = category ? (category.description || '') : '';
    document.getElementById('categoryOutput').textContent = '';
    document.getElementById('categoryModalTitle').textContent = category ? 'Sửa danh mục bằng API' : 'Thêm danh mục bằng API';
    categoryModal.show();
}

document.getElementById('categoryForm').addEventListener('submit', async e => {
    e.preventDefault();

    const id = document.getElementById('categoryIdValue').value;
    const payload = {
        name: document.getElementById('categoryName').value.trim(),
        description: document.getElementById('categoryDescription').value.trim()
    };

    const json = id ? await ApiClient.put('/api/categories/' + id, payload) : await ApiClient.post('/api/categories', payload);
    document.getElementById('categoryOutput').textContent = JSON.stringify(json, null, 2);

    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    if (json.success) {
        categoryModal.hide();
        loadCategoriesApi();
    }
});

async function deleteCategoryApi(id) {
    if (!confirm('Xóa danh mục bằng DELETE /api/categories/' + id + '?')) return;
    const json = await ApiClient.delete('/api/categories/' + id);
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    loadCategoriesApi();
}
</script>

<?php include 'app/views/layouts/footer.php'; ?>
