<?php include 'app/views/layouts/header.php'; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card-soft p-5 text-center">
    <div class="mb-3">
        <i class="bi bi-check-circle-fill text-success" style="font-size:56px;"></i>
    </div>

    <h2 class="fw-bold mb-3">Đặt hàng thành công</h2>

    <p class="text-secondary mb-4">
        Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được xử lý thành công.
    </p>

    <a href="<?php echo BASE_URL; ?>/Product/list" class="btn btn-danger rounded-pill px-4 fw-bold">
        Tiếp tục mua sắm
    </a>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
