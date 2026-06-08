<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Thu cũ đổi mới</h2><div class="card-soft p-3"><form method="post"><input class="form-control mb-2" name="old_device_name" placeholder="Tên máy cũ" required><input class="form-control mb-2" name="imei" placeholder="IMEI/Serial"><textarea class="form-control mb-2" name="condition_note" rows="4" placeholder="Tình trạng máy"></textarea><button class="btn btn-primary">Gửi yêu cầu định giá</button></form></div>

<?php include 'app/views/layouts/footer.php'; ?>
