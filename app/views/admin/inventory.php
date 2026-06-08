<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Quản lý kho hàng</h2><div class="card-soft p-3"><table class="table"><tr><th>Sản phẩm</th><th>Danh mục</th><th>Tồn kho</th><th>Cập nhật</th></tr><?php foreach($products as $p): ?><tr><td><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($p->category_name, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo (int)$p->stock_quantity; ?></td><td><form method="post" class="d-flex gap-2" action="<?php echo BASE_URL; ?>/Admin/updateStock/<?php echo $p->id; ?>"><input class="form-control form-control-sm" name="stock_quantity" type="number" min="0" value="<?php echo (int)$p->stock_quantity; ?>"><button class="btn btn-sm btn-primary">Lưu</button></form></td></tr><?php endforeach; ?></table></div>

<?php include 'app/views/layouts/footer.php'; ?>
