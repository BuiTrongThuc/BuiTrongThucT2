<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Thông báo</h2><div class="card-soft p-3">
<?php foreach ($notifications as $n): ?><div class="border-bottom py-2"><b><?php echo htmlspecialchars($n->title, ENT_QUOTES, 'UTF-8'); ?></b><p class="mb-1"><?php echo htmlspecialchars($n->message, ENT_QUOTES, 'UTF-8'); ?></p><small class="text-muted"><?php echo htmlspecialchars($n->created_at, ENT_QUOTES, 'UTF-8'); ?></small></div><?php endforeach; ?>
<?php if(empty($notifications)): ?><p class="text-muted">Chưa có thông báo.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
