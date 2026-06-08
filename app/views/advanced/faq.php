<?php include 'app/views/layouts/header.php'; ?>

<h2 class="fw-bold mb-3">Câu hỏi thường gặp</h2><div class="accordion" id="faqAcc"><?php foreach($faqs as $f): ?><div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f<?php echo $f->id; ?>"><?php echo htmlspecialchars($f->question, ENT_QUOTES, 'UTF-8'); ?></button></h2><div id="f<?php echo $f->id; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAcc"><div class="accordion-body"><?php echo nl2br(htmlspecialchars($f->answer, ENT_QUOTES, 'UTF-8')); ?></div></div></div><?php endforeach; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
