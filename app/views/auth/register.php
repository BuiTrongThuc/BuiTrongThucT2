<?php include 'app/views/layouts/header.php'; ?>

<div class="container py-5" style="max-width: 560px;">
    <div class="card-soft p-4">
        <h2 class="fw-bold mb-3 text-center">Đăng ký</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($verifyLink)): ?>
            <div class="alert alert-info">
                <div class="fw-bold mb-1">Link xác thực email demo:</div>
                <a href="<?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <div class="small mt-2">Khi chạy local chưa cấu hình SMTP, bấm link này để giả lập email xác thực.</div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/Auth/register">
            <div class="mb-3">
                <label class="form-label fw-bold">Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Họ tên</label>
                <input type="text" name="full_name" class="form-control" placeholder="Có thể bỏ trống">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email xác thực</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nhập lại mật khẩu</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">
                Đăng ký
            </button>
        </form>

        <div class="text-center mt-3">
            Đã có tài khoản?
            <a href="<?php echo BASE_URL; ?>/Auth/login" class="text-danger fw-bold">
                Đăng nhập
            </a>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
