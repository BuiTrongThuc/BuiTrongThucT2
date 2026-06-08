<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Chi tiết đơn hàng <?php echo htmlspecialchars($order->order_code ?: ('DH'.$order->id), ENT_QUOTES, 'UTF-8'); ?></h2>
<div class="row g-3">
<div class="col-lg-8"><div class="card-soft p-3"><h5>Sản phẩm</h5>
<table class="table align-middle"><thead><tr><th>Sản phẩm</th><th>SL</th><th>Giá</th><th>Thành tiền</th></tr></thead><tbody>
<?php foreach ($items as $i): ?><tr><td><?php echo htmlspecialchars($i->product_name ?: $i->name, ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo (int)$i->quantity; ?></td><td><?php echo number_format((float)$i->price,0,',','.'); ?>đ</td><td><?php echo number_format((float)$i->price*(int)$i->quantity,0,',','.'); ?>đ</td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="col-lg-4"><div class="card-soft p-3"><h5>Thông tin đơn</h5>
<p><b>Người nhận:</b> <?php echo htmlspecialchars($order->name, ENT_QUOTES, 'UTF-8'); ?></p>
<p><b>SĐT:</b> <?php echo htmlspecialchars($order->phone, ENT_QUOTES, 'UTF-8'); ?></p>
<p><b>Địa chỉ:</b> <?php echo htmlspecialchars($order->address, ENT_QUOTES, 'UTF-8'); ?></p>
<p><b>Phương thức:</b> <?php echo htmlspecialchars($order->payment_method, ENT_QUOTES, 'UTF-8'); ?></p>
<p><b>Tạm tính:</b> <?php echo number_format((float)$order->total_amount,0,',','.'); ?>đ</p>
<p><b>Giảm:</b> <?php echo number_format((float)$order->discount_amount,0,',','.'); ?>đ</p>
<p><b>Phí ship:</b> <?php echo number_format((float)$order->shipping_fee,0,',','.'); ?>đ</p>
<h5 class="text-danger">Tổng: <?php echo number_format((float)$order->final_amount,0,',','.'); ?>đ</h5>
<a class="btn btn-outline-danger w-100 mt-2" href="<?php echo BASE_URL; ?>/Advanced/returnRequest/<?php echo $order->id; ?>">Yêu cầu đổi trả / hoàn tiền</a>
</div></div>
</div>
<div class="card-soft p-3 mt-3"><h5>Theo dõi trạng thái</h5>
<?php foreach ($logs as $l): ?><div class="border-bottom py-2"><b><?php echo htmlspecialchars($l->status, ENT_QUOTES, 'UTF-8'); ?></b> - <?php echo htmlspecialchars($l->created_at, ENT_QUOTES, 'UTF-8'); ?><br><span class="text-muted"><?php echo htmlspecialchars($l->note, ENT_QUOTES, 'UTF-8'); ?></span></div><?php endforeach; ?>
<?php if (empty($logs)): ?><p class="text-muted">Chưa có lịch sử trạng thái.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
