<?php include 'app/views/layouts/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-soft p-4 text-center h-100">
            <?php if (!empty($user->avatar)): ?>
                <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($user->avatar, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-circle mb-3" style="width:130px;height:130px;object-fit:cover;" alt="Avatar">
            <?php else: ?>
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:130px;height:130px;">
                    <i class="bi bi-person fs-1 text-secondary"></i>
                </div>
            <?php endif; ?>
            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user->full_name ?: $user->username, ENT_QUOTES, 'UTF-8'); ?></h4>
            <div class="text-secondary mb-2">@<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></div>
            <span class="badge <?php echo $user->role === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                <?php echo strtoupper(htmlspecialchars($user->role, ENT_QUOTES, 'UTF-8')); ?>
            </span>
            <?php if (!empty($user->email_verified_at)): ?>
                <span class="badge bg-success">Email đã xác thực</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Chưa xác thực email</span>
            <?php endif; ?>

            <?php
                $currentTierKey = $user->member_tier ?? 'bac';
                $currentTier = $tierDefinitions[$currentTierKey] ?? $tierDefinitions['bac'];
                $totalSpent = (float)($user->total_spent ?? 0);
            ?>
            <div class="member-rank-card <?php echo htmlspecialchars($currentTier['class'], ENT_QUOTES, 'UTF-8'); ?> mt-4">
                <div class="rank-label">Hạng thành viên</div>
                <div class="rank-name"><?php echo htmlspecialchars($currentTier['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="rank-spent">Tổng tiêu dùng: <?php echo number_format($totalSpent, 0, ',', '.'); ?>đ</div>
            </div>

            <div class="cultivation-card mt-3 text-start">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="small text-secondary fw-bold">Cảnh giới tu tiên</div>
                        <div class="fw-black"><?php echo htmlspecialchars($cultivationProgress['realm'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="level-pill">Lv. <?php echo (int)$cultivationProgress['level']; ?></div>
                </div>
                <div class="energy-bar">
                    <span style="width: <?php echo (int)$cultivationProgress['percent']; ?>%;"></span>
                </div>
                <div class="small text-secondary mt-2">
                    Linh khí: <?php echo (int)$cultivationProgress['exp']; ?>/<?php echo (int)$cultivationProgress['required']; ?>
                </div>
            </div>
            <div class="mt-4 d-grid gap-2">
                <a class="btn btn-outline-danger rounded-pill" href="<?php echo BASE_URL; ?>/User/changePassword">Đổi mật khẩu</a>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a class="btn btn-danger rounded-pill" href="<?php echo BASE_URL; ?>/User/admin">Quản lý người dùng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if (!empty($_SESSION['cultivation_notice'])): ?>
            <div class="alert alert-success rounded-4 fw-bold">
                <i class="bi bi-stars"></i>
                <?php echo htmlspecialchars($_SESSION['cultivation_notice'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['cultivation_notice']); ?>
            </div>
        <?php endif; ?>

        <div class="card-soft p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h3 class="fw-bold mb-1">Hệ thống tiêu dùng & tu tiên</h3>
                    <div class="text-secondary">Mua hàng và xem sản phẩm để nhận linh khí, tăng cấp và nâng hạng thành viên.</div>
                </div>
                <span class="badge bg-danger rounded-pill px-3 py-2">Gamification</span>
            </div>

            <div class="row g-3">
                <?php foreach ($tierDefinitions as $tierKey => $tier): ?>
                    <div class="col-md-4">
                        <div class="tier-benefit-card <?php echo htmlspecialchars($tier['class'], ENT_QUOTES, 'UTF-8'); ?> <?php echo ($currentTierKey === $tierKey) ? 'is-current' : ''; ?>">
                            <div class="tier-top">
                                <span><?php echo htmlspecialchars($tier['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if ($currentTierKey === $tierKey): ?>
                                    <strong>Đang dùng</strong>
                                <?php endif; ?>
                            </div>
                            <div class="tier-min">Từ <?php echo number_format((float)$tier['min_spent'], 0, ',', '.'); ?>đ</div>
                            <ul>
                                <?php foreach ($tier['benefits'] as $benefit): ?>
                                    <li><?php echo htmlspecialchars($benefit, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-soft p-4">
            <h3 class="fw-bold mb-3">Hồ sơ cá nhân</h3>
            <form method="POST" action="<?php echo BASE_URL; ?>/User/updateProfile" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ tên</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user->full_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user->phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea name="address" rows="3" class="form-control"><?php echo htmlspecialchars($user->address ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Ảnh đại diện</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text">Chấp nhận JPG, PNG, GIF, WEBP. Tối đa 2MB.</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger rounded-pill fw-bold mt-4 px-4">Lưu thay đổi</button>
            </form>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>

<style>
    .fw-black { font-weight: 900; }
    .member-rank-card {
        border-radius: 22px;
        padding: 18px;
        color: #fff;
        text-align: left;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .16);
    }
    .member-rank-card .rank-label {
        font-size: 12px;
        font-weight: 800;
        opacity: .9;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .member-rank-card .rank-name {
        font-size: 30px;
        line-height: 1.1;
        font-weight: 950;
        margin-top: 4px;
    }
    .member-rank-card .rank-spent {
        font-size: 13px;
        font-weight: 800;
        margin-top: 8px;
        opacity: .92;
    }
    .tier-silver {
        background: linear-gradient(135deg, #64748b, #cbd5e1);
    }
    .tier-gold {
        background: linear-gradient(135deg, #ca8a04, #facc15);
    }
    .tier-diamond {
        background: linear-gradient(135deg, #0891b2, #67e8f9 45%, #a855f7);
    }
    .cultivation-card {
        border: 1px solid #fee2e2;
        background: linear-gradient(180deg, #fff7f7, #ffffff);
        border-radius: 20px;
        padding: 16px;
    }
    .level-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        height: 38px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-weight: 900;
        box-shadow: 0 10px 20px rgba(220, 38, 38, .22);
    }
    .energy-bar {
        height: 14px;
        border-radius: 999px;
        background: #fee2e2;
        overflow: hidden;
    }
    .energy-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #ef4444, #f97316, #facc15);
        box-shadow: 0 6px 14px rgba(239, 68, 68, .28);
        transition: width .35s ease;
    }
    .tier-benefit-card {
        height: 100%;
        border-radius: 22px;
        padding: 18px;
        color: #fff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .12);
        position: relative;
        overflow: hidden;
    }
    .tier-benefit-card::after {
        content: "";
        position: absolute;
        width: 160px;
        height: 160px;
        border-radius: 999px;
        right: -70px;
        top: -70px;
        background: rgba(255,255,255,.22);
    }
    .tier-benefit-card.is-current {
        outline: 4px solid rgba(220, 38, 38, .15);
        transform: translateY(-2px);
    }
    .tier-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
        position: relative;
        z-index: 1;
    }
    .tier-top span {
        font-size: 22px;
        font-weight: 950;
    }
    .tier-top strong {
        font-size: 11px;
        border-radius: 999px;
        padding: 5px 8px;
        background: rgba(255,255,255,.22);
    }
    .tier-min {
        font-size: 13px;
        font-weight: 800;
        opacity: .92;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
    }
    .tier-benefit-card ul {
        margin: 0;
        padding-left: 18px;
        position: relative;
        z-index: 1;
    }
    .tier-benefit-card li {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }
</style>
