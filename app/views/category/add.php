<?php include 'app/views/layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h2 class="fw-bold mb-1">Thêm danh mục</h2>
        <p class="text-secondary mb-0">Tạo mới danh mục sản phẩm</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/Category/list" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
        Quay lại
    </a>
</div>

<div class="form-shell p-4">
    <form method="post">
        <div class="mb-3">
            <label class="form-label fw-bold">Tên danh mục</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['name']); ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Mô tả</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Lưu danh mục</button>
    </form>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
