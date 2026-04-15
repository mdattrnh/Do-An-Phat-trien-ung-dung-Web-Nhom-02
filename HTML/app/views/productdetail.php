<?php
/**
 * Biến cần truyền vào view này từ controller:
 *  - $product         : array thông tin sản phẩm hiện tại
 *  - $reviews         : array đánh giá
 *  - $avgRating       : float điểm trung bình
 *  - $suggestedProducts : array gợi ý (tối đa 4 sản phẩm), ví dụ lấy theo cùng category:
 *      $suggestedProducts = Product::getByCategoryExcluding($product['category'], $product['id'], 4);
 *    Mỗi phần tử cần có: id, name, price, image, category, tag (optional)
 */
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?> — SOFT EDGE</title>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&family=Inter:wght@300;400;500;700&family=Bebas+Neue&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= ASSET_URL ?>/style.css?v=<?= time() ?>" />
  <style>
    /* ── PAGE-LEVEL OVERRIDES ── */
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--mid);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin-bottom: 2.5rem;
      transition: color 0.2s;
    }
    .back-link:hover { color: var(--charcoal); }
    .back-link svg { transition: transform 0.2s; }
    .back-link:hover svg { transform: translateX(-3px); }

    /* Sticky image panel - chỉ sticky trên desktop */
    @media (min-width: 769px) {
      .detail-image-card { position: sticky; top: 8rem; }
    }

    /* Mobile: ảnh nằm yên, hiển thị đủ */
    @media (max-width: 768px) {
      .detail-image-card { position: static; }
      .detail-image img { height: auto !important; max-height: 480px; object-fit: contain; }
    }

    /* ── LIGHTBOX ── */
    .lightbox-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.92);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      cursor: zoom-out;
      animation: fadeIn 0.2s ease;
    }
    .lightbox-overlay.open { display: flex; }
    @keyframes fadeIn { from { opacity:0 } to { opacity:1 } }
    .lightbox-overlay img {
      max-width: 92vw;
      max-height: 88vh;
      object-fit: contain;
      border-radius: 1rem;
      box-shadow: 0 32px 80px rgba(0,0,0,0.5);
      animation: zoomIn 0.25s ease;
    }
    @keyframes zoomIn { from { transform: scale(0.88); opacity:0 } to { transform: scale(1); opacity:1 } }
    .lightbox-close {
      position: fixed;
      top: 1.25rem;
      right: 1.25rem;
      width: 40px; height: 40px;
      background: rgba(255,255,255,0.15);
      border: none;
      border-radius: 50%;
      color: #fff;
      font-size: 1.2rem;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.2s;
    }
    .lightbox-close:hover { background: rgba(255,255,255,0.3); }
    .detail-image { cursor: zoom-in; }

    /* Main image — taller & rounded */
    .detail-image {
      border-radius: 1.5rem;
      overflow: hidden;
      background: var(--white);
      box-shadow: 0 24px 64px rgba(0,0,0,0.12);
      margin-bottom: 1rem;
    }
    .detail-image img {
      width: 100%;
      height: 560px;
      object-fit: cover;
      display: block;
      transition: transform 0.6s ease;
    }
    .detail-image:hover img { transform: scale(1.02); }

    /* Thumbnail strip */
    .thumb-strip {
      display: flex;
      gap: 0.6rem;
      flex-wrap: nowrap;
      overflow-x: auto;
      padding-bottom: 4px;
      scrollbar-width: none;
    }
    .thumb-strip::-webkit-scrollbar { display: none; }
    .thumb-btn {
      flex-shrink: 0;
      width: 72px;
      height: 88px;
      border: 2px solid transparent;
      border-radius: 10px;
      overflow: hidden;
      cursor: pointer;
      padding: 0;
      background: none;
      transition: border-color 0.2s, transform 0.2s;
    }
    .thumb-btn:hover { transform: translateY(-2px); border-color: rgba(0,0,0,0.2); }
    .thumb-btn.active { border-color: var(--charcoal); }
    .thumb-btn img { width: 100%; height: 100%; object-fit: cover; }

    /* Info panel */
    .detail-info { padding-top: 0.5rem; }

    /* Product name */
    .detail-info h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2.8rem, 6vw, 4.5rem);
      line-height: 0.95;
      letter-spacing: -0.01em;
      margin-bottom: 1rem;
      color: var(--charcoal);
    }

    /* Meta row */
    .detail-meta {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.5rem 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.85rem;
      color: var(--mid);
    }
    .detail-meta .dot { opacity: 0.4; }
    .tag-pill {
      background: #f0f0f0;
      color: var(--charcoal);
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding: 0.25rem 0.7rem;
      border-radius: 100px;
    }

    /* Price */
    .detail-price {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2.2rem, 5vw, 3.2rem);
      color: var(--charcoal);
      margin-bottom: 1.2rem;
      letter-spacing: -0.01em;
    }

    /* Divider */
    .info-divider {
      height: 1px;
      background: rgba(0,0,0,0.08);
      margin: 1.5rem 0;
    }

    /* Description */
    .detail-desc {
      font-size: 1rem;
      line-height: 1.75;
      color: var(--mid);
      margin-bottom: 0;
    }

    /* Size section */
    .detail-sizes { margin-bottom: 1.8rem; }
    .size-label {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--charcoal);
      margin-bottom: 0.75rem;
    }
    .size-guide { font-size: 0.8rem; color: var(--mid); font-weight: 400; text-decoration: underline; cursor: pointer; }

    .detail-size-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 0.6rem;
    }
    .detail-size-pill {
      padding: 0.65rem 1.2rem;
      border: 2px solid rgba(0,0,0,0.12);
      background: transparent;
      border-radius: 0.75rem;
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--mid);
      cursor: pointer;
      transition: all 0.2s ease;
      min-width: 56px;
      text-align: center;
    }
    .detail-size-pill:hover {
      border-color: var(--charcoal);
      color: var(--charcoal);
      transform: translateY(-1px);
    }
    .detail-size-pill.active {
      background: var(--charcoal);
      color: var(--cream);
      border-color: var(--charcoal);
    }

    /* Action buttons */
    .detail-actions {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 2rem;
    }
    .btn-add-cart {
      flex: 1;
      background: var(--charcoal);
      color: var(--cream);
      border: none;
      padding: 1rem 1.5rem;
      border-radius: 0.875rem;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.25s;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .btn-add-cart:hover {
      background: #333;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .btn-continue {
      padding: 1rem 1.4rem;
      border-radius: 0.875rem;
      border: 2px solid rgba(0,0,0,0.15);
      background: transparent;
      color: var(--charcoal);
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      cursor: pointer;
      transition: all 0.25s;
      white-space: nowrap;
    }
    .btn-continue:hover { border-color: var(--charcoal); background: #f5f5f5; }

    /* Rating summary box */
    .rating-box {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.25rem;
      background: #f8f8f8;
      border-radius: 0.875rem;
      margin-bottom: 2rem;
    }
    .rating-score {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.2rem;
      line-height: 1;
      color: var(--charcoal);
    }
    .rating-stars-row { color: #ffc107; font-size: 1rem; margin-bottom: 0.2rem; }
    .rating-count { font-size: 0.82rem; color: var(--mid); }

    /* Shipping info strip */
    .shipping-strip {
      display: flex;
      gap: 0.75rem;
      padding: 1.25rem 0;
      margin-bottom: 0;
    }
    .shipping-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      flex: 1;
      background: #f8f8f8;
      border-radius: 1rem;
      padding: 0.9rem 0.5rem;
      text-align: center;
      border: 1.5px solid transparent;
      transition: border-color 0.2s, background 0.2s;
    }
    .shipping-item:hover {
      border-color: rgba(0,0,0,0.12);
      background: #f0f0f0;
    }
    .shipping-item .icon {
      font-size: 1.4rem;
      line-height: 1;
    }
    .shipping-item-title {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--charcoal);
      letter-spacing: 0.02em;
    }
    .shipping-item-sub {
      font-size: 0.7rem;
      color: var(--mid);
      line-height: 1.3;
    }

    /* ── REVIEWS ── */
    .reviews-section {
      max-width: 1200px;
      margin: 4rem auto 0;
      padding: 3rem 2rem;
      border-top: 1px solid rgba(0,0,0,0.08);
    }
    .reviews-inner { display: grid; grid-template-columns: 340px 1fr; gap: 4rem; align-items: start; }
    .reviews-sidebar { position: sticky; top: 8rem; }
    .reviews-title-lg {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      letter-spacing: -0.01em;
      margin-bottom: 1.5rem;
    }
    .avg-score-big {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 5rem;
      line-height: 1;
      color: var(--charcoal);
    }
    .avg-stars { color: #ffc107; font-size: 1.5rem; margin-bottom: 0.5rem; }
    .avg-label { font-size: 0.875rem; color: var(--mid); margin-bottom: 2rem; }

    /* Review form */
    .review-form-box {
      background: #f9f9f9;
      border-radius: 1.25rem;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .review-form-box h3 { font-size: 1rem; font-weight: 700; margin-bottom: 1.2rem; }
    .star-picker { display: flex; gap: 0.4rem; font-size: 1.8rem; margin-bottom: 1rem; }
    .star-picker span { cursor: pointer; color: #ddd; transition: color 0.15s; }
    .star-picker span:hover,
    .star-picker span.lit { color: #ffc107; }
    .review-textarea {
      width: 100%;
      min-height: 110px;
      padding: 0.875rem;
      border: 1.5px solid rgba(0,0,0,0.1);
      border-radius: 0.75rem;
      font-family: inherit;
      font-size: 0.95rem;
      resize: vertical;
      margin-bottom: 1rem;
      box-sizing: border-box;
      transition: border-color 0.2s;
    }
    .review-textarea:focus { outline: none; border-color: var(--charcoal); }
    .btn-submit-review {
      width: 100%;
      background: var(--charcoal);
      color: var(--cream);
      border: none;
      padding: 0.875rem;
      border-radius: 0.75rem;
      font-weight: 700;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.2s;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .btn-submit-review:hover { background: #333; transform: translateY(-1px); }

    /* Review cards */
    .reviews-list { display: flex; flex-direction: column; gap: 1.25rem; }
    .review-card {
      background: #fff;
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 1.25rem;
      padding: 1.5rem;
    }
    .review-header { display: flex; align-items: center; gap: 0.875rem; margin-bottom: 0.875rem; }
    .review-avatar {
      width: 44px; height: 44px;
      border-radius: 50%;
      background: var(--charcoal);
      color: var(--cream);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 1.1rem;
      flex-shrink: 0;
    }
    .review-author { font-weight: 600; font-size: 0.95rem; color: var(--charcoal); margin-bottom: 0.2rem; }
    .review-stars { color: #ffc107; font-size: 0.85rem; }
    .review-date { font-size: 0.8rem; color: var(--mid); margin-top: 0.15rem; }
    .review-content { font-size: 0.95rem; line-height: 1.65; color: #555; }

    .empty-state {
      text-align: center; padding: 3rem; color: #bbb;
      border: 2px dashed rgba(0,0,0,0.08); border-radius: 1.25rem;
      font-size: 0.95rem;
    }
    .login-prompt {
      text-align: center; padding: 1.5rem;
      background: #f9f9f9; border-radius: 1rem;
      font-size: 0.9rem; color: var(--mid);
      margin-bottom: 2rem;
    }
    .login-prompt button {
      margin-top: 0.75rem;
      background: var(--charcoal); color: var(--cream);
      border: none; padding: 0.75rem 1.5rem;
      border-radius: 0.75rem; font-weight: 600;
      font-size: 0.85rem; cursor: pointer;
      letter-spacing: 0.05em; text-transform: uppercase;
    }

    /* ── SUGGESTED PRODUCTS ── */
    .suggested-section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 4rem 2rem 5rem;
      border-top: 1px solid rgba(0,0,0,0.08);
    }
    .suggested-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 2.5rem;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .suggested-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      letter-spacing: -0.01em;
      color: var(--charcoal);
      line-height: 1;
    }
    .suggested-title span {
  color: var(--mid);
  font-size: 0.55em; 
  font-family: 'Urbanist', sans-serif;
  font-weight: 700; 
  letter-spacing: 0.12em; 
  text-transform: uppercase;
  display: block;
  margin-bottom: 0.35rem;
}
    .suggested-view-all {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--charcoal);
      text-decoration: none;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      border-bottom: 1.5px solid var(--charcoal);
      padding-bottom: 2px;
      white-space: nowrap;
      transition: opacity 0.2s;
    }
    .suggested-view-all:hover { opacity: 0.6; }
    .suggested-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
    }
    .sugg-card {
      background: var(--white);
      border-radius: 1.25rem;
      overflow: hidden;
      text-decoration: none;
      color: inherit;
      border: 1px solid rgba(0,0,0,0.07);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    .sugg-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 48px rgba(0,0,0,0.12);
    }
    .sugg-img-wrap {
      position: relative;
      overflow: hidden;
      aspect-ratio: 3/4;
      background: #f5f5f5;
    }
    .sugg-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    .sugg-card:hover .sugg-img-wrap img { transform: scale(1.06); }
    .sugg-badge {
      position: absolute;
      top: 0.75rem;
      left: 0.75rem;
      background: var(--charcoal);
      color: var(--cream);
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 0.25rem 0.6rem;
      border-radius: 100px;
    }
    .sugg-card-body {
      padding: 1rem 1.1rem 1.2rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }
    .sugg-cat {
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--mid);
    }
    .sugg-name {
      font-weight: 700;
      font-size: 0.95rem;
      color: var(--charcoal);
      line-height: 1.3;
    }
    .sugg-price {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem;
      color: var(--charcoal);
      margin-top: auto;
      padding-top: 0.5rem;
    }
    .sugg-card-btn {
      margin: 0 1.1rem 1.1rem;
      background: var(--charcoal);
      color: var(--cream);
      border: none;
      padding: 0.7rem;
      border-radius: 0.75rem;
      font-weight: 700;
      font-size: 0.8rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      cursor: pointer;
      width: calc(100% - 2.2rem);
      transition: background 0.2s, transform 0.2s;
      text-align: center;
      text-decoration: none;
      display: block;
    }
    .sugg-card-btn:hover { background: #333; transform: translateY(-1px); }

    @media (max-width: 900px) {
      .reviews-inner { grid-template-columns: 1fr; gap: 2rem; }
      .reviews-sidebar { position: static; }
      .suggested-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .detail-image img { height: 380px; }
      .detail-actions { flex-direction: column; }
      .shipping-strip { flex-wrap: wrap; }
      .reviews-section { padding: 2rem 1rem; }
      .suggested-section { padding: 3rem 1rem 4rem; }
    }
    @media (max-width: 480px) {
      .suggested-grid { gap: 0.75rem; }
    }
  </style>
  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
    window.isLoggedIn = <?= (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) ? 'true' : 'false' ?>;
    window.userId = '<?= $_SESSION['user_id'] ?? '' ?>';
  </script>
</head>
<body>
  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- ── NAV ── -->
  <nav id="nav" class="nav">
    <a href="<?= BASE_URL ?>" class="nav-logo">SOFT<span class="logo-edge">EDGE</span></a>
    <div class="nav-links desktop-only">
      <a href="<?= BASE_URL ?>">Trang chủ</a>
      <a href="<?= BASE_URL ?>/#collection">Collection</a>
      <a href="<?= BASE_URL ?>/#about">About</a>
    </div>
    <div class="nav-cart">
      <?php
        $isLoggedIn = false;
        $userName   = '';
        if (isset($_SESSION['user_name'])) {
            $isLoggedIn = true;
            $userName   = $_SESSION['user_name'];
        } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $isLoggedIn = true;
            $userName   = 'Admin';
        }
      ?>
      <?php if ($isLoggedIn): ?>
        <a href="<?= BASE_URL ?>/logout" class="login-btn desktop-only"
           style="border:1px solid #ddd;padding:6px 12px;border-radius:4px;">
          👤 <?= htmlspecialchars($userName) ?> (Thoát)
        </a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/register" class="login-btn desktop-only">Đăng nhập</a>
      <?php endif; ?>
      <button type="button" class="cart-btn" id="cartBtn">
        Giỏ hàng
        <span class="cart-count" id="cartCount">0</span>
      </button>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <div class="mobile-menu-overlay" id="mobileMenu">
    <div class="mobile-menu-content">
      <a href="<?= BASE_URL ?>/#collection" class="mobile-link">Collection</a>
      <a href="<?= BASE_URL ?>/#about" class="mobile-link">About</a>
      <div class="mobile-divider"></div>
      <?php if ($isLoggedIn): ?>
        <a href="<?= BASE_URL ?>/logout" class="mobile-link">👤 <?= htmlspecialchars($userName) ?> (Thoát)</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/register" class="mobile-link">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── MAIN PRODUCT SECTION ── -->
  <main class="product-detail-hero">

    <a href="<?= BASE_URL ?>" class="back-link">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M19 12H5M12 5l-7 7 7 7"/>
      </svg>
      Quay lại cửa hàng
    </a>

    <section class="detail-hero-grid">

      <!-- LEFT: IMAGE PANEL -->
      <div class="detail-image-card">
        <div class="detail-image" onclick="openLightbox(document.getElementById('mainImage').src)">
          <img id="mainImage"
               src="<?= htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/600x800') ?>"
               alt="<?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?>"
               loading="eager">

        </div>

        <?php if (!empty($product['images']) && count($product['images']) > 1): ?>
          <div class="thumb-strip">
            <?php foreach ($product['images'] as $i => $thumb): ?>
              <button type="button"
                      class="thumb-btn <?= $i === 0 ? 'active' : '' ?>"
                      onclick="switchImage(this, '<?= htmlspecialchars($thumb) ?>')">
                <img src="<?= htmlspecialchars($thumb) ?>"
                     alt="<?= htmlspecialchars($product['name'] ?? '') ?> - ảnh <?= $i + 1 ?>">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: INFO PANEL -->
      <div class="detail-info">

        <!-- Category + Brand tags -->
        <div class="detail-meta">
          <span class="tag-pill"><?= htmlspecialchars(ucfirst($product['category'] ?? 'others')) ?></span>
        </div>

        <!-- Name -->
        <h1><?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?></h1>

        <!-- Rating summary -->
        <div class="rating-box">
          <div>
            <div class="rating-score"><?= number_format($avgRating, 1) ?></div>
          </div>
          <div>
            <div class="rating-stars-row">
              <?php
                $full  = floor($avgRating);
                $half  = ($avgRating - $full) >= 0.5 ? 1 : 0;
                $empty = 5 - $full - $half;
                echo str_repeat('★', $full) . ($half ? '½' : '') . str_repeat('☆', $empty);
              ?>
            </div>
            <div class="rating-count"><?= count($reviews) ?> đánh giá từ khách hàng</div>
          </div>
        </div>

        <!-- Price -->
        <div class="detail-price">
          <?= htmlspecialchars(number_format($product['price'] ?? 0, 0, ',', '.')) ?> ₫
        </div>

        <!-- Description -->
        <p class="detail-desc">
          <?= nl2br(htmlspecialchars($product['desc'] ?? 'Sản phẩm chất lượng cao với thiết kế streetwear cá tính.')) ?>
        </p>

        <div class="info-divider"></div>

        <!-- SIZE SELECTION -->
        <div class="detail-sizes">
          <div class="size-label">
            <span>Kích cỡ</span>
            <span class="size-guide">Hướng dẫn chọn size →</span>
          </div>
          <div class="detail-size-grid" id="sizeGrid">
            <?php if (!empty($product['sizes'])): ?>
              <?php foreach ($product['sizes'] as $size): ?>
                <button type="button"
                        class="detail-size-pill"
                        onclick="selectProductSize(this, '<?= htmlspecialchars($size) ?>')">
                  <?= htmlspecialchars($size) ?>
                </button>
              <?php endforeach; ?>
            <?php else: ?>
              <button type="button"
                      class="detail-size-pill active"
                      onclick="selectProductSize(this, 'One Size')">
                One Size
              </button>
            <?php endif; ?>
          </div>
          <input type="hidden" id="selectedSize" value="<?= htmlspecialchars($product['sizes'][0] ?? 'One Size') ?>">
        </div>

        <!-- ACTION BUTTONS -->
        <div class="detail-actions">
          <button type="button" class="btn-add-cart" onclick="addDetailToCart()">
            🛒 Thêm vào giỏ hàng
          </button>
          <a href="<?= BASE_URL ?>/#collection" class="btn-continue">
            Tiếp tục mua sắm
          </a>
        </div>

        <!-- SHIPPING STRIP -->
        <div class="shipping-strip">
          <div class="shipping-item">
            <span class="icon">🚚</span>
            <span class="shipping-item-title">Miễn phí ship</span>
            <span class="shipping-item-sub">Đơn hàng từ 500k</span>
          </div>
          <div class="shipping-item">
            <span class="icon">↩️</span>
            <span class="shipping-item-title">Đổi trả 30 ngày</span>
            <span class="shipping-item-sub">Miễn phí, không hỏi</span>
          </div>
          <div class="shipping-item">
            <span class="icon">🔒</span>
            <span class="shipping-item-title">Thanh toán an toàn</span>
            <span class="shipping-item-sub">Mã hoá 256-bit</span>
          </div>
        </div>

      </div><!-- /detail-info -->
    </section>

    <!-- ── REVIEWS SECTION ── -->
    <section class="reviews-section">
      <div class="reviews-inner">

        <!-- SIDEBAR: rating overview + form -->
        <div class="reviews-sidebar">
          <h2 class="reviews-title-lg">Đánh giá</h2>
          <div class="avg-score-big"><?= number_format($avgRating, 1) ?></div>
          <div class="avg-stars">
            <?= str_repeat('★', (int)round($avgRating)) . str_repeat('☆', 5 - (int)round($avgRating)) ?>
          </div>
          <div class="avg-label"><?= count($reviews) ?> lượt đánh giá</div>

          <?php if ($isLoggedIn): ?>
            <div class="review-form-box">
              <h3>Chia sẻ đánh giá của bạn</h3>
              <form id="reviewForm" onsubmit="submitReview(event)">
                <div class="star-picker" id="ratingStars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="rating-star"
                          data-rating="<?= $i ?>"
                          onclick="setRating(<?= $i ?>)">★</span>
                  <?php endfor; ?>
                </div>
                <input type="hidden" id="ratingValue" value="0">
                <textarea id="commentValue"
                          class="review-textarea"
                          placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                <button type="submit" class="btn-submit-review">Gửi đánh giá</button>
              </form>
            </div>
          <?php else: ?>
            <div class="login-prompt">
              <p>Đăng nhập để chia sẻ đánh giá của bạn</p>
              <a href="<?= BASE_URL ?>/register">
  <button type="button">Đăng nhập</button>
</a>
            </div>
          <?php endif; ?>
        </div>

        <!-- REVIEW LIST -->
        <div>
          <?php if (count($reviews) === 0): ?>
            <div class="empty-state">
              Chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ! 🌟
            </div>
          <?php else: ?>
            <div class="reviews-list">
              <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                  <div class="review-header">
                    <div class="review-avatar">
                      <?= strtoupper(substr($review['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                      <div class="review-author">
                        <?= htmlspecialchars($review['full_name'] ?? 'Khách hàng ẩn danh') ?>
                      </div>
                      <div class="review-stars">
                        <?= str_repeat('★', (int)($review['rating'] ?? 0))
                          . str_repeat('☆', 5 - (int)($review['rating'] ?? 0)) ?>
                      </div>
                      <div class="review-date">
                        <?= date('d/m/Y', strtotime($review['created_at'] ?? 'now')) ?>
                      </div>
                    </div>
                  </div>
                  <?php if (!empty($review['comment'])): ?>
                    <div class="review-content">
                      <?= nl2br(htmlspecialchars($review['comment'])) ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </section>

    <section class="suggested-section">
      <div class="suggested-header">
        <div class="suggested-title">
          <span>Có thể bạn sẽ thích</span>
        </div>
        <a href="<?= BASE_URL ?>/#collection" class="suggested-view-all">Xem tất cả →</a>
      </div>
      <div class="suggested-grid">
        <?php foreach ($suggestedProducts as $sp): ?>
          <a href="<?= BASE_URL ?>/productdetail/<?= htmlspecialchars($sp['id']) ?>" class="sugg-card">
            <div class="sugg-img-wrap">
              <img src="<?= htmlspecialchars($sp['image'] ?? 'https://via.placeholder.com/300x400') ?>"
                   alt="<?= htmlspecialchars($sp['name'] ?? 'Sản phẩm') ?>"
                   loading="lazy">

            </div>
            <div class="sugg-card-body">
              <span class="sugg-cat"><?= htmlspecialchars(ucfirst($sp['category'] ?? 'others')) ?></span>
              <span class="sugg-name"><?= htmlspecialchars($sp['name'] ?? 'Sản phẩm') ?></span>
              <span class="sugg-price"><?= number_format($sp['price'] ?? 0, 0, ',', '.') ?> ₫</span>
            </div>
            <span class="sugg-card-btn">Xem sản phẩm</span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

  </main>

  <!-- ── CART SIDEBAR ── -->
  <div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
      <h3>Giỏ hàng của bạn</h3>
      <button class="cart-close" id="cartClose">✕</button>
    </div>
    <div class="cart-items" id="cartItems">
      <div class="cart-empty">Chưa có sản phẩm nào</div>
    </div>
    <div class="cart-footer">
      <div class="cart-total">
        <span>Tổng cộng:</span>
        <span id="cartTotal">0 ₫</span>
      </div>
      <button class="btn-primary checkout-btn" id="checkoutBtn">Thanh toán</button>
    </div>
  </div>
  <div class="cart-overlay" id="cartOverlay"></div>
  <div class="toast" id="toast">Đã thêm vào giỏ hàng ✓</div>

  <!-- ── LIGHTBOX ── -->
  <div class="lightbox-overlay" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <img id="lightboxImg" src="" alt="Xem ảnh lớn">
  </div>

  <script src="<?= ASSET_URL ?>/script.js?v=<?= time() ?>" defer></script>
  <script>
    const productId = '<?= $product['id'] ?>';
    window.detailSelectedSize = '<?= $product['sizes'][0] ?? 'One Size' ?>';

    /* ── Lightbox ── */
    function openLightbox(src) {
      document.getElementById('lightboxImg').src = src;
      document.getElementById('lightbox').classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
      document.getElementById('lightbox').classList.remove('open');
      document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    /* ── Switch thumbnail ── */
    function switchImage(btn, src) {
      document.getElementById('mainImage').src = src;
      document.querySelectorAll('.thumb-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    /* ── Size selection ── */
    function selectProductSize(button, size) {
      document.querySelectorAll('.detail-size-pill').forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');
      window.detailSelectedSize = size;
      document.getElementById('selectedSize').value = size;
    }

    /* ── Add to cart ── */
    function addDetailToCart() {
      if (!window.detailSelectedSize) {
        alert('Vui lòng chọn kích cỡ');
        return;
      }
      if (typeof window.addToCart !== 'function') {
        alert('Lỗi: Chức năng giỏ hàng chưa load. Vui lòng refresh trang.');
        return;
      }
      const product = {
        id:    '<?= $product['id'] ?>',
        name:  '<?= addslashes($product['name'] ?? 'Sản phẩm') ?>',
        price: <?= (int)$product['price'] ?>,
        image: '<?= htmlspecialchars($product['image'] ?? '') ?>'
      };
      window.addToCart(product, window.detailSelectedSize);
    }

    /* ── Star rating picker ── */
    function setRating(rating) {
      document.querySelectorAll('.rating-star').forEach((star, i) => {
        star.classList.toggle('lit', i < rating);
      });
      document.getElementById('ratingValue').value = rating;
    }

    /* ── Submit review ── */
    function submitReview(e) {
      e.preventDefault();
      const rating  = parseInt(document.getElementById('ratingValue').value) || 0;
      const comment = document.getElementById('commentValue').value;
      if (rating === 0) { alert('Vui lòng chọn xếp hạng'); return; }

      fetch(window.BASE_URL + '/productdetail/<?= $product['id'] ?>/review', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rating, comment })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert('Cảm ơn đánh giá của bạn!');
          location.reload();
        } else {
          alert('Lỗi: ' + (data.error || 'Không thể gửi đánh giá'));
        }
      })
      .catch(() => alert('Lỗi kết nối'));
    }

    /* ── Init ── */
    window.addEventListener('DOMContentLoaded', () => {
      // Select first size
      const firstBtn = document.querySelector('.detail-size-pill');
      if (firstBtn) selectProductSize(firstBtn, firstBtn.textContent.trim());

      // Load cart
      setTimeout(() => {
        if (typeof window.loadCart === 'function') window.loadCart();
      }, 100);
    });
  </script>
</body>
</html>