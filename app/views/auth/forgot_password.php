<?php include 'app/views/layouts/header.php'; ?>

<div class="container py-5" style="max-width: 540px;">
    <div class="card-soft p-4">
        <h2 class="fw-bold mb-3 text-center">Quên mật khẩu</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($resetLink)): ?>
            <div class="alert alert-info">
                <div class="fw-bold mb-1">Link đặt lại mật khẩu demo:</div>
                <a href="<?php echo htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <div class="small mt-2">Link có hiệu lực 30 phút theo dữ liệu trong database.</div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/Auth/forgotPassword">
            <div class="mb-3">
                <label class="form-label fw-bold">Email tài khoản</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">Tạo link đặt lại mật khẩu</button>
        </form>

        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/Auth/login" class="text-danger fw-bold">Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
