<?php include 'app/views/layouts/header.php'; ?>

<div class="container py-5" style="max-width: 540px;">
    <div class="card-soft p-4">
        <h2 class="fw-bold mb-3 text-center">Đặt lại mật khẩu</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-bold">Mật khẩu mới</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nhập lại mật khẩu mới</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">Cập nhật mật khẩu</button>
        </form>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
