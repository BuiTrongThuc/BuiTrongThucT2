<?php include 'app/views/layouts/header.php'; ?>
<section class="container my-4">
    <div class="p-4 rounded-4 bg-danger text-white shadow-sm mb-4">
        <h1 class="fw-black mb-2">Demo gọi API sản phẩm bằng fetch</h1>
        <p class="mb-0">Trang này không lấy dữ liệu trực tiếp bằng PHP. Dữ liệu được tải từ <code class="text-white">/api/products</code>.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Tìm kiếm</label>
                    <input id="search" class="form-control" placeholder="iphone, laptop...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Sắp xếp</label>
                    <select id="sort" class="form-select">
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                        <option value="name_asc">Tên A-Z</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="btnLoad" class="btn btn-danger w-100 fw-bold">Tải API</button>
                </div>
                <div class="col-md-3">
                    <a href="<?php echo BASE_URL; ?>/ApiDemo/login" class="btn btn-outline-danger w-100 fw-bold">Demo Login JWT</a>
                </div>
            </div>
        </div>
    </div>

    <pre id="apiInfo" class="bg-dark text-white p-3 rounded-4 small"></pre>
    <div id="products" class="row g-3"></div>
</section>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';

async function loadProducts() {
    const search = document.getElementById('search').value;
    const sort = document.getElementById('sort').value;
    const url = `${BASE_URL}/api/products?search=${encodeURIComponent(search)}&sort=${encodeURIComponent(sort)}&limit=12`;
    const res = await fetch(url);
    const json = await res.json();

    document.getElementById('apiInfo').textContent = 'GET ' + url + '\n\n' + JSON.stringify(json, null, 2);

    const wrap = document.getElementById('products');
    wrap.innerHTML = '';
    const items = json.data && json.data.items ? json.data.items : [];
    items.forEach(p => {
        wrap.innerHTML += `
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px">
                        <img src="${BASE_URL}/public/uploads/${p.image || ''}" style="max-height:150px;max-width:95%" onerror="this.style.display='none'">
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold">${p.name}</h5>
                        <div class="text-danger fw-black">${Number(p.price).toLocaleString('vi-VN')}đ</div>
                        <small class="text-secondary">${p.category_name || ''}</small>
                    </div>
                </div>
            </div>
        `;
    });
}

document.getElementById('btnLoad').addEventListener('click', loadProducts);
loadProducts();
</script>
<?php include 'app/views/layouts/footer.php'; ?>
