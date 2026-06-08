<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Voucher / điểm thưởng</h2><div class="row g-3">
<?php foreach ($vouchers as $v): ?><div class="col-md-4"><div class="card-soft p-3"><h4><?php echo htmlspecialchars($v->code, ENT_QUOTES, 'UTF-8'); ?></h4><p><?php echo htmlspecialchars($v->name, ENT_QUOTES, 'UTF-8'); ?></p><b class="text-danger"><?php echo $v->type==='percent' ? ((float)$v->value.'%') : (number_format((float)$v->value,0,',','.').'đ'); ?></b><p class="small text-muted">Đơn tối thiểu <?php echo number_format((float)$v->min_order_value,0,',','.'); ?>đ</p></div></div><?php endforeach; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
