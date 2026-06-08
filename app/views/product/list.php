<?php include 'app/views/layouts/header.php'; ?>

<section class="container-fluid px-lg-4 py-4">
    <div class="uv-shell">
        <div class="uv-hero mb-3">
            <div class="uv-hero-inner">
                <div>
                    <span class="uv-kicker">
                        <i class="bi bi-cpu-fill"></i> TECHSTORE NEXT-GEN
                    </span>
                    <h1 class="uv-gradient-text">Công nghệ<br>búng ra tương lai</h1>
                    <p>
                        Flagship smartphone, laptop hiệu năng cao, phụ kiện gaming và âm thanh cao cấp.
                        Tất cả được tải realtime bằng Web API.
                    </p>

                    <div class="uv-actions">
                        <button class="uv-btn uv-btn-primary" onclick="document.getElementById('productSection').scrollIntoView({behavior:'smooth'})">
                            <i class="bi bi-lightning-charge-fill"></i> Săn deal ngay
                        </button>
                        <button class="uv-btn uv-btn-dark" onclick="selectCategory('1')">
                            <i class="bi bi-phone"></i> Xem điện thoại
                        </button>
                    </div>

                    <div class="uv-tags">
                        <span>Trả góp 0%</span>
                        <span>Giảm đến 5 triệu</span>
                        <span>Giao nhanh 2h</span>
                        <span>Chính hãng</span>
                    </div>
                </div>

                <div class="uv-stage">
                    <div class="uv-ring"></div>
                    <div class="uv-device">
                        <img id="heroMainImage" src="" alt="" style="display:none">
                        <div id="heroMainFallback" class="text-center position-relative" style="z-index:2">
                            <i class="bi bi-phone display-1"></i>
                            <div class="uv-device-name">Ultra Deal</div>
                        </div>
                        <div id="heroMainName" class="uv-device-name"></div>
                    </div>
                    <div class="uv-mini-product uv-float-1">
                        <img id="heroFloatOne" src="" alt="" style="display:none">
                        <i id="heroFloatOneIcon" class="bi bi-laptop"></i>
                    </div>
                    <div class="uv-mini-product uv-float-2">
                        <img id="heroFloatTwo" src="" alt="" style="display:none">
                        <i id="heroFloatTwoIcon" class="bi bi-headphones"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="uv-deal-grid">
            <div class="uv-deal uv-deal-1">
                <small>MACBOOK PRO</small>
                <h3>Nâng cấp M-Series</h3>
                <p>Trợ giá sinh viên</p>
            </div>
            <div class="uv-deal uv-deal-2">
                <small>GALAXY A SERIES</small>
                <h3>Giảm sốc hôm nay</h3>
                <p>Trả góp 0%</p>
            </div>
            <div class="uv-deal uv-deal-3">
                <small>LAPTOP ONLINE</small>
                <h3>Giảm đến 5 triệu</h3>
                <p>Miễn phí giao hàng</p>
            </div>
            <div class="uv-deal uv-deal-4">
                <small>ACCESSORY WEEK</small>
                <h3>Phụ kiện gaming</h3>
                <p>Mua 1 tặng 1</p>
            </div>
        </div>

        <div class="card border-0 uv-filter mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3">
                        <label class="form-label fw-bold">Tìm kiếm</label>
                        <input id="search" class="form-control" placeholder="Bạn muốn mua gì hôm nay?">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select id="categoryId" class="form-select">
                            <option value="">Tất cả</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold">Sắp xếp</label>
                        <select id="sort" class="form-select">
                            <option value="newest">Mới nhất</option>
                            <option value="price_asc">Giá tăng</option>
                            <option value="price_desc">Giá giảm</option>
                            <option value="name_asc">Tên A-Z</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold">Giá từ</label>
                        <input id="minPrice" type="number" class="form-control" placeholder="0">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label fw-bold">Giá đến</label>
                        <input id="maxPrice" type="number" class="form-control" placeholder="50000000">
                    </div>
                    <div class="col-lg-1">
                        <button id="btnFilter" class="btn btn-danger w-100 fw-bold rounded-pill" style="height:52px">Lọc</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="categoryPills" class="uv-category-pills"></div>

        <div id="productSection" class="uv-section-head">
            <div>
                <h2>Sản phẩm nổi bật</h2>
                <p>Danh sách sản phẩm gọi từ Web API, giao diện render bằng JavaScript.</p>
            </div>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="uv-btn uv-btn-dark" href="<?php echo BASE_URL; ?>/Admin/banners">
                        <i class="bi bi-images"></i> Quản lý banner
                    </a>
                    <a class="uv-btn uv-btn-red" href="<?php echo BASE_URL; ?>/Product/add">
                        <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div id="productGrid" class="row g-4"></div>
        <div id="pagination" class="d-flex justify-content-center gap-2 mt-4"></div>
    </div>
</section>

<script src="<?php echo BASE_URL; ?>/public/js/api-client.js"></script>
<script>
window.API_BASE_URL = '<?php echo BASE_URL; ?>';
let currentPage = 1;

function productImageUrl(fileName) {
    if (!fileName) return '';
    if (fileName.startsWith('http')) return fileName;
    return window.API_BASE_URL + '/public/uploads/' + fileName;
}

function selectCategory(id) {
    document.getElementById('categoryId').value = id || '';
    currentPage = 1;
    document.querySelectorAll('.uv-pill').forEach(btn => {
        btn.classList.toggle('active', String(btn.dataset.id || '') === String(id || ''));
    });
    loadProducts();
}

function setSmartImage(imgId, iconId, product) {
    if (!product || !product.image) return;
    const img = document.getElementById(imgId);
    const icon = iconId ? document.getElementById(iconId) : null;
    img.src = productImageUrl(product.image);
    img.onload = () => {
        img.style.display = 'block';
        if (icon) icon.style.display = 'none';
    };
}

function hydrateHeroProducts(items) {
    if (!items || items.length === 0) return;
    const phone = items.find(p => Number(p.category_id) === 1) || items[0];
    const laptop = items.find(p => Number(p.category_id) === 2) || items[1];
    const audio = items.find(p => Number(p.category_id) === 3 || Number(p.category_id) === 5) || items[2];

    const mainImg = document.getElementById('heroMainImage');
    mainImg.src = productImageUrl(phone.image || '');
    mainImg.onload = () => {
        mainImg.style.display = 'block';
        document.getElementById('heroMainFallback').style.display = 'none';
    };
    document.getElementById('heroMainName').textContent = phone.name || '';

    setSmartImage('heroFloatOne', 'heroFloatOneIcon', laptop);
    setSmartImage('heroFloatTwo', 'heroFloatTwoIcon', audio);
}

async function loadCategories() {
    const json = await ApiClient.get('/api/categories');
    const select = document.getElementById('categoryId');
    const pills = document.getElementById('categoryPills');

    pills.innerHTML = `<button class="uv-pill active" data-id="" onclick="selectCategory('')">Tất cả</button>`;

    if (json.success && Array.isArray(json.data)) {
        json.data.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            pills.innerHTML += `<button class="uv-pill" data-id="${c.id}" onclick="selectCategory('${c.id}')">${c.name}</button>`;
        });
    }
}

function queryString() {
    const params = new URLSearchParams();
    params.set('page', currentPage);
    params.set('limit', 12);
    const search = document.getElementById('search').value.trim();
    const categoryId = document.getElementById('categoryId').value;
    const sort = document.getElementById('sort').value;
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    if (search) params.set('search', search);
    if (categoryId) params.set('category_id', categoryId);
    if (sort) params.set('sort', sort);
    if (minPrice) params.set('min_price', minPrice);
    if (maxPrice) params.set('max_price', maxPrice);
    return params.toString();
}

async function addToCart(productId) {
    const json = await ApiClient.post('/api/cart', {product_id: productId, quantity: 1});
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    if (!json.success && json.status === 401) {
        setTimeout(() => location.href = '<?php echo BASE_URL; ?>/Auth/login', 800);
    }
}

async function deleteProduct(productId) {
    if (!confirm('Xóa sản phẩm này?')) return;
    const json = await ApiClient.delete('/api/products/' + productId);
    ApiClient.toast(json.message, json.success ? 'success' : 'error');
    if (json.success) loadProducts();
}

async function loadProducts() {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = `<div class="col-12 text-center py-5 text-secondary fw-bold">Đang tải sản phẩm...</div>`;

    const json = await ApiClient.get('/api/products?' + queryString());
    if (!json.success) {
        grid.innerHTML = `<div class="col-12"><div class="alert alert-danger">${json.message}</div></div>`;
        return;
    }

    const items = json.data.items || [];
    hydrateHeroProducts(items);

    if (items.length === 0) {
        grid.innerHTML = `<div class="col-12"><div class="alert alert-warning rounded-4">Không có sản phẩm phù hợp.</div></div>`;
        return;
    }

    grid.innerHTML = '';
    items.forEach(p => {
        const discount = Number(p.discount_percent || 0);
        const badge = p.is_flash_sale == 1 ? 'Flash Sale' : (p.is_best_seller == 1 ? 'Bán chạy' : (p.is_new == 1 ? 'Mới' : 'Hot'));
        grid.innerHTML += `
            <div class="col-sm-6 col-lg-3">
                <div class="uv-product-card">
                    <div class="uv-product-media">
                        <div class="uv-badges">
                            <span class="uv-badge">${badge}</span>
                            ${discount > 0 ? `<span class="uv-badge dark">-${discount.toFixed(0)}%</span>` : ''}
                        </div>
                        <img src="${productImageUrl(p.image || '')}" onerror="this.style.display='none'">
                    </div>
                    <div class="uv-product-body">
                        <span class="badge text-bg-light align-self-start mb-2">${p.category_name || 'Sản phẩm'}</span>
                        <h5 class="uv-product-title">${p.name}</h5>
                        <p class="uv-product-desc">${p.description || ''}</p>
                        <div class="uv-price-row">
                            <span class="uv-price">${ApiClient.money(p.price)}</span>
                            ${p.old_price ? `<span class="uv-old">${ApiClient.money(p.old_price)}</span>` : ''}
                        </div>
                        <div class="uv-actions-card">
                            <a href="${window.API_BASE_URL}/Product/show/${p.id}" class="btn btn-outline-dark">Chi tiết</a>
                            <button class="btn btn-danger" onclick="addToCart(${p.id})">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                            ${ApiClient.user()?.role === 'admin' ? `
                                <a href="${window.API_BASE_URL}/Product/edit/${p.id}" class="btn btn-warning">Sửa</a>
                                <button class="btn btn-outline-danger" onclick="deleteProduct(${p.id})">Xóa</button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    renderPagination(json.data.pagination);
}

function renderPagination(pagination) {
    const box = document.getElementById('pagination');
    if (!pagination || pagination.total_pages <= 1) {
        box.innerHTML = '';
        return;
    }

    box.innerHTML = '';
    for (let i = 1; i <= pagination.total_pages; i++) {
        box.innerHTML += `<button class="btn ${i === pagination.page ? 'btn-danger' : 'btn-outline-danger'} rounded-pill fw-bold" onclick="currentPage=${i};loadProducts()">${i}</button>`;
    }
}

document.getElementById('btnFilter').addEventListener('click', () => { currentPage = 1; loadProducts(); });
document.getElementById('search').addEventListener('keyup', e => { if (e.key === 'Enter') { currentPage = 1; loadProducts(); } });

loadCategories();
loadProducts();
</script>

<?php include 'app/views/layouts/footer.php'; ?>
