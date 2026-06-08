<?php include 'app/views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold">Lịch sử mua hàng</h2>
    <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/Product/list">Tiếp tục mua hàng</a>
</div>
<div class="card-soft p-3">
<table class="table align-middle">
<thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
<tr>
<td><?php echo htmlspecialchars($o->order_code ?: ('DH'.$o->id), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($o->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
<td class="fw-bold text-danger"><?php echo number_format((float)$o->final_amount,0,',','.'); ?>đ</td>
<td><?php echo htmlspecialchars($o->payment_status, ENT_QUOTES, 'UTF-8'); ?></td>
<td><span class="badge bg-info"><?php echo htmlspecialchars($o->order_status, ENT_QUOTES, 'UTF-8'); ?></span></td>
<td><a class="btn btn-sm btn-primary" href="<?php echo BASE_URL; ?>/Advanced/orderDetail/<?php echo $o->id; ?>">Xem chi tiết</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($orders)): ?><tr><td colspan="6" class="text-center text-muted">Chưa có đơn hàng.</td></tr><?php endif; ?>
</tbody></table></div>

<?php include 'app/views/layouts/footer.php'; ?>
