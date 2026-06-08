<?php include 'app/views/layouts/header.php'; ?>

<section class="admin-hero mb-4">
    <div class="admin-hero-content">
        <span class="admin-kicker"><i class="bi bi-command"></i> ADMIN CENTER</span>
        <h1>Trung tâm quản trị website bán điện thoại</h1>
        <p>Gom toàn bộ nghiệp vụ quản trị vào một trang riêng: sản phẩm, kho, đơn hàng, khách hàng, marketing, báo cáo, phân quyền, cài đặt và hỗ trợ.</p>
    </div>
    <div class="admin-hero-panel">
        <div class="admin-hero-number"><?php echo number_format((float)$stats['revenue'], 0, ',', '.'); ?>đ</div>
        <div class="admin-hero-label">Tổng doanh thu ghi nhận</div>
        <a href="<?php echo BASE_URL; ?>/Admin/dashboard" class="admin-hero-btn">Xem báo cáo <i class="bi bi-arrow-right"></i></a>
    </div>
</section>


<div class="admin-center-shortcuts mb-4">
    <a href="<?php echo BASE_URL; ?>/Admin/productsCenter"><i class="bi bi-box-seam"></i> Sản phẩm</a>
    <a href="<?php echo BASE_URL; ?>/Admin/inventoryCenter"><i class="bi bi-building-gear"></i> Kho</a>
    <a href="<?php echo BASE_URL; ?>/Admin/ordersCenter"><i class="bi bi-receipt"></i> Đơn hàng</a>
    <a href="<?php echo BASE_URL; ?>/Admin/customersCenter"><i class="bi bi-people"></i> Khách hàng</a>
    <a href="<?php echo BASE_URL; ?>/Admin/marketingCenter"><i class="bi bi-megaphone"></i> Marketing</a>
    <a href="<?php echo BASE_URL; ?>/Admin/reportsCenter"><i class="bi bi-graph-up"></i> Báo cáo</a>
    <a href="<?php echo BASE_URL; ?>/Admin/staffCenter"><i class="bi bi-shield-lock"></i> Phân quyền</a>
    <a href="<?php echo BASE_URL; ?>/Admin/settingsCenter"><i class="bi bi-sliders"></i> Cài đặt</a>
    <a href="<?php echo BASE_URL; ?>/Admin/supportCenter"><i class="bi bi-chat-dots"></i> Hỗ trợ</a>
</div>

<div class="admin-stat-grid mb-4">
    <div class="admin-stat-card">
        <i class="bi bi-box-seam"></i>
        <span>Sản phẩm</span>
        <strong><?php echo number_format((int)$stats['products']); ?></strong>
    </div>
    <div class="admin-stat-card">
        <i class="bi bi-receipt"></i>
        <span>Đơn hàng</span>
        <strong><?php echo number_format((int)$stats['orders']); ?></strong>
    </div>
    <div class="admin-stat-card">
        <i class="bi bi-hourglass-split"></i>
        <span>Đơn chờ xử lý</span>
        <strong><?php echo number_format((int)$stats['pending_orders']); ?></strong>
    </div>
    <div class="admin-stat-card">
        <i class="bi bi-people"></i>
        <span>Khách hàng</span>
        <strong><?php echo number_format((int)$stats['users']); ?></strong>
    </div>
    <div class="admin-stat-card">
        <i class="bi bi-exclamation-triangle"></i>
        <span>Sắp hết hàng</span>
        <strong><?php echo number_format((int)$stats['low_stock']); ?></strong>
    </div>
</div>

<div class="admin-toolbar mb-4">
    <div>
        <h2 class="mb-1">Danh mục chức năng quản trị</h2>
        <p class="mb-0 text-secondary">Bấm vào từng module để đi tới chức năng đang có. Các mục chưa code sâu được giữ như checklist mở rộng.</p>
    </div>
    <div class="admin-toolbar-actions">
        <a href="<?php echo BASE_URL; ?>/Product/add" class="btn btn-danger rounded-pill fw-bold">
            <i class="bi bi-plus-circle"></i> Thêm sản phẩm
        </a>
        <a href="<?php echo BASE_URL; ?>/Admin/orders" class="btn btn-outline-danger rounded-pill fw-bold">
            <i class="bi bi-truck"></i> Xử lý đơn
        </a>
    </div>
</div>

<div class="admin-module-grid">
    <?php foreach ($adminModules as $module): ?>
        <article class="admin-module-card <?php echo htmlspecialchars($module['color'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="admin-module-head">
                <div class="admin-module-icon">
                    <i class="bi <?php echo htmlspecialchars($module['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                </div>
                <div>
                    <h3><?php echo htmlspecialchars($module['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($module['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>

            <div class="admin-link-row">
                <?php foreach ($module['links'] as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="admin-feature-list">
                <?php foreach ($module['features'] as $feature): ?>
                    <span><i class="bi bi-check2-circle"></i><?php echo htmlspecialchars($feature, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<style>
    .admin-hero {
        position: relative;
        overflow: hidden;
        border-radius: 32px;
        padding: 34px;
        min-height: 260px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 24px;
        align-items: stretch;
        background:
            radial-gradient(circle at 10% 10%, rgba(255,255,255,.25), transparent 32%),
            linear-gradient(135deg, #b90016 0%, #e11d48 48%, #7c2d12 100%);
        color: #fff;
        box-shadow: 0 24px 70px rgba(185, 0, 22, .24);
    }
    .admin-hero::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        border-radius: 999px;
        right: -110px;
        top: -120px;
        background: rgba(255,255,255,.18);
    }
    .admin-hero-content,
    .admin-hero-panel {
        position: relative;
        z-index: 1;
    }
    .admin-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        font-weight: 900;
        color: #fff7c2;
        margin-bottom: 14px;
    }
    .admin-hero h1 {
        font-size: clamp(30px, 4vw, 52px);
        line-height: 1.05;
        font-weight: 950;
        margin-bottom: 14px;
    }
    .admin-hero p {
        max-width: 760px;
        font-size: 16px;
        line-height: 1.7;
        color: rgba(255,255,255,.9);
        margin-bottom: 0;
    }
    .admin-hero-panel {
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 26px;
        padding: 24px;
        background: rgba(255,255,255,.14);
        backdrop-filter: blur(10px);
        align-self: stretch;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.16);
    }
    .admin-hero-number {
        font-size: 34px;
        font-weight: 950;
        line-height: 1.1;
    }
    .admin-hero-label {
        font-weight: 800;
        opacity: .85;
        margin: 10px 0 20px;
    }
    .admin-hero-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: fit-content;
        padding: 12px 18px;
        border-radius: 999px;
        background: #fff;
        color: #dc2626;
        text-decoration: none;
        font-weight: 900;
    }
    .admin-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }
    .admin-stat-card {
        border-radius: 22px;
        background: #fff;
        border: 1px solid #f1f5f9;
        padding: 18px;
        box-shadow: 0 12px 30px rgba(15,23,42,.07);
    }
    .admin-stat-card i {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff1f2;
        color: #dc2626;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .admin-stat-card span {
        display: block;
        font-size: 13px;
        color: #64748b;
        font-weight: 800;
    }
    .admin-stat-card strong {
        display: block;
        font-size: 26px;
        font-weight: 950;
        color: #0f172a;
    }
    .admin-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #f1f5f9;
        box-shadow: 0 12px 28px rgba(15,23,42,.06);
    }
    .admin-toolbar h2 {
        font-size: 24px;
        font-weight: 950;
    }
    .admin-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .admin-module-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .admin-module-card {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 22px;
        background: #fff;
        border: 1px solid #edf2f7;
        box-shadow: 0 16px 34px rgba(15,23,42,.08);
        min-height: 330px;
    }
    .admin-module-card::after {
        content: "";
        position: absolute;
        width: 160px;
        height: 160px;
        border-radius: 999px;
        right: -70px;
        top: -70px;
        background: var(--module-soft, #fee2e2);
    }
    .admin-module-head {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 18px;
    }
    .admin-module-icon {
        flex: 0 0 auto;
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--module-bg, #ef4444);
        color: #fff;
        font-size: 25px;
        box-shadow: 0 12px 24px var(--module-shadow, rgba(239,68,68,.25));
    }
    .admin-module-card h3 {
        margin: 0 0 8px;
        font-size: 19px;
        font-weight: 950;
        color: #0f172a;
    }
    .admin-module-card p {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
        color: #64748b;
        font-weight: 600;
    }
    .admin-link-row {
        position: relative;
        z-index: 1;
        display: grid;
        gap: 8px;
        margin-bottom: 16px;
    }
    .admin-link-row a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 42px;
        border-radius: 14px;
        padding: 10px 12px;
        background: #f8fafc;
        color: #111827;
        text-decoration: none;
        font-weight: 900;
        border: 1px solid #eef2f7;
    }
    .admin-link-row a:hover {
        color: var(--module-bg, #dc2626);
        background: #fff;
    }
    .admin-feature-list {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .admin-feature-list span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--module-soft, #fff1f2);
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }
    .admin-red { --module-bg:#ef4444; --module-soft:#fee2e2; --module-shadow:rgba(239,68,68,.24); }
    .admin-orange { --module-bg:#f97316; --module-soft:#ffedd5; --module-shadow:rgba(249,115,22,.24); }
    .admin-blue { --module-bg:#2563eb; --module-soft:#dbeafe; --module-shadow:rgba(37,99,235,.24); }
    .admin-purple { --module-bg:#7c3aed; --module-soft:#ede9fe; --module-shadow:rgba(124,58,237,.24); }
    .admin-pink { --module-bg:#db2777; --module-soft:#fce7f3; --module-shadow:rgba(219,39,119,.24); }
    .admin-green { --module-bg:#16a34a; --module-soft:#dcfce7; --module-shadow:rgba(22,163,74,.24); }
    .admin-dark { --module-bg:#111827; --module-soft:#e5e7eb; --module-shadow:rgba(17,24,39,.20); }
    .admin-cyan { --module-bg:#0891b2; --module-soft:#cffafe; --module-shadow:rgba(8,145,178,.22); }
    .admin-teal { --module-bg:#0f766e; --module-soft:#ccfbf1; --module-shadow:rgba(15,118,110,.22); }
    @media (max-width: 1200px) {
        .admin-module-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .admin-hero { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .admin-module-grid, .admin-stat-grid { grid-template-columns: 1fr; }
        .admin-toolbar { flex-direction: column; align-items: flex-start; }
        .admin-hero { padding: 24px; border-radius: 24px; }
    }

    .admin-center-shortcuts {
        display: grid;
        grid-template-columns: repeat(9, minmax(0, 1fr));
        gap: 10px;
    }
    .admin-center-shortcuts a {
        min-height: 58px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid #fee2e2;
        box-shadow: 0 10px 24px rgba(15,23,42,.06);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #111827;
        text-decoration: none;
        font-weight: 950;
        font-size: 13px;
        text-align: center;
        padding: 10px;
    }
    .admin-center-shortcuts a:hover {
        background: #fff1f2;
        color: #dc2626;
        transform: translateY(-2px);
    }
    .admin-center-shortcuts i { color: #dc2626; font-size: 18px; }
    @media(max-width:1200px){ .admin-center-shortcuts{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media(max-width:576px){ .admin-center-shortcuts{ grid-template-columns: 1fr; } }

</style>

<?php include 'app/views/layouts/footer.php'; ?>
