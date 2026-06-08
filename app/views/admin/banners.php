<?php include 'app/views/layouts/header.php'; ?>

<section class="container py-4">
    <div class="premium-page-hero mb-4">
        <span class="premium-hero-kicker"><i class="bi bi-images"></i> Banner Admin</span>
        <h1>Quản lý banner</h1>
        <p>Admin thêm, sửa, xóa banner bằng Web API. Ảnh có thể nhập tên file hoặc upload trực tiếp.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-5">
                <div class="card-body p-4">
                    <h4 class="fw-black mb-3" id="bannerFormTitle">Thêm banner</h4>
                    <div id="bannerAlert"></div>

                    <form id="bannerForm">
                        <input type="hidden" id="bannerId">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề</label>
                            <input id="title" class="form-control" required placeholder="iPhone 15 Series - Giảm sốc cuối tuần">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả phụ</label>
                            <input id="subtitle" class="form-control" placeholder="Trả góp 0%, thu cũ đổi mới">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vị trí</label>
                            <select id="position" class="form-select">
                                <option value="home_main">Banner chính</option>
                                <option value="home_mini">Banner phụ</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Link khi bấm</label>
                            <input id="link" class="form-control" value="/Product/list">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên ảnh đã có trong public/uploads/banners</label>
                            <input id="image" class="form-control" placeholder="banner-iphone.jpg">
                            <small class="text-secondary fw-semibold">Có thể bỏ trống nếu upload file bên dưới.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload ảnh banner</label>
                            <input id="imageFile" type="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Thứ tự</label>
                                <input id="sortOrder" type="number" class="form-control" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <select id="isActive" class="form-select">
                                    <option value="1">Đang hiện</option>
                                    <option value="0">Ẩn</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 flex-wrap">
                            <button class="uv-btn uv-btn-red" type="submit">
                                <i class="bi bi-cloud-arrow-up"></i> Lưu banner
                            </button>
                            <button class="btn btn-outline-secondary rounded-pill fw-bold px-4" type="button" onclick="resetBannerForm()">Làm mới</button>
                        </div>
                    </form>

                    <pre id="bannerOutput" class="bg-dark text-white rounded-4 p-3 mt-4 small mb-0" style="display:none;max-height:260px;overflow:auto"></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-5">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h4 class="fw-black mb-1">Danh sách banner</h4>
                            <div class="text-secondary fw-semibold">Tải từ <code>GET /api/banners?admin=1</code></div>
                        </div>
                        <button class="btn btn-danger rounded-pill fw-bold px-4" onclick="loadBannersAdmin()">
                            <i class="bi bi-arrow-clockwise"></i> Tải lại
                        </button>
                    </div>

                    <div id="bannerList"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.banner-admin-thumb{
    width:150px;
    height:84px;
    border-radius:18px;
    background:linear-gradient(135deg,#fff1f2,#f8fafc);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    border:1px solid #fee2e2;
}
.banner-admin-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}
.banner-admin-item{
    border:1px solid rgba(15,23,42,.08);
    border-radius:24px;
    padding:16px;
    display:flex;
    gap:16px;
    align-items:center;
    justify-content:space-between;
    margin-bottom:12px;
    background:#fff;
    box-shadow:0 12px 32px rgba(15,23,42,.05);
}
@media(max-width:768px){
    .banner-admin-item{flex-direction:column;align-items:flex-start}
}
</style>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';

function bannerImageUrl(fileName) {
    if (!fileName) return '';
    if (fileName.startsWith('http')) return fileName;
    return window.API_BASE_URL + '/public/uploads/banners/' + fileName;
}

function showOutput(json) {
    const out = document.getElementById('bannerOutput');
    out.style.display = 'block';
    out.textContent = JSON.stringify(json, null, 2);
}

async function loadBannersAdmin() {
    const json = await ApiClient.get('/api/banners?admin=1');
    const box = document.getElementById('bannerList');

    if (!json.success) {
        box.innerHTML = `<div class="alert alert-danger rounded-4">${json.message}</div>`;
        return;
    }

    const items = (json.data && json.data.all) ? json.data.all : [];
    if (items.length === 0) {
        box.innerHTML = `<div class="alert alert-warning rounded-4">Chưa có banner nào. Hãy thêm banner mới.</div>`;
        return;
    }

    box.innerHTML = items.map(b => `
        <div class="banner-admin-item">
            <div class="d-flex align-items-center gap-3">
                <div class="banner-admin-thumb">
                    ${b.image ? `<img src="${bannerImageUrl(b.image)}" onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\\'bi bi-image fs-1 text-danger\\'></i>'">` : `<i class="bi bi-image fs-1 text-danger"></i>`}
                </div>
                <div>
                    <div class="fw-black">${b.title}</div>
                    <div class="text-secondary fw-semibold small">${b.subtitle || ''}</div>
                    <div class="mt-1">
                        <span class="badge text-bg-light">${b.position}</span>
                        <span class="badge ${Number(b.is_active) === 1 ? 'text-bg-success' : 'text-bg-secondary'}">${Number(b.is_active) === 1 ? 'Đang hiện' : 'Đang ẩn'}</span>
                        <span class="badge text-bg-danger">#${b.sort_order || 0}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-warning rounded-pill fw-bold" onclick='editBanner(${JSON.stringify(b).replace(/'/g, "&apos;")})'>Sửa</button>
                <button class="btn btn-outline-danger rounded-pill fw-bold" onclick="deleteBanner(${b.id})">Xóa</button>
            </div>
        </div>
    `).join('');
}

function editBanner(b) {
    document.getElementById('bannerFormTitle').textContent = 'Sửa banner #' + b.id;
    document.getElementById('bannerId').value = b.id;
    document.getElementById('title').value = b.title || '';
    document.getElementById('subtitle').value = b.subtitle || '';
    document.getElementById('position').value = b.position || 'home_main';
    document.getElementById('link').value = b.link || '/Product/list';
    document.getElementById('image').value = b.image || '';
    document.getElementById('sortOrder').value = b.sort_order || 0;
    document.getElementById('isActive').value = Number(b.is_active) === 1 ? '1' : '0';
    document.getElementById('imageFile').value = '';
    window.scrollTo({top:0, behavior:'smooth'});
}

function resetBannerForm() {
    document.getElementById('bannerFormTitle').textContent = 'Thêm banner';
    document.getElementById('bannerForm').reset();
    document.getElementById('bannerId').value = '';
    document.getElementById('link').value = '/Product/list';
    document.getElementById('sortOrder').value = 0;
    document.getElementById('isActive').value = 1;
    document.getElementById('bannerOutput').style.display = 'none';
}

document.getElementById('bannerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('bannerId').value;
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value.trim());
    formData.append('subtitle', document.getElementById('subtitle').value.trim());
    formData.append('position', document.getElementById('position').value);
    formData.append('link', document.getElementById('link').value.trim());
    formData.append('image', document.getElementById('image').value.trim());
    formData.append('sort_order', document.getElementById('sortOrder').value);
    formData.append('is_active', document.getElementById('isActive').value);

    const file = document.getElementById('imageFile').files[0];
    if (file) formData.append('image_file', file);

    const token = ApiClient.token();
    const response = await fetch(window.API_BASE_URL + '/api/banners' + (id ? '/' + id : ''), {
        method: 'POST',
        headers: token ? {'Authorization': 'Bearer ' + token} : {},
        body: formData
    });

    const json = await response.json();
    showOutput(json);
    ApiClient.toast(json.message, json.success ? 'success' : 'error');

    if (json.success) {
        resetBannerForm();
        loadBannersAdmin();
    }
});

async function deleteBanner(id) {
    if (!confirm('Xóa banner #' + id + '?')) return;
    const json = await ApiClient.delete('/api/banners/' + id);
    showOutput(json);
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    if (json.success) loadBannersAdmin();
}

loadBannersAdmin();
</script>

<?php include 'app/views/layouts/footer.php'; ?>
