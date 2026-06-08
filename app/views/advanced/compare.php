<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">So sánh sản phẩm</h2>
<div class="card-soft p-3"><table class="table table-bordered"><tbody>
<tr><th>Tiêu chí</th><?php foreach ($products as $p): ?><th><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></th><?php endforeach; ?></tr>
<tr><td>Ảnh</td><?php foreach ($products as $p): ?><td><img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($p->image ?: 'default-phone.png', ENT_QUOTES, 'UTF-8'); ?>" style="height:120px;object-fit:contain"></td><?php endforeach; ?></tr>
<tr><td>Giá</td><?php foreach ($products as $p): ?><td class="text-danger fw-bold"><?php echo number_format((float)$p->price,0,',','.'); ?>đ</td><?php endforeach; ?></tr>
<tr><td>Tồn kho</td><?php foreach ($products as $p): ?><td><?php echo (int)$p->stock_quantity; ?></td><?php endforeach; ?></tr>
<tr><td>Mô tả</td><?php foreach ($products as $p): ?><td><?php echo htmlspecialchars($p->description, ENT_QUOTES, 'UTF-8'); ?></td><?php endforeach; ?></tr>
</tbody></table><?php if(empty($products)): ?><p>Hãy bấm “So sánh” ở trang sản phẩm.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
