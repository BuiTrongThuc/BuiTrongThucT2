<?php include 'app/views/layouts/header.php'; ?>
<?php require_once 'app/helpers/SessionHelper.php'; ?>

<?php
function showProductImage($product)
{
    if (!empty($product->image)) {
        return BASE_URL . '/public/uploads/' . htmlspecialchars($product->image, ENT_QUOTES, 'UTF-8');
    }
    return BASE_URL . '/public/uploads/default-phone.png';
}
$dbView = (new Database())->getConnection();
$stmt = $dbView->prepare('SELECT * FROM product_images WHERE product_id=:id ORDER BY sort_order ASC, id ASC');
$stmt->execute(array(':id'=>(int)$product->id));
$productImages = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt = $dbView->prepare('SELECT * FROM product_variants WHERE product_id=:id ORDER BY id ASC');
$stmt->execute(array(':id'=>(int)$product->id));
$variants = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt = $dbView->prepare('SELECT * FROM product_specifications WHERE product_id=:id ORDER BY sort_order ASC, id ASC');
$stmt->execute(array(':id'=>(int)$product->id));
$specs = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt = $dbView->prepare('SELECT r.*, a.username, a.full_name FROM reviews r LEFT JOIN account a ON a.id=r.account_id WHERE r.product_id=:id AND r.is_approved=1 ORDER BY r.id DESC');
$stmt->execute(array(':id'=>(int)$product->id));
$reviews = $stmt->fetchAll(PDO::FETCH_OBJ);
$stmt = $dbView->prepare('SELECT q.*, a.username FROM product_questions q LEFT JOIN account a ON a.id=q.account_id WHERE q.product_id=:id ORDER BY q.id DESC');
$stmt->execute(array(':id'=>(int)$product->id));
$questions = $stmt->fetchAll(PDO::FETCH_OBJ);
$dbView->prepare('UPDATE product SET view_count = view_count + 1 WHERE id=:id')->execute(array(':id'=>(int)$product->id));
?>

<div class="card-soft p-4">
    <div class="row g-4 align-items-start">
        <div class="col-md-5">
            <div class="product-img-wrap rounded-4 bg-light d-flex align-items-center justify-content-center" style="height:360px">
                <img src="<?php echo showProductImage($product); ?>" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:100%;max-height:100%;object-fit:contain;padding:20px" onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/public/uploads/default-phone.png';">
            </div>
            <?php if (!empty($productImages)): ?>
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <?php foreach($productImages as $img): ?><img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($img->image, ENT_QUOTES, 'UTF-8'); ?>" style="width:70px;height:70px;object-fit:contain" class="border rounded-3 p-1"><?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-7">
            <div class="small text-secondary mb-2">Danh mục: <?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></div>
            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="price fs-2 mb-3 text-danger fw-bold"><?php echo number_format((float)$product->price, 0, ',', '.'); ?>đ</div>
            <div class="mb-3"><span class="badge bg-success">Đã bán: <?php echo (int)($product->sold_count ?? 0); ?></span> <span class="badge bg-secondary">Tồn kho: <?php echo (int)($product->stock_quantity ?? 0); ?></span></div>

            <?php if (!empty($variants)): ?>
            <div class="mb-3"><h5 class="fw-bold">Màu / dung lượng / phiên bản</h5><div class="d-flex flex-wrap gap-2">
                <?php foreach($variants as $v): ?><span class="badge text-bg-light border p-2"><?php echo htmlspecialchars(trim(($v->color ?: '').' '.($v->storage ?: '').' '.($v->version ?: '')), ENT_QUOTES, 'UTF-8'); ?><?php if($v->price): ?> - <?php echo number_format((float)$v->price,0,',','.'); ?>đ<?php endif; ?></span><?php endforeach; ?>
            </div></div>
            <?php endif; ?>

            <div class="mb-4"><h5 class="fw-bold">Mô tả sản phẩm</h5><p class="text-secondary fs-6"><?php echo nl2br(htmlspecialchars($product->description ?: 'Sản phẩm chưa có mô tả chi tiết.', ENT_QUOTES, 'UTF-8')); ?></p></div>

            <div class="row g-2 mb-4">
                <div class="col-sm-4"><div class="border rounded-4 p-3 h-100"><strong><i class="bi bi-shield-check text-danger"></i> Chính hãng</strong><div class="small text-secondary">Bảo hành theo IMEI.</div></div></div>
                <div class="col-sm-4"><div class="border rounded-4 p-3 h-100"><strong><i class="bi bi-truck text-danger"></i> Giao nhanh</strong><div class="small text-secondary">Theo dõi trạng thái đơn.</div></div></div>
                <div class="col-sm-4"><div class="border rounded-4 p-3 h-100"><strong><i class="bi bi-arrow-repeat text-danger"></i> Đổi trả</strong><div class="small text-secondary">Gửi yêu cầu đổi trả.</div></div></div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-danger rounded-pill px-4 fw-bold" href="<?php echo BASE_URL; ?>/Cart/add/<?php echo (int)$product->id; ?>"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</a>
                <a class="btn btn-outline-danger rounded-pill px-4" href="<?php echo BASE_URL; ?>/Advanced/addWishlist/<?php echo (int)$product->id; ?>"><i class="bi bi-heart"></i> Yêu thích</a>
                <a class="btn btn-outline-primary rounded-pill px-4" href="<?php echo BASE_URL; ?>/Advanced/addCompare/<?php echo (int)$product->id; ?>"><i class="bi bi-arrow-left-right"></i> So sánh</a>
                <a class="btn btn-outline-dark rounded-pill px-4" href="<?php echo BASE_URL; ?>/Product/list">Quay lại</a>
                <?php if (SessionHelper::isAdmin()): ?><a class="btn btn-warning rounded-pill px-4" href="<?php echo BASE_URL; ?>/Product/edit/<?php echo (int)$product->id; ?>">Sửa sản phẩm</a><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-6"><div class="card-soft p-4"><h4>Thông số kỹ thuật</h4><?php if($specs): ?><table class="table"><?php foreach($specs as $sp): ?><tr><th><?php echo htmlspecialchars($sp->spec_name, ENT_QUOTES, 'UTF-8'); ?></th><td><?php echo htmlspecialchars($sp->spec_value, ENT_QUOTES, 'UTF-8'); ?></td></tr><?php endforeach; ?></table><?php else: ?><p class="text-muted">Chưa nhập thông số chi tiết.</p><?php endif; ?></div></div>
    <div class="col-lg-6"><div class="card-soft p-4"><h4>Hỏi đáp sản phẩm</h4><form method="post" action="<?php echo BASE_URL; ?>/Advanced/question/<?php echo (int)$product->id; ?>" class="mb-3"><textarea class="form-control mb-2" name="question" rows="2" placeholder="Bạn cần hỏi gì về sản phẩm?"></textarea><button class="btn btn-primary btn-sm">Gửi câu hỏi</button></form><?php foreach($questions as $q): ?><div class="border-bottom py-2"><b><?php echo htmlspecialchars($q->username ?: 'Khách', ENT_QUOTES, 'UTF-8'); ?>:</b> <?php echo htmlspecialchars($q->question, ENT_QUOTES, 'UTF-8'); ?></div><?php endforeach; ?></div></div>
</div>

<div class="card-soft p-4 mt-3"><h4>Đánh giá sản phẩm</h4>
<?php if(SessionHelper::isLoggedIn()): ?><form method="post" action="<?php echo BASE_URL; ?>/Advanced/review/<?php echo (int)$product->id; ?>" class="row g-2 mb-3"><div class="col-md-2"><select class="form-select" name="rating"><option value="5">5 sao</option><option value="4">4 sao</option><option value="3">3 sao</option><option value="2">2 sao</option><option value="1">1 sao</option></select></div><div class="col-md-8"><input class="form-control" name="comment" placeholder="Nhận xét của bạn"></div><div class="col-md-2"><button class="btn btn-danger w-100">Gửi</button></div></form><?php else: ?><p><a href="<?php echo BASE_URL; ?>/Auth/login">Đăng nhập</a> để đánh giá.</p><?php endif; ?>
<?php foreach($reviews as $r): ?><div class="border-bottom py-2"><b><?php echo str_repeat('★', (int)$r->rating); ?></b> - <?php echo htmlspecialchars($r->full_name ?: $r->username ?: 'Người dùng', ENT_QUOTES, 'UTF-8'); ?><p><?php echo htmlspecialchars($r->comment, ENT_QUOTES, 'UTF-8'); ?></p></div><?php endforeach; ?><?php if(empty($reviews)): ?><p class="text-muted">Chưa có đánh giá.</p><?php endif; ?></div>

<?php include 'app/views/layouts/footer.php'; ?>
