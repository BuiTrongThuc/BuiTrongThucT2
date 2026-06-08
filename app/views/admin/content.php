<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">CMS nội dung</h2><div class="row g-3"><div class="col-lg-5"><div class="card-soft p-3"><h5>Thêm FAQ</h5><form method="post" action="<?php echo BASE_URL; ?>/Admin/addFaq"><input class="form-control mb-2" name="question" placeholder="Câu hỏi" required><textarea class="form-control mb-2" name="answer" rows="4" placeholder="Trả lời" required></textarea><input class="form-control mb-2" name="sort_order" placeholder="Thứ tự" value="0"><button class="btn btn-primary">Thêm</button></form></div></div><div class="col-lg-7"><div class="card-soft p-3"><h5>FAQ hiện có</h5><?php foreach($faqs as $f): ?><div class="border-bottom py-2"><b><?php echo htmlspecialchars($f->question, ENT_QUOTES, 'UTF-8'); ?></b><p><?php echo htmlspecialchars($f->answer, ENT_QUOTES, 'UTF-8'); ?></p></div><?php endforeach; ?></div></div></div>

<?php include 'app/views/layouts/footer.php'; ?>
