<?php include 'app/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-1">Quản lý người dùng</h2>
        <p class="text-secondary mb-0">Admin có thể xem, phân quyền, khóa hoặc mở khóa tài khoản.</p>
    </div>
</div>

<div class="card-soft p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người dùng</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Xác thực</th>
                <th>Ngày tạo</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $item): ?>
            <tr>
                <td><?php echo (int)$item->id; ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($item->avatar)): ?>
                            <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($item->avatar, ENT_QUOTES, 'UTF-8'); ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;" alt="Avatar">
                        <?php else: ?>
                            <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;"><i class="bi bi-person"></i></span>
                        <?php endif; ?>
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($item->full_name ?: $item->username, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="small text-secondary">@<?php echo htmlspecialchars($item->username, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($item->email ?? 'Chưa có', ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <form method="POST" action="<?php echo BASE_URL; ?>/User/updateRole/<?php echo (int)$item->id; ?>" class="d-flex gap-2 align-items-center">
                        <select name="role" class="form-select form-select-sm" style="min-width:110px;" <?php echo ((int)$item->id === (int)($_SESSION['user_id'] ?? 0)) ? 'title="Không nên tự hạ quyền tài khoản đang đăng nhập"' : ''; ?>>
                            <option value="user" <?php echo $item->role === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $item->role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Lưu</button>
                    </form>
                </td>
                <td>
                    <?php if ((int)$item->is_active === 1): ?>
                        <span class="badge bg-success">Đang hoạt động</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Đã khóa</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($item->email_verified_at)): ?>
                        <span class="badge bg-success">Đã xác thực</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Chưa xác thực</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($item->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <?php if ((int)$item->is_active === 1): ?>
                            <a class="btn btn-outline-dark" onclick="return confirm('Khóa tài khoản này?')" href="<?php echo BASE_URL; ?>/User/lock/<?php echo (int)$item->id; ?>">Khóa</a>
                        <?php else: ?>
                            <a class="btn btn-outline-success" href="<?php echo BASE_URL; ?>/User/unlock/<?php echo (int)$item->id; ?>">Mở</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
