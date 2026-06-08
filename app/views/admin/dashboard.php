<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Dashboard quản trị</h2><div class="row g-3 mb-3">
<?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-soft p-3"><div class="text-muted"><?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?></div><h3><?php echo $k==='revenue'? number_format($v,0,',','.').'đ' : $v; ?></h3></div></div><?php endforeach; ?></div>
<div class="card-soft p-3"><h5>Đơn mới</h5><table class="table"><tr><th>Mã</th><th>Khách</th><th>Tổng</th><th>Trạng thái</th></tr><?php foreach($recentOrders as $o): ?><tr><td><?php echo htmlspecialchars($o->order_code ?: $o->id, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($o->name, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo number_format((float)$o->final_amount,0,',','.'); ?>đ</td><td><?php echo htmlspecialchars($o->order_status, ENT_QUOTES, 'UTF-8'); ?></td></tr><?php endforeach; ?></table></div>

<?php include 'app/views/layouts/footer.php'; ?>
