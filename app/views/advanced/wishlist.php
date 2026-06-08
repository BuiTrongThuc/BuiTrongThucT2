<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Danh sách yêu thích</h2><div class="row g-3">
<?php foreach ($items as $it): ?><div class="col-md-3"><div class="card-soft p-3 h-100"><img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($it->image ?: 'default-phone.png', ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid mb-2" style="height:160px;object-fit:contain"><h6><?php echo htmlspecialchars($it->name, ENT_QUOTES, 'UTF-8'); ?></h6><p class="text-danger fw-bold"><?php echo number_format((float)$it->price,0,',','.'); ?>đ</p><a class="btn btn-sm btn-primary" href="<?php echo BASE_URL; ?>/Product/show/<?php echo $it->product_id; ?>">Xem</a> <a class="btn btn-sm btn-outline-danger" href="<?php echo BASE_URL; ?>/Advanced/removeWishlist/<?php echo $it->id; ?>">Xoá</a></div></div><?php endforeach; ?>
<?php if (empty($items)): ?><p class="text-muted">Chưa có sản phẩm yêu thích.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
