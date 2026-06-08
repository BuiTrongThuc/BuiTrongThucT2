<?php include 'app/views/layouts/header.php'; ?>

<section class="admin-module-hero" style="--module-color: <?php echo htmlspecialchars($config['color'], ENT_QUOTES, 'UTF-8'); ?>;">
    <div class="admin-module-hero-left">
        <div class="admin-module-badge">
            <i class="bi <?php echo htmlspecialchars($config['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            Admin Module
        </div>
        <h1><?php echo htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($config['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="admin-module-hero-actions">
        <a href="<?php echo BASE_URL; ?>/Admin/center" class="btn btn-light rounded-pill fw-bold">
            <i class="bi bi-arrow-left"></i> Admin Center
        </a>
        <?php if (!empty($config['actions'][0])): ?>
            <a href="<?php echo htmlspecialchars($config['actions'][0]['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-pill fw-bold">
                <i class="bi bi-arrow-right-circle"></i> Mở chức năng
            </a>
        <?php endif; ?>
    </div>
</section>

<div class="admin-module-stats">
    <?php foreach ($config['stats'] as $stat): ?>
        <div class="admin-module-stat-card">
            <div class="stat-icon" style="background: <?php echo htmlspecialchars($config['color'], ENT_QUOTES, 'UTF-8'); ?>;">
                <i class="bi <?php echo htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            </div>
            <div>
                <span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                <strong><?php echo htmlspecialchars((string)$stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="admin-module-card h-100">
            <h3><i class="bi bi-list-check"></i> Chức năng cần có</h3>
            <div class="admin-feature-checklist">
                <?php foreach ($config['features'] as $feature): ?>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?php echo htmlspecialchars($feature, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-module-card h-100">
            <div class="module-card-head">
                <h3><i class="bi bi-lightning-charge"></i> Thao tác nhanh</h3>
                <span>Quick actions</span>
            </div>

            <div class="admin-action-grid">
                <?php foreach ($config['actions'] as $action): ?>
                    <a href="<?php echo htmlspecialchars($action['url'], ENT_QUOTES, 'UTF-8'); ?>" class="admin-action-btn">
                        <span><?php echo htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="admin-note-box mt-3">
                <strong>Ghi chú triển khai:</strong>
                Module này đã có trang quản trị riêng, thống kê, checklist nghiệp vụ và đường dẫn tới chức năng đang code. Các tích hợp bên thứ ba như vận chuyển thật, email hàng loạt, 2FA thật, Excel/PDF thật được giữ dạng điểm mở rộng để tránh làm hỏng project hiện tại.
            </div>
        </div>
    </div>
</div>

<div class="admin-module-card mt-4">
    <div class="module-card-head">
        <h3><i class="bi bi-table"></i> <?php echo htmlspecialchars($config['rowsTitle'], ENT_QUOTES, 'UTF-8'); ?></h3>
        <span><?php echo count($config['rows']); ?> dòng</span>
    </div>

    <?php if (!empty($config['rows'])): ?>
        <div class="table-responsive">
            <table class="table align-middle admin-module-table">
                <thead>
                    <tr>
                        <?php foreach ($config['columns'] as $label): ?>
                            <th><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($config['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($config['columns'] as $key => $label): ?>
                                <?php $value = isset($row->$key) ? $row->$key : ''; ?>
                                <td>
                                    <?php
                                    if (is_numeric($value) && in_array($key, array('price','amount','total_spent','revenue'), true)) {
                                        echo number_format((float)$value, 0, ',', '.') . 'đ';
                                    } else {
                                        echo htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <strong>Chưa có dữ liệu</strong>
            <p>Module đã sẵn sàng, dữ liệu sẽ hiện khi bảng tương ứng có bản ghi.</p>
        </div>
    <?php endif; ?>
</div>

<style>
    .admin-module-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        gap: 22px;
        align-items: center;
        border-radius: 30px;
        padding: 34px;
        color: #fff;
        background:
            radial-gradient(circle at 8% 10%, rgba(255,255,255,.24), transparent 28%),
            linear-gradient(135deg, var(--module-color), #111827);
        box-shadow: 0 24px 64px rgba(15,23,42,.18);
        margin-bottom: 22px;
    }
    .admin-module-hero::after {
        content: "";
        position: absolute;
        right: -100px;
        top: -110px;
        width: 300px;
        height: 300px;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
    }
    .admin-module-hero-left,
    .admin-module-hero-actions {
        position: relative;
        z-index: 1;
    }
    .admin-module-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        font-weight: 900;
        margin-bottom: 14px;
    }
    .admin-module-hero h1 {
        font-size: clamp(30px, 4vw, 48px);
        font-weight: 950;
        line-height: 1.05;
        margin-bottom: 12px;
    }
    .admin-module-hero p {
        max-width: 760px;
        margin: 0;
        color: rgba(255,255,255,.9);
        line-height: 1.65;
        font-weight: 650;
    }
    .admin-module-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .admin-module-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }
    .admin-module-stat-card,
    .admin-module-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(15,23,42,.07);
    }
    .admin-module-stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 23px;
        box-shadow: 0 12px 22px rgba(15,23,42,.16);
    }
    .admin-module-stat-card span {
        display: block;
        color: #64748b;
        font-size: 13px;
        font-weight: 850;
    }
    .admin-module-stat-card strong {
        display: block;
        color: #0f172a;
        font-size: 25px;
        font-weight: 950;
        line-height: 1.1;
    }
    .admin-module-card {
        padding: 22px;
    }
    .admin-module-card h3,
    .module-card-head h3 {
        font-size: 20px;
        font-weight: 950;
        color: #0f172a;
        margin: 0;
    }
    .module-card-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 16px;
    }
    .module-card-head span {
        border-radius: 999px;
        padding: 6px 10px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }
    .admin-feature-checklist {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }
    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 11px 12px;
        border-radius: 15px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        font-weight: 800;
        color: #334155;
    }
    .feature-item i {
        color: #16a34a;
        margin-top: 2px;
    }
    .admin-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .admin-action-btn {
        min-height: 54px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-radius: 17px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        text-decoration: none;
        font-weight: 950;
    }
    .admin-action-btn:hover {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #dc2626;
    }
    .admin-note-box {
        padding: 14px 16px;
        border-radius: 18px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #7c2d12;
        font-weight: 750;
        line-height: 1.55;
    }
    .admin-module-table th {
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 950;
        background: #f8fafc;
    }
    .admin-module-table td {
        font-weight: 750;
        color: #334155;
    }
    .empty-state {
        display: grid;
        place-items: center;
        min-height: 180px;
        color: #64748b;
        text-align: center;
    }
    .empty-state i {
        font-size: 42px;
        color: #cbd5e1;
        margin-bottom: 10px;
    }
    .empty-state strong {
        display: block;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
    }
    @media(max-width: 992px) {
        .admin-module-hero { flex-direction: column; align-items: flex-start; }
        .admin-module-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-action-grid { grid-template-columns: 1fr; }
    }
    @media(max-width: 576px) {
        .admin-module-stats { grid-template-columns: 1fr; }
        .admin-module-hero { padding: 24px; border-radius: 24px; }
    }
</style>

<?php include 'app/views/layouts/footer.php'; ?>
