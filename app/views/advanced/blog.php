<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Blog / Tin công nghệ</h2><div class="row g-3"><?php foreach($posts as $p): ?><div class="col-md-4"><div class="card-soft p-3"><h5><?php echo htmlspecialchars($p->title, ENT_QUOTES, 'UTF-8'); ?></h5><p><?php echo htmlspecialchars(mb_substr(strip_tags($p->content),0,150), ENT_QUOTES, 'UTF-8'); ?>...</p></div></div><?php endforeach; ?><?php if(empty($posts)): ?><p class="text-muted">Chưa có bài viết.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
