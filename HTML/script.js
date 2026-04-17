// ── CURSOR ──
const cursor = document.getElementById("cursor");
const ring = document.getElementById("cursorRing");

document.addEventListener("mousemove", (e) => {
  cursor.style.left = e.clientX + "px";
  cursor.style.top = e.clientY + "px";
  ring.style.left = e.clientX + "px";
  ring.style.top = e.clientY + "px";
});

document
  .querySelectorAll(
    "a, button, .look-card, select, .pill, .page-btn, .modal-close, .modal-size-btn, .product-card",
  )
  .forEach((el) => {
    el.addEventListener("mouseenter", () => {
      cursor.classList.add("hovered");
      ring.classList.add("hovered");
    });
    el.addEventListener("mouseleave", () => {
      cursor.classList.remove("hovered");
      ring.classList.remove("hovered");
    });
  });

// ── IMAGE FALLBACK ──
window.handleImageError = function (img) {
  img.onerror = null; // Prevent infinite loop
  img.src = "https://via.placeholder.com/600x800?text=SOFT+EDGE+Streetwear";
};

// ── CART IMAGE URL HELPER ──
function getCartImageUrl(imageUrl) {
  if (!imageUrl) return "https://via.placeholder.com/100x100?text=No+Image";

  // If it's already a full URL, return as is
  if (imageUrl.startsWith("http://") || imageUrl.startsWith("https://")) {
    return imageUrl;
  }

  // If it starts with /, it's relative to root
  if (imageUrl.startsWith("/")) {
    return window.BASE_URL + imageUrl;
  }

  // Otherwise, assume it's relative to assets
  return window.BASE_URL + "/" + imageUrl;
}

// ── NAV SCROLL ──
window.addEventListener("scroll", () => {
  document
    .getElementById("nav")
    .classList.toggle("scrolled", window.scrollY > 60);
});

// ── TOAST ──
let cart = [];

function showToast(msg) {
  const t = document.getElementById("toast");
  t.textContent = msg;
  t.classList.add("show");
  setTimeout(() => t.classList.remove("show"), 2500);
}

async function loadCart() {
  if (window.isLoggedIn) {
    try {
      const response = await fetch(window.BASE_URL + "/api/cart");
      const data = await response.json();
      if (data.items) {
        cart = data.items.map((item) => ({
          id: item.product_id,
          name: item.product_name,
          size: item.size,
          color: item.color,
          price: item.unit_price,
          quantity: item.quantity,
          image: getCartImageUrl(item.image_url || item.image),
        }));
      }
    } catch (e) {
      console.error("Failed to load cart from API", e);
    }
  } else {
    const saved = localStorage.getItem("cart");
    if (saved) {
      cart = JSON.parse(saved);
    }
  }
  updateCartUI();
}

// Export functions for use in inline scripts
window.loadCart = loadCart;

function saveCartToLocal() {
  if (!window.isLoggedIn) {
    localStorage.setItem("cart", JSON.stringify(cart));
  }
}

function updateCartUI() {
  const cartCount = document.getElementById("cartCount");
  const cartItems = document.getElementById("cartItems");
  const cartTotal = document.getElementById("cartTotal");

  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  cartCount.textContent = totalItems;

  if (cart.length === 0) {
    cartItems.innerHTML = '<div class="cart-empty">Chưa có sản phẩm nào</div>';
    cartTotal.textContent = "0 ₫";
    return;
  }

  cartItems.innerHTML = cart
    .map(
      (item, index) => `
    <div class="cart-item">
      <div class="cart-item-img">
        <img src="${item.image}" alt="${item.name}">
      </div>
      <div class="cart-item-info">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-size">Size: ${item.size}</div>
        <div class="cart-item-price">${(item.price * item.quantity).toLocaleString("vi-VN")} ₫</div>
      </div>
      <div class="cart-item-quantity" style="display: flex; align-items: center; gap: 0.5rem;">
        <button class="cart-qty-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})" style="width: 24px; height: 24px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">-</button>
        <span style="min-width: 24px; text-align: center;">${item.quantity}</span>
        <button class="cart-qty-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})" style="width: 24px; height: 24px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">+</button>
      </div>
      <button class="cart-item-remove" onclick="removeFromCart(${index})">🗑️</button>
    </div>
  `,
    )
    .join("");

  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  cartTotal.textContent = total.toLocaleString("vi-VN") + " ₫";
}

window.updateQuantity = function (index, newQuantity) {
  if (window.isLoggedIn) {
    const item = cart[index];
    if (newQuantity <= 0) {
      removeFromCart(index);
    } else {
      fetch(window.BASE_URL + "/api/cart/update", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          product_id: item.id,
          size: item.size,
          quantity: newQuantity,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            loadCart();
          } else {
            showToast("Lỗi cập nhật");
          }
        })
        .catch((e) => {
          console.error(e);
          showToast("Lỗi kết nối");
        });
    }
  } else {
    if (newQuantity <= 0) {
      removeFromCart(index);
    } else {
      cart[index].quantity = newQuantity;
      updateCartUI();
      saveCartToLocal();
    }
  }
};

window.removeFromCart = function (index) {
  if (window.isLoggedIn) {
    const item = cart[index];
    fetch(window.BASE_URL + "/api/cart/remove", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ product_id: item.id, size: item.size }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          loadCart();
          showToast("Đã xóa khỏi giỏ hàng");
        } else {
          showToast("Lỗi xóa");
        }
      })
      .catch((e) => {
        console.error(e);
        showToast("Lỗi kết nối");
      });
  } else {
    cart.splice(index, 1);
    updateCartUI();
    saveCartToLocal();
    showToast("Đã xóa khỏi giỏ hàng");
  }
};

function addToCart(product, size) {
  if (window.isLoggedIn) {
    // Call API
    fetch(window.BASE_URL + "/api/cart/add", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ product_id: product.id, size: size, quantity: 1 }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          loadCart(); // Reload cart
          showToast(`"${product.name}" đã thêm vào giỏ ✓`);
        } else {
          showToast("Lỗi thêm vào giỏ");
        }
      })
      .catch((e) => {
        console.error(e);
        showToast("Lỗi kết nối");
      });
  } else {
    // Local storage
    const existingItem = cart.find(
      (item) => item.id === product.id && item.size === size,
    );
    if (existingItem) {
      existingItem.quantity++;
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        size: size,
        image: product.image,
        quantity: 1,
      });
    }
    updateCartUI();
    saveCartToLocal();
    showToast(`"${product.name}" đã thêm vào giỏ ✓`);
  }
}

// Export for use in inline scripts
window.addToCart = addToCart;

function saveCartToLocal() {
  localStorage.setItem("mist_cart", JSON.stringify(cart));
}

function loadCartFromLocal() {
  const saved = localStorage.getItem("mist_cart");
  if (saved) {
    cart = JSON.parse(saved);
    updateCartUI();
  }
}

// Cart sidebar
const cartBtn = document.getElementById("cartBtn");
const cartSidebar = document.getElementById("cartSidebar");
const cartOverlay = document.getElementById("cartOverlay");
const cartClose = document.getElementById("cartClose");

function openCart() {
  cartSidebar.classList.add("open");
  cartOverlay.classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeCart() {
  cartSidebar.classList.remove("open");
  cartOverlay.classList.remove("open");
  document.body.style.overflow = "";
}

cartBtn.addEventListener("click", (e) => {
  e.preventDefault();
  openCart();
});

cartClose.addEventListener("click", closeCart);
cartOverlay.addEventListener("click", closeCart);

document.getElementById("checkoutBtn").addEventListener("click", async () => {
  if (!window.isLoggedIn) {
    showToast("Vui lòng đăng nhập để thanh toán!");
    window.location.href = window.BASE_URL + "/register";
    return;
  }

  if (cart.length === 0) {
    showToast("Giỏ hàng trống!");
    return;
  }

  // Redirect to payment page
  window.location.href = window.BASE_URL + "/pay";
});

// Subscribe
document.getElementById("subscribeBtn").addEventListener("click", () => {
  const email = document.getElementById("emailInput").value;
  if (email) {
    showToast("Đã đăng ký thành công! 🎉");
    document.getElementById("emailInput").value = "";
  } else {
    showToast("Vui lòng nhập email!");
  }
});

// ── FADE UP ──
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) e.target.classList.add("visible");
    });
  },
  { threshold: 0.08 },
);

document.querySelectorAll(".fade-up").forEach((el) => observer.observe(el));

// ── PRODUCT DATA (FETCH FROM API) ──
let products = [];
let categories = [];

// ── STATE ──
let activeCategory = "all";
let currentPage = 1;
const PER_PAGE = 8; // Show 12 products per page
let filtered = [...products];

function renderCategoryPills() {
  const container = document.getElementById("filterPills");
  if (!container) return;

  const seen = new Set();
  const pills = [];
  pills.push(
    `<a href="?category=all" class="pill${activeCategory === "all" ? " active" : ""}" data-category="all">Tất cả</a>`,
  );

  categories.forEach((cat) => {
    seen.add(cat.slug);
    const active = cat.slug === activeCategory ? " active" : "";
    pills.push(
      `<a href="?category=${encodeURIComponent(cat.slug)}" class="pill${active}" data-category="${cat.slug}">${cat.category_name}</a>`,
    );
  });

  const productCategories = Array.from(
    new Set(
      products.map((p) => p.category).filter((slug) => slug && !seen.has(slug)),
    ),
  );
  productCategories.forEach((slug) => {
    const name = slug
      .replace(/[-_]/g, " ")
      .replace(/\b\w/g, (c) => c.toUpperCase());
    const active = slug === activeCategory ? " active" : "";
    pills.push(
      `<a href="?category=${encodeURIComponent(slug)}" class="pill${active}" data-category="${slug}">${name}</a>`,
    );
  });

  container.innerHTML = pills.join("");
  attachCategoryPillListeners();
}

function attachCategoryPillListeners() {
  document.querySelectorAll("#filterPills .pill").forEach((pill) => {
    pill.addEventListener("click", (e) => {
      e.preventDefault();
      const cat = pill.getAttribute("data-category");
      const params = new URLSearchParams(location.search);
      params.set("category", cat);
      history.pushState({ category: cat }, "", "?" + params.toString());
      setCategory(cat, pill);
    });
  });
}

function setCategory(cat, btn) {
  activeCategory = cat;
  document
    .querySelectorAll("#filterPills .pill")
    .forEach((p) => p.classList.remove("active"));
  if (btn) btn.classList.add("active");
  currentPage = 1;
  filterProducts();
}

function filterProducts() {
  const q = document.getElementById("searchInput").value.toLowerCase().trim();
  const sort = document.getElementById("sortSelect").value;

  filtered = products.filter((p) => {
    const matchCat = activeCategory === "all" || p.category === activeCategory;
    const matchQ =
      !q ||
      p.name.toLowerCase().includes(q) ||
      p.color.toLowerCase().includes(q);
    return matchCat && matchQ;
  });

  if (sort === "price-asc") filtered.sort((a, b) => a.price - b.price);
  else if (sort === "price-desc") filtered.sort((a, b) => b.price - a.price);
  else if (sort === "name")
    filtered.sort((a, b) => a.name.localeCompare(b.name));

  currentPage = 1;
  renderGrid();
}

function readUrlParams() {
  const params = new URLSearchParams(location.search);
  const cat = params.get("category") || "all";
  activeCategory = cat;
  document.querySelectorAll("#filterPills .pill").forEach((p) => {
    p.classList.toggle("active", p.getAttribute("data-category") === cat);
  });
}

// ── SYNC URL on LOAD ──
window.addEventListener("load", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const p = parseInt(urlParams.get("page"));
  if (p && !isNaN(p)) {
    currentPage = p;
    renderGrid();
  }
});

// ── HANDLE BACK/FORWARD ──
window.addEventListener("popstate", (e) => {
  if (e.state && e.state.page) {
    currentPage = e.state.page;
    renderGrid();
  }
});

function renderGrid() {
  const grid = document.getElementById("productsGrid");
  const start = (currentPage - 1) * PER_PAGE;
  const page = filtered.slice(start, start + PER_PAGE);
  const totalPages = Math.ceil(filtered.length / PER_PAGE);
  document.getElementById("productCount").textContent =
    `${filtered.length} sản phẩm`;

  if (page.length === 0) {
    grid.innerHTML =
      '<div class="empty-state"><strong>KHÔNG TÌM THẤY</strong>Thử từ khóa khác nhé.</div>';
    document.getElementById("pagination").innerHTML = "";
    return;
  }

  grid.innerHTML = page
    .map((p, i) => {
      // Clean URL: avoid double query params if it's already an Unsplash/parameterized URL
      let imgSrc = p.image;
      if (!imgSrc.includes("w=")) {
        imgSrc += (imgSrc.includes("?") ? "&" : "?") + "w=600&h=800&fit=crop";
      }

      return `
        <div class="product-card-link">
          <div class="product-card fade-up visible" style="transition-delay:${(i % 4) * 0.05}s" onclick="window.location.href='${window.BASE_URL}/productdetail/${p.id}'">
            <div class="product-img">
              <img src="${imgSrc}" alt="${p.name}" onerror="handleImageError(this)">
             
              <button class="wishlist-btn" onclick="event.stopPropagation(); addToCartFromCard('${p.id}')">♡</button>
            </div>
            <div class="product-info">
              <div class="product-name">${p.name}</div>
              <div class="product-sub">${p.color}</div>
              <div class="product-footer">
                <div class="product-price">${p.price.toLocaleString("vi-VN")} ₫</div>
                <div class="product-sizes">${p.sizes
                  .slice(0, 3)
                  .map((s) => `<div class="size-dot">${s}</div>`)
                  .join("")}</div>
              </div>
            </div>
          </div>
        </div>`;
    })
    .join("");

  renderPagination(totalPages);
}

window.addToCartFromCard = function (id) {
  const product = products.find((p) => p.id === id);
  if (product) {
    addToCart(product, product.sizes[0]);
  }
};

function renderPagination(totalPages) {
  if (totalPages <= 1) {
    document.getElementById("pagination").innerHTML = "";
    return;
  }

  let html = `<button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? "disabled" : ""}>‹</button>`;

  // Show first page
  if (currentPage > 3) {
    html += `<button class="page-btn" onclick="goPage(1)">1</button>`;
    if (currentPage > 4)
      html += `<span style="color:var(--light);line-height:38px;">…</span>`;
  }

  // Show pages around current
  for (
    let i = Math.max(1, currentPage - 2);
    i <= Math.min(totalPages, currentPage + 2);
    i++
  ) {
    html += `<button class="page-btn ${i === currentPage ? "active" : ""}" onclick="goPage(${i})">${i}</button>`;
  }

  // Show last page
  if (currentPage < totalPages - 2) {
    if (currentPage < totalPages - 3)
      html += `<span style="color:var(--light);line-height:38px;">…</span>`;
    html += `<button class="page-btn" onclick="goPage(${totalPages})">${totalPages}</button>`;
  }

  html += `<button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === totalPages ? "disabled" : ""}>›</button>`;
  document.getElementById("pagination").innerHTML = html;
}

window.goPage = function (p) {
  if (p < 1 || p > Math.ceil(filtered.length / PER_PAGE)) return;
  currentPage = p;

  // Update URL string
  const url = new URL(window.location);
  url.searchParams.set("page", p);
  history.pushState({ page: p }, "", url);

  renderGrid();
  document
    .getElementById("collection")
    .scrollIntoView({ behavior: "smooth", block: "start" });
};

function capitalize(s) {
  if (!s) return "";
  return s.charAt(0).toUpperCase() + s.slice(1);
}

// ── MODAL ──
let currentProduct = null;
let selectedSize = null;

window.openModal = function (id) {
  currentProduct = products.find((p) => p.id === id);
  if (!currentProduct) return;
  selectedSize = currentProduct.sizes[0];

  const categoryDisplay = getCategoryDisplay(currentProduct.category);

  document.getElementById("modalTag").textContent =
    `${categoryDisplay} — ${currentProduct.tag || "MIST"}`;
  document.getElementById("modalName").textContent = currentProduct.name;
  document.getElementById("modalSub").textContent =
    `Màu: ${currentProduct.color}`;
  document.getElementById("modalPrice").textContent =
    currentProduct.price.toLocaleString("vi-VN") + " ₫";
  document.getElementById("modalDesc").textContent = currentProduct.desc;

  let modalImgSrc = currentProduct.image;
  if (!modalImgSrc.includes("w=")) {
    modalImgSrc +=
      (modalImgSrc.includes("?") ? "&" : "?") + "w=600&h=800&fit=crop";
  }

  document.getElementById("modalImg").innerHTML =
    `<img src="${modalImgSrc}" alt="${currentProduct.name}" onerror="handleImageError(this)">`;
  document.getElementById("modalSizes").innerHTML = currentProduct.sizes
    .map(
      (s) =>
        `<button class="modal-size-btn ${s === selectedSize ? "sel" : ""}" onclick="selectSize(this,'${s}')">${s}</button>`,
    )
    .join("");
  document.getElementById("modalMeta").innerHTML =
    `SKU: MIST-${String(currentProduct.id).padStart(4, "0")} &nbsp;·&nbsp; ${currentProduct.isNew ? "Hàng mới về" : "Còn hàng"} &nbsp;·&nbsp; Miễn phí ship`;
  document.getElementById("modalAddBtn").onclick = () => {
    if (selectedSize) {
      addToCart(currentProduct, selectedSize);
      document.getElementById("productModal").classList.remove("open");
    } else {
      showToast("Vui lòng chọn size!");
    }
  };
  document.getElementById("productModal").classList.add("open");
};

function getCategoryDisplay(category) {
  const map = {
    hoodie: "Hoodie",
    tshirt: "T-Shirt",
    cargo: "Cargo Pants",
    jacket: "Jacket",
    shorts: "Shorts",
    cap: "Cap",
    bag: "Bag",
    longsleeve: "Long Sleeve",
  };
  return map[category] || category;
}

window.selectSize = function (btn, size) {
  document
    .querySelectorAll(".modal-size-btn")
    .forEach((b) => b.classList.remove("sel"));
  btn.classList.add("sel");
  selectedSize = size;
};

// Modal close
document.getElementById("productModalClose").addEventListener("click", () => {
  document.getElementById("productModal").classList.remove("open");
});

document.getElementById("productModal").addEventListener("click", (e) => {
  if (e.target === document.getElementById("productModal")) {
    document.getElementById("productModal").classList.remove("open");
  }
});

// Event listeners
document
  .getElementById("searchInput")
  .addEventListener("input", filterProducts);
document
  .getElementById("sortSelect")
  .addEventListener("change", filterProducts);

// Đọc URL param khi load trang
function readUrlParams() {
  const params = new URLSearchParams(location.search);
  const cat = params.get("category") || "all";
  activeCategory = cat;
  document.querySelectorAll("#filterPills .pill").forEach((p) => {
    p.classList.toggle("active", p.getAttribute("data-category") === cat);
  });
}

// Xử lý nút Back/Forward trình duyệt
window.addEventListener("popstate", (e) => {
  const cat =
    (e.state && e.state.category) ||
    new URLSearchParams(location.search).get("category") ||
    "all";
  activeCategory = cat;
  document.querySelectorAll("#filterPills .pill").forEach((p) => {
    const isCat = p.getAttribute("data-category") === cat;
    p.classList.toggle("active", isCat);
  });
  filterProducts();
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const target = this.getAttribute("href");
    if (target === "#") return;
    const element = document.querySelector(target);
    if (element) {
      e.preventDefault();
      element.scrollIntoView({ behavior: "smooth" });
    }
  });
});

// ── LOGIN MODAL ──
window.openLoginModal = function (e) {
  if (e) e.preventDefault();
  document.getElementById("loginModal").classList.add("open");
  document.body.style.overflow = "hidden";
};

function closeLoginModal() {
  document.getElementById("loginModal").classList.remove("open");
  document.body.style.overflow = "";
}

document
  .getElementById("loginModalClose")
  .addEventListener("click", closeLoginModal);

// Mobile Menu logic
const navToggle = document.getElementById("navToggle");
const mobileMenu = document.getElementById("mobileMenu");
const navEl = document.getElementById("nav");

window.toggleMobileMenu = function () {
  const isOpen = mobileMenu.classList.toggle("open");
  navEl.classList.toggle("mobile-open", isOpen);
  document.body.style.overflow = isOpen ? "hidden" : "";
};

window.closeMobileMenu = function () {
  mobileMenu.classList.remove("open");
  navEl.classList.remove("mobile-open");
  document.body.style.overflow = "";
};

if (navToggle) {
  navToggle.addEventListener("click", toggleMobileMenu);
}

// Close mobile menu when clicking a link
document.querySelectorAll(".mobile-link").forEach((link) => {
  link.addEventListener("click", closeMobileMenu);
});

document.getElementById("loginModal").addEventListener("click", (e) => {
  if (e.target === document.getElementById("loginModal")) closeLoginModal();
});

window.switchTab = function (tab) {
  const isLogin = tab === "login";
  document.getElementById("formLogin").classList.toggle("hidden", !isLogin);
  document.getElementById("formRegister").classList.toggle("hidden", isLogin);
  document.getElementById("tabLogin").classList.toggle("active", isLogin);
  document.getElementById("tabRegister").classList.toggle("active", !isLogin);
};

window.handleLogin = async function (e) {
  e.preventDefault();
  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;
  if (!email || !password) {
    showToast("Vui lòng điền đủ thông tin!");
    return;
  }

  showToast("Đang đăng nhập...");
  try {
    const res = await fetch((window.BASE_URL || "/HTML") + "/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      showToast("Đăng nhập thành công, đang tải lại trang... 👋");
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast("Lỗi: " + (data.error || "Sai thông tin"));
    }
  } catch (err) {
    console.error(err);
    showToast("Lỗi kết nối!");
  }
};

window.handleRegister = function (e) {
  e.preventDefault();
  const name = document.getElementById("regName").value;
  const email = document.getElementById("regEmail").value;
  const password = document.getElementById("regPassword").value;
  if (!name || !email || !password) {
    showToast("Vui lòng điền đủ thông tin!");
    return;
  }
  const params = new URLSearchParams(location.search);
  params.set("action", "register");
  params.set("user", name);
  history.pushState({}, "", "?" + params.toString());
  const btn = document.getElementById("loginBtn");
  btn.textContent = "👤 " + name;
  btn.classList.add("logged-in");
  showToast("Tài khoản đã tạo thành công! 🎉");
  closeLoginModal();
};

window.handleSocialLogin = function (provider) {
  showToast("Đang kết nối với " + provider + "...");
  setTimeout(() => {
    const btn = document.getElementById("loginBtn");
    btn.textContent = "👤 Google User";
    btn.classList.add("logged-in");
    showToast("Đăng nhập Google thành công! 🎉");
    closeLoginModal();
  }, 1200);
};

// ── INIT ──
loadCart();

async function initStore() {
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      fetch(window.BASE_URL + "/api/products"),
      fetch(window.BASE_URL + "/api/categories"),
    ]);

    if (!productsRes.ok)
      throw new Error(
        `Products API ${productsRes.status} ${productsRes.statusText}`,
      );
    if (!categoriesRes.ok)
      throw new Error(
        `Categories API ${categoriesRes.status} ${categoriesRes.statusText}`,
      );

    products = await productsRes.json();
    const categoriesData = await categoriesRes.json();
    categories = Array.isArray(categoriesData.categories)
      ? categoriesData.categories
      : [];

    filtered = [...products];
    renderCategoryPills();
    readUrlParams(); // đọc ?category= từ URL
    filterProducts(); // filter ngay theo category
    console.log(
      `✅ Loaded ${products.length} products and ${categories.length} categories from API!`,
    );
  } catch (err) {
    console.error("Failed to initialize store", err);
    document.getElementById("productsGrid").innerHTML =
      `<div class="empty-state"><strong>Error loading products</strong><br>${err.message}</div>`;
  }
}

initStore();
