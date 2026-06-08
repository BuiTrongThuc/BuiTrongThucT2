<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Tra cứu bảo hành IMEI/Serial</h2><div class="card-soft p-3"><form method="get" class="d-flex gap-2"><input class="form-control" name="imei" placeholder="Nhập IMEI/Serial" value="<?php echo htmlspecialchars($imei, ENT_QUOTES, 'UTF-8'); ?>"><button class="btn btn-primary">Tra cứu</button></form>
<?php if($imei !== ''): ?><hr><?php if($warranty): ?><h5><?php echo htmlspecialchars($warranty->product_name, ENT_QUOTES, 'UTF-8'); ?></h5><p>IMEI: <b><?php echo htmlspecialchars($warranty->imei, ENT_QUOTES, 'UTF-8'); ?></b></p><p>Bảo hành: <?php echo htmlspecialchars($warranty->start_date . ' - ' . $warranty->end_date, ENT_QUOTES, 'UTF-8'); ?></p><p>Trạng thái: <?php echo htmlspecialchars($warranty->status, ENT_QUOTES, 'UTF-8'); ?></p><?php else: ?><p class="text-danger">Không tìm thấy dữ liệu bảo hành cho IMEI này.</p><?php endif; ?><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
