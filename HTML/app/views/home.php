<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SOFT EDGE — Streetwear</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
      href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&family=Inter:wght@300;400;500;700&family=Bebas+Neue&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="<?= ASSET_URL ?>/style.css?v=<?= time() ?>" />
    <script>
        window.isLoggedIn = <?= isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] ? 'true' : 'false' ?>;
        window.userId = '<?= $_SESSION['user_id'] ?? '' ?>';
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
  </head>
  <body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <div class="cursor" id="cursor"></div>
    <div class="cursor-ring" id="cursorRing"></div>

    <!-- NAV -->
    <nav id="nav" class="nav">
      <a href="#" class="nav-logo">SOFT<span class="logo-edge">EDGE</span></a>
      <div class="nav-links desktop-only">
        <a href="#collection">Collection</a>
        <a href="#lookbook">Lookbook</a>
        <a href="#about">About</a>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
          <a href="<?= BASE_URL ?>/admin/dashboard" style="font-weight: 500; color: #000;">Dashboard (Admin)</a>
        <?php endif; ?>
      </div>
      <div class="nav-cart">
        <?php 
        $isLoggedIn = false;
        $userName = '';
        if (isset($_SESSION['user_name'])) {
            $isLoggedIn = true;
            $userName = $_SESSION['user_name'];
        } elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $isLoggedIn = true;
            $userName = 'Admin';
        }
        ?>
        <?php if ($isLoggedIn): ?>
          <a href="<?= BASE_URL ?>/logout" class="login-btn desktop-only" style="border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px;">👤 <?= htmlspecialchars($userName) ?> (Thoát)</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/register" class="login-btn desktop-only" id="loginBtn">Đăng nhập</a>
        <?php endif; ?>
        <a href="#" class="cart-btn" id="cartBtn">
          Giỏ hàng
          <span class="cart-count" id="cartCount">0</span>
        </a>
        <!-- MOBILE TOGGLE -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
           <span></span>
           <span></span>
           <span></span>
        </button>
      </div>
    </nav>

    <!-- MOBILE MENU OVERLAY -->
    <div class="mobile-menu-overlay" id="mobileMenu">
      <div class="mobile-menu-content">
        <a href="#collection" class="mobile-link">Collection</a>
        <a href="#lookbook" class="mobile-link">Lookbook</a>
        <a href="#about" class="mobile-link">About</a>
        <div class="mobile-divider"></div>
        <?php if ($isLoggedIn): ?>
          <a href="<?= BASE_URL ?>/logout" class="mobile-link">👤 <?= htmlspecialchars($userName) ?> (Thoát)</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/register" class="mobile-link" onclick="closeMobileMenu()">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- HERO -->
    <section class="hero" style="position: relative; min-height: 100vh; overflow: hidden;">
      <img src="<?= ASSET_URL ?>/assets/image/hero.jpg" alt="Hero" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; z-index: 0;" />
      <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.2); z-index: 1;"></div>
      <div class="hero-left" style="position: relative; z-index: 2; color: #fff;">
        <div class="hero-tag">SS 2025 — Drop 01</div>
        <h1 class="hero-title"  >
          <span>SOFT</span>
          <em>EDGE</em>
        </h1>
        <p class="hero-desc">
          Streetwear được tái định nghĩa — không cần phải to tiếng mới để nổi
          bật. Pastel mạnh mẽ theo cách của riêng nó.
        </p>
        <div class="hero-actions">
          <a href="#collection" class="btn-primary">Khám phá ngay</a>
          <a href="#lookbook" class="btn-ghost">Lookbook</a>
        </div>
      </div>
    </section>

    <!-- MARQUEE -->
    <div class="marquee-section">
      <div class="marquee-track">
        <span class="marquee-item">Free shipping toàn quốc</span>
        <span class="marquee-item">Drop mới mỗi tháng</span>
        <span class="marquee-item">Limited pieces only</span>
        <span class="marquee-item">100% cotton organic</span>
        <span class="marquee-item">SOFT EDGE Studio — Hồ Chí Minh</span>
        <span class="marquee-item">Free shipping toàn quốc</span>
        <span class="marquee-item">Drop mới mỗi tháng</span>
        <span class="marquee-item">Limited pieces only</span>
        <span class="marquee-item">100% cotton organic</span>
        <span class="marquee-item">SOFT EDGE Studio — Hồ Chí Minh</span>
      </div>
    </div>

    <!-- COLLECTION -->
    <section id="collection">
      <div class="section-header fade-up">
        <div>
          <div class="section-eyebrow">Bộ sưu tập</div>
          <div class="section-title">ALL PRODUCTS</div>
        </div>
        <div
          style="font-size: 0.82rem; color: var(--light)"
          id="productCount"
        ></div>
      </div>

      <!-- Search + Filter bar (2 row) -->
      <div class="filter-bar fade-up">
        <!-- Row 1: Search + Sort -->
        <div class="filter-row-top">
          <div class="search-wrap">
            <span class="search-icon">⌕</span>
            <input type="text" id="searchInput" placeholder="Tìm sản phẩm..." />
          </div>
          <select id="sortSelect">
            <option value="default">Mặc định</option>
            <option value="price-asc">Giá thấp → cao</option>
            <option value="price-desc">Giá cao → thấp</option>
            <option value="name">Tên A–Z</option>
          </select>
        </div>
        <!-- Row 2: Pills (GET method) -->
        <div class="filter-pills" id="filterPills"></div>
      </div>

      <div class="products-grid" id="productsGrid"></div>

      <!-- Pagination -->
      <div class="pagination" id="pagination"></div>
    </section>

    <!-- Cart Sidebar -->
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
        <button class="btn-primary checkout-btn" id="checkoutBtn">
          Thanh toán
        </button>
      </div>
    </div>
    <div class="cart-overlay" id="cartOverlay"></div>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
      <div class="login-modal-box">
        <button class="modal-close" id="loginModalClose">✕</button>
        <div class="login-modal-left">
          <div class="login-brand">SOFT<span>EDGE</span></div>
          <p class="login-tagline">Streetwear được tái định nghĩa.</p>
          <div class="login-deco"></div>
        </div>
        <div class="login-modal-right">
          <!-- Tabs -->
          <div class="login-tabs">
            <button class="login-tab active" id="tabLogin" onclick="switchTab('login')">Đăng nhập</button>
            <button class="login-tab" id="tabRegister" onclick="switchTab('register')">Đăng ký</button>
          </div>

          <!-- Login Form -->
          <form class="auth-form" id="formLogin" method="GET" action="#" onsubmit="handleLogin(event)">
            <div class="form-group">
              <label>Email</label>
              <input type="text" id="loginEmail" name="email" placeholder="Email hoặc 'admin'" required />
            </div>
            <div class="form-group">
              <label>Mật khẩu</label>
              <input type="password" id="loginPassword" name="password" placeholder="••••••••" required />
            </div>
            <div class="form-options">
              <label class="remember-me"><input type="checkbox" name="remember" /> Nhớ tôi</label>
              <a href="#" class="forgot-link">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn-primary auth-submit">Đăng nhập</button>
            <div class="auth-divider"><span>hoặc</span></div>
            <button type="button" class="btn-social" onclick="handleSocialLogin('google')">
              <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Tiếp tục với Google
            </button>
          </form>

          <!-- Register Form -->
          
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal-overlay">
      <div class="modal-box">
        <button class="modal-close" id="productModalClose">✕</button>
        <div class="modal-img" id="modalImg"></div>
        <div class="modal-info">
          <div class="modal-tag" id="modalTag"></div>
          <h2 class="modal-name" id="modalName"></h2>
          <div class="modal-sub" id="modalSub"></div>
          <div class="modal-price" id="modalPrice"></div>
          <div class="modal-desc" id="modalDesc"></div>
          <div class="modal-sizes-label">Chọn size:</div>
          <div class="modal-sizes" id="modalSizes"></div>
          <div class="modal-actions">
            <button class="btn-primary" id="modalAddBtn">Thêm vào giỏ</button>
          </div>
          <div class="modal-meta" id="modalMeta"></div>
        </div>
      </div>
    </div>

    <!-- FEATURE STRIP -->
    <div class="feature-strip">
      <div class="feature-item fade-up">
        <div class="feature-icon sky">🚚</div>
        <div class="feature-text">
          <h4>Free Shipping</h4>
          <p>Miễn phí vận chuyển toàn quốc cho đơn từ 500k</p>
        </div>
      </div>
      <div class="feature-item fade-up" style="transition-delay: 0.1s">
        <div class="feature-icon sage">♻️</div>
        <div class="feature-text">
          <h4>Sustainable</h4>
          <p>Vải organic, quy trình sản xuất thân thiện môi trường</p>
        </div>
      </div>
      <div class="feature-item fade-up" style="transition-delay: 0.2s">
        <div class="feature-icon peach">↩️</div>
        <div class="feature-text">
          <h4>30-day Return</h4>
          <p>Đổi trả trong vòng 30 ngày, không cần lý do</p>
        </div>
      </div>
      <div class="feature-item fade-up" style="transition-delay: 0.3s">
        <div class="feature-icon lavender">✦</div>
        <div class="feature-text">
          <h4>Limited Drops</h4>
          <p>Mỗi sản phẩm chỉ sản xuất số lượng giới hạn</p>
        </div>
      </div>
    </div>

    <!-- LOOKBOOK -->
    <section class="lookbook" id="lookbook">
  <div class="section-header fade-up">
    <div>
      <div class="section-eyebrow">SS 2025</div>
      <div class="section-title">LOOKBOOK</div>
    </div>
    <a href="#" class="view-all">Xem tất cả →</a>
  </div>

  <div class="lookbook-grid">

  <!-- LOOK 1 (to hơn) -->
  <div class="look-card fade-up">
    <div class="look-inner">
      <img src="<?= ASSET_URL ?>/assets/image/look1.jpg" class="look-img" alt="Look 01">
      <div class="look-overlay"></div>
      <div class="look-info">
        <div class="look-name">Look 01 — "Still Waters"</div>
        <div class="look-price">Aura Hoodie + Cloud Cargo</div>
      </div>
    </div>
  </div>

  <!-- LOOK 2 -->
  <div class="look-card fade-up" style="transition-delay: 0.1s">
    <div class="look-inner">
      <img src="<?= ASSET_URL ?>/assets/image/look2.jpg" class="look-img" alt="Look 02">
      <div class="look-overlay"></div>
      <div class="look-info">
        <div class="look-name">Look 02 — "Morning Light"</div>
        <div class="look-price">Soft Tee + Haze Jacket</div>
      </div>
    </div>
  </div>

  <!-- LOOK 3 -->
  <div class="look-card fade-up" style="transition-delay: 0.2s">
    <div class="look-inner">
      <img src="<?= ASSET_URL ?>/assets/image/look3.jpg" class="look-img" alt="Look 03">
      <div class="look-overlay"></div>
      <div class="look-info">
        <div class="look-name">Look 03 — "Quiet Confidence"</div>
        <div class="look-price">Cloud Cargo + Soft Tee</div>
      </div>
    </div>
  </div>

  <!-- LOOK 4 -->
  <div class="look-card fade-up" style="transition-delay: 0.1s">
    <div class="look-inner">
      <img src="<?= ASSET_URL ?>/assets/image/look4.jpg" class="look-img" alt="Look 04">
      <div class="look-overlay"></div>
      <div class="look-info">
        <div class="look-name">Look 04 — "Drift"</div>
        <div class="look-price">Haze Jacket + Aura Hoodie</div>
      </div>
    </div>
  </div>

  <!-- LOOK 5 -->
  <div class="look-card fade-up" style="transition-delay: 0.2s">
    <div class="look-inner">
      <img src="<?= ASSET_URL ?>/assets/image/look5.jpg" class="look-img" alt="Look 05">
      <div class="look-overlay"></div>
      <div class="look-info">
        <div class="look-name">Look 05 — "Ease"</div>
        <div class="look-price">Full Set — Drop 01</div>
      </div>
    </div>
  </div>

</div>
</section>
    <!-- NEWSLETTER -->
    <section class="newsletter">
      <div class="section-eyebrow">Stay in the loop</div>
      <div class="section-title fade-up">FIRST TO KNOW,<br />FIRST TO WEAR</div>
      <p>Đăng ký nhận thông báo về các drop mới và ưu đãi exclusive.</p>
      <div class="newsletter-form">
        <input type="email" placeholder="email của bạn" id="emailInput" />
        <button id="subscribeBtn">Đăng ký</button>
      </div>
    </section>

    <!-- ABOUT -->
    <section class="about" id="about" style="background: black; padding: 7rem 3rem;">
  <div style="width: 100%; display: flex; justify-content: center; align-items: center;">

    <div id="aboutCarousel"
         class="carousel slide"
         data-bs-ride="carousel"
         data-bs-interval="3000"
         style="width: 100%; max-width: 1400px; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.10);">

      <div class="carousel-indicators" style="margin-bottom: 1rem;">
        <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>

      <div class="carousel-inner" style="border-radius: 24px;">
        <div class="carousel-item active">
          <img src="<?= ASSET_URL ?>/assets/image/about1.jpg"
               class="d-block w-100"
               alt="About 1"
               style="height: 650px; object-fit: cover;">
        </div>

        <div class="carousel-item">
          <img src="<?= ASSET_URL ?>/assets/image/about2.jpg"
               class="d-block w-100"
               alt="About 2"
               style="height: 650px; object-fit: cover;">
        </div>

        <div class="carousel-item">
          <img src="<?= ASSET_URL ?>/assets/image/about3.jpg"
               class="d-block w-100"
               alt="About 3"
               style="height: 650px; object-fit: cover;">
        </div>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 3rem; height: 3rem;"></span>
        <span class="visually-hidden">Previous</span>
      </button>

      <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true" style="width: 3rem; height: 3rem;"></span>
        <span class="visually-hidden">Next</span>
      </button>

    </div>

  </div>
</section>

    <!-- FOOTER -->
    <footer>
      <div class="footer-top">
        <div class="footer-brand">
          <a href="#" class="nav-logo">SOFT<span class="logo-edge">EDGE</span></a>
          <p>
            Streetwear được làm chậm lại — pastel, purpose-driven, và luôn giới
            hạn số lượng.
          </p>
        </div>
        <div class="footer-col">
          <h5>Shop</h5>
          <a href="#collection">New Arrivals</a>
          <a href="#collection">Hoodies</a>
          <a href="#collection">T-Shirts</a>
          <a href="#collection">Bottoms</a>
          <a href="#collection">Accessories</a>
        </div>
        <div class="footer-col">
          <h5>Info</h5>
          <a href="#about">About SOFT EDGE</a>
          <a href="#">Sustainability</a>
          <a href="#">Size Guide</a>
          <a href="#lookbook">Lookbook</a>
        </div>
        <div class="footer-col">
          <h5>Support</h5>
          <a href="#">Shipping &amp; Returns</a>
          <a href="#">FAQ</a>
          <a href="#">Contact</a>
          <a href="#">Track Order</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2025 SOFT EDGE Studio. All rights reserved.</span>
        <div class="footer-socials">
          <a href="#" class="social-link">ig</a>
          <a href="#" class="social-link">tt</a>
          <a href="#" class="social-link">fb</a>
        </div>
      </div>
    </footer>

    <div class="toast" id="toast">Đã thêm vào giỏ hàng ✓</div>

    <script>
      // Global config for frontend
      window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= ASSET_URL ?>/script.js?v=<?= time() ?>"></script>
  </body>
</html>