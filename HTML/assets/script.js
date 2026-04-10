// ── CURSOR ──
const cursor = document.getElementById('cursor');
const ring = document.getElementById('cursorRing');

document.addEventListener('mousemove', e => {
  cursor.style.left = e.clientX + 'px';
  cursor.style.top = e.clientY + 'px';
  ring.style.left = e.clientX + 'px';
  ring.style.top = e.clientY + 'px';
});

document.querySelectorAll('a, button, .look-card, select, .pill, .page-btn, .modal-close, .modal-size-btn, .product-card').forEach(el => {
  el.addEventListener('mouseenter', () => {
    cursor.classList.add('hovered');
    ring.classList.add('hovered');
  });
  el.addEventListener('mouseleave', () => {
    cursor.classList.remove('hovered');
    ring.classList.remove('hovered');
  });
});

// ── NAV SCROLL ──
window.addEventListener('scroll', () => {
  document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 60);
});

// ── TOAST ──
let cart = [];

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2500);
}

function updateCartUI() {
  const cartCount = document.getElementById('cartCount');
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  cartCount.textContent = totalItems;
  
  if (cart.length === 0) {
    cartItems.innerHTML = '<div class="cart-empty">Chưa có sản phẩm nào</div>';
    cartTotal.textContent = '0 ₫';
    return;
  }
  
  cartItems.innerHTML = cart.map((item, index) => `
    <div class="cart-item">
      <div class="cart-item-img">
        <img src="${item.image}" alt="${item.name}">
      </div>
      <div class="cart-item-info">
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-size">Size: ${item.size}</div>
        <div class="cart-item-price">${(item.price * item.quantity).toLocaleString('vi-VN')} ₫</div>
      </div>
      <div class="cart-item-quantity" style="display: flex; align-items: center; gap: 0.5rem;">
        <button class="cart-qty-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})" style="width: 24px; height: 24px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">-</button>
        <span style="min-width: 24px; text-align: center;">${item.quantity}</span>
        <button class="cart-qty-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})" style="width: 24px; height: 24px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">+</button>
      </div>
      <button class="cart-item-remove" onclick="removeFromCart(${index})">🗑️</button>
    </div>
  `).join('');
  
  const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  cartTotal.textContent = total.toLocaleString('vi-VN') + ' ₫';
}

window.updateQuantity = function(index, newQuantity) {
  if (newQuantity <= 0) {
    removeFromCart(index);
  } else {
    cart[index].quantity = newQuantity;
    updateCartUI();
    saveCartToLocal();
  }
};

window.removeFromCart = function(index) {
  cart.splice(index, 1);
  updateCartUI();
  saveCartToLocal();
  showToast('Đã xóa khỏi giỏ hàng');
};

function addToCart(product, size) {
  const existingItem = cart.find(item => item.id === product.id && item.size === size);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      size: size,
      image: product.image,
      quantity: 1
    });
  }
  updateCartUI();
  saveCartToLocal();
  showToast(`"${product.name}" đã thêm vào giỏ ✓`);
}

function saveCartToLocal() {
  localStorage.setItem('mist_cart', JSON.stringify(cart));
}

function loadCartFromLocal() {
  const saved = localStorage.getItem('mist_cart');
  if (saved) {
    cart = JSON.parse(saved);
    updateCartUI();
  }
}

// Cart sidebar
const cartBtn = document.getElementById('cartBtn');
const cartSidebar = document.getElementById('cartSidebar');
const cartOverlay = document.getElementById('cartOverlay');
const cartClose = document.getElementById('cartClose');

function openCart() {
  cartSidebar.classList.add('open');
  cartOverlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  cartSidebar.classList.remove('open');
  cartOverlay.classList.remove('open');
  document.body.style.overflow = '';
}

cartBtn.addEventListener('click', (e) => {
  e.preventDefault();
  openCart();
});

cartClose.addEventListener('click', closeCart);
cartOverlay.addEventListener('click', closeCart);

document.getElementById('checkoutBtn').addEventListener('click', () => {
  if (cart.length === 0) {
    showToast('Giỏ hàng trống!');
    return;
  }
  showToast('Chức năng thanh toán đang phát triển!');
  closeCart();
});

// Subscribe
document.getElementById('subscribeBtn').addEventListener('click', () => {
  const email = document.getElementById('emailInput').value;
  if (email) {
    showToast('Đã đăng ký thành công! 🎉');
    document.getElementById('emailInput').value = '';
  } else {
    showToast('Vui lòng nhập email!');
  }
});

// ── FADE UP ──
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) e.target.classList.add('visible');
  });
}, { threshold: 0.08 });

document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

// ── PRODUCT DATA (100 PRODUCTS) ──
const productNames = {
  hoodie: ['Aura', 'Drift', 'Mist', 'Cloud', 'Haze', 'Bloom', 'Fog', 'Soft', 'Still', 'Pale', 'Dawn', 'Dusk', 'Mellow', 'Zen', 'Calm', 'Pure', 'Bare', 'Glow', 'Hush', 'Echo', 'Veil', 'Arch', 'Tone', 'Form', 'Rime', 'Lull', 'Dew', 'Ebb', 'Flow', 'Tidal'],
  tshirt: ['Soft', 'Blank', 'Pure', 'Mist', 'Drift', 'Aura', 'Haze', 'Cloud', 'Dawn', 'Still', 'Zen', 'Calm', 'Glow', 'Hush', 'Echo', 'Tone', 'Form', 'Dew', 'Ebb', 'Flow', 'Pale', 'Bloom', 'Fog', 'Veil', 'Arch', 'Rime', 'Lull', 'Bare'],
  cargo: ['Cloud', 'Drift', 'Mist', 'Haze', 'Aura', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm', 'Glow', 'Hush', 'Echo', 'Tone', 'Form', 'Dew', 'Ebb', 'Flow', 'Pale', 'Veil', 'Arch', 'Rime', 'Lull', 'Bare', 'Tidal'],
  jacket: ['Haze', 'Cloud', 'Mist', 'Drift', 'Aura', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm', 'Glow', 'Hush', 'Echo', 'Tone', 'Form', 'Dew', 'Ebb', 'Flow', 'Pale', 'Veil'],
  shorts: ['Drift', 'Mist', 'Haze', 'Aura', 'Cloud', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm', 'Glow', 'Hush', 'Echo', 'Tone', 'Form', 'Dew', 'Ebb', 'Flow', 'Pale', 'Veil'],
  cap: ['Mist', 'Drift', 'Aura', 'Cloud', 'Haze', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm'],
  bag: ['Mist', 'Drift', 'Aura', 'Cloud', 'Haze', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm'],
  longsleeve: ['Drift', 'Mist', 'Haze', 'Aura', 'Cloud', 'Still', 'Bloom', 'Fog', 'Zen', 'Calm', 'Glow', 'Hush', 'Echo', 'Tone', 'Form']
};

const colors = [
  'Sky Blue', 'Sage Green', 'Peach', 'Lavender', 'Blush', 'Mint', 'Cloud White', 'Stone', 'Butter Yellow', 
  'Dusty Rose', 'Arctic Blue', 'Fern', 'Apricot', 'Lilac', 'Oat', 'Charcoal', 'Navy', 'Olive', 'Terracotta', 
  'Coral', 'Mauve', 'Cream', 'Slate', 'Sand', 'Pistachio', 'Rose', 'Indigo', 'Cinnamon', 'Honey'
];

const tags = ['New', 'Best Seller', 'Limited', 'Sale', 'Drop 01', 'Drop 02', 'Exclusive', 'Staff Pick', 'Low Stock', ''];

const sizeOptions = {
  hoodie: [['S', 'M', 'L', 'XL'], ['M', 'L', 'XL'], ['XS', 'S', 'M', 'L'], ['S', 'M', 'L']],
  tshirt: [['XS', 'S', 'M', 'L', 'XL'], ['S', 'M', 'L'], ['M', 'L', 'XL'], ['XS', 'S', 'M']],
  cargo: [['28', '30', '32', '34'], ['30', '32', '34'], ['28', '30', '32'], ['32', '34', '36']],
  jacket: [['S', 'M', 'L'], ['M', 'L', 'XL'], ['S', 'M'], ['XS', 'S', 'M', 'L']],
  shorts: [['S', 'M', 'L', 'XL'], ['M', 'L'], ['XS', 'S', 'M'], ['S', 'M', 'L']],
  cap: [['One Size'], ['S/M', 'L/XL'], ['One Size'], ['Fitted']],
  bag: [['One Size'], ['Mini', 'Standard'], ['One Size'], ['S', 'M']],
  longsleeve: [['XS', 'S', 'M', 'L'], ['S', 'M', 'L', 'XL'], ['M', 'L'], ['XS', 'S', 'M', 'L', 'XL']]
};

const descriptions = [
  'Thiết kế oversized thoải mái, chất liệu cotton organic 100%. Perfect cho daily wear.',
  'Đường cắt clean, tối giản nhưng đầy character. Dễ mix với mọi outfit.',
  'Limited run — mỗi piece đều được đánh số. Chất liệu premium, form đẹp.',
  'Phong cách streetwear hiện đại, màu pastel nhẹ nhàng nhưng statement.',
  'Relaxed fit, cổ rộng, tay vừa. Mặc được từ sáng đến tối.',
  'Inspired by Japanese workwear, adapted for the streets of Sài Gòn.',
  'Wash gentle, dry flat. Chất liệu giữ form sau nhiều lần giặt.',
  'Phom rộng thoáng mát, thích hợp với thời tiết nhiệt đới.',
  'Collab exclusive với local artist. Mỗi batch chỉ 50 pieces.',
  'Vải dày dặn, không xuyên sáng. Seam đường may chắc chắn.',
  'Form dáng unisex, phù hợp cho cả nam và nữ.',
  'Chất liệu cao cấp, không phai màu sau nhiều lần giặt.',
  'Thiết kế tối giản nhưng tinh tế với logo thêu tay.',
  'Công nghệ in chuyển nhiệt độc quyền, bền màu theo thời gian.',
  'Phối cùng quần jeans hoặc cargo đều đẹp.',
  'Thích hợp mặc hàng ngày hoặc đi chơi, dạo phố.',
  'Được lấy cảm hứng từ văn hóa đường phố Tokyo.',
  'Form dáng rộng, tạo cảm giác thoải mái khi vận động.',
  'Chất vải mềm mịn, thấm hút mồ hôi tốt.',
  'Sản phẩm được kiểm tra chất lượng nghiêm ngặt trước khi xuất xưởng.'
];

const priceRanges = {
  hoodie: [790000, 1290000],
  tshirt: [350000, 590000],
  cargo: [690000, 990000],
  jacket: [990000, 1590000],
  shorts: [450000, 790000],
  cap: [290000, 490000],
  bag: [390000, 790000],
  longsleeve: [490000, 790000]
};

// High quality image URLs from Pexels (diverse streetwear photos)
const imageSets = {
  hoodie: [
    'https://images.pexels.com/photos/1194420/pexels-photo-1194420.jpeg',
    'https://images.pexels.com/photos/428340/pexels-photo-428340.jpeg',
    'https://images.pexels.com/photos/8344815/pexels-photo-8344815.jpeg',
    'https://images.pexels.com/photos/3771692/pexels-photo-3771692.jpeg',
    'https://images.pexels.com/photos/1485035/pexels-photo-1485035.jpeg'
  ],
  tshirt: [
    'https://images.pexels.com/photos/2892342/pexels-photo-2892342.jpeg',
    'https://images.pexels.com/photos/1656684/pexels-photo-1656684.jpeg',
    'https://images.pexels.com/photos/1036622/pexels-photo-1036622.jpeg',
    'https://images.pexels.com/photos/997489/pexels-photo-997489.jpeg',
    'https://images.pexels.com/photos/1055691/pexels-photo-1055691.jpeg'
  ],
  cargo: [
    'https://images.pexels.com/photos/6424251/pexels-photo-6424251.jpeg',
    'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg',
    'https://images.pexels.com/photos/1598508/pexels-photo-1598508.jpeg',
    'https://images.pexels.com/photos/1456705/pexels-photo-1456705.jpeg'
  ],
  jacket: [
    'https://images.pexels.com/photos/1306248/pexels-photo-1306248.jpeg',
    'https://images.pexels.com/photos/997489/pexels-photo-997489.jpeg',
    'https://images.pexels.com/photos/1055691/pexels-photo-1055691.jpeg',
    'https://images.pexels.com/photos/1194420/pexels-photo-1194420.jpeg'
  ],
  shorts: [
    'https://images.pexels.com/photos/3771692/pexels-photo-3771692.jpeg',
    'https://images.pexels.com/photos/1456705/pexels-photo-1456705.jpeg',
    'https://images.pexels.com/photos/1195623/pexels-photo-1195623.jpeg'
  ],
  cap: [
    'https://images.pexels.com/photos/2783873/pexels-photo-2783873.jpeg',
    'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg',
    'https://images.pexels.com/photos/1036622/pexels-photo-1036622.jpeg'
  ],
  bag: [
    'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg',
    'https://images.pexels.com/photos/934658/pexels-photo-934658.jpeg',
    'https://images.pexels.com/photos/1598508/pexels-photo-1598508.jpeg'
  ],
  longsleeve: [
    'https://images.pexels.com/photos/8344815/pexels-photo-8344815.jpeg',
    'https://images.pexels.com/photos/1036622/pexels-photo-1036622.jpeg',
    'https://images.pexels.com/photos/1485035/pexels-photo-1485035.jpeg',
    'https://images.pexels.com/photos/428340/pexels-photo-428340.jpeg'
  ]
};

function getRandomItem(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

function getRandomPrice(category) {
  const [min, max] = priceRanges[category];
  const step = 10000;
  const randomPrice = Math.floor(Math.random() * ((max - min) / step + 1)) * step + min;
  return randomPrice;
}

// Generate 100 products
const products = [];
let productId = 1;

// Helper to get category display name
function getCategoryDisplay(category) {
  const map = {
    hoodie: 'Hoodie',
    tshirt: 'T-Shirt',
    cargo: 'Cargo Pants',
    jacket: 'Jacket',
    shorts: 'Shorts',
    cap: 'Cap',
    bag: 'Bag',
    longsleeve: 'Long Sleeve'
  };
  return map[category];
}

// Generate products for each category
for (const category of Object.keys(productNames)) {
  const names = productNames[category];
  const categoryDisplay = getCategoryDisplay(category);
  
  for (let i = 0; i < names.length; i++) {
    if (products.length >= 100) break;
    
    const name = names[i];
    const fullName = `${name} ${categoryDisplay}`;
    const color = getRandomItem(colors);
    const price = getRandomPrice(category);
    const tag = getRandomItem(tags);
    const sizes = getRandomItem(sizeOptions[category]);
    const desc = getRandomItem(descriptions);
    const image = getRandomItem(imageSets[category]);
    const isNew = Math.random() > 0.7;
    const isBestSeller = Math.random() > 0.85;
    
    let finalTag = tag;
    if (isNew && !finalTag) finalTag = 'New';
    if (isBestSeller && !finalTag) finalTag = 'Best Seller';
    if (isNew && isBestSeller) finalTag = 'New & Best Seller';
    
    products.push({
      id: productId++,
      name: fullName,
      category: category,
      color: color,
      price: price,
      tag: finalTag || '',
      sizes: sizes,
      desc: desc,
      image: image,
      isNew: isNew,
      isBestSeller: isBestSeller
    });
  }
}

// If we need exactly 100, add more from hoodie/tshirt
while (products.length < 100) {
  const category = getRandomItem(['hoodie', 'tshirt', 'cargo']);
  const names = productNames[category];
  const categoryDisplay = getCategoryDisplay(category);
  const name = getRandomItem(names);
  const fullName = `${name} ${categoryDisplay} ${products.length + 1}`;
  
  products.push({
    id: productId++,
    name: fullName,
    category: category,
    color: getRandomItem(colors),
    price: getRandomPrice(category),
    tag: getRandomItem(tags),
    sizes: getRandomItem(sizeOptions[category]),
    desc: getRandomItem(descriptions),
    image: getRandomItem(imageSets[category]),
    isNew: Math.random() > 0.7,
    isBestSeller: Math.random() > 0.85
  });
}

// Shuffle products for variety
for (let i = products.length - 1; i > 0; i--) {
  const j = Math.floor(Math.random() * (i + 1));
  [products[i], products[j]] = [products[j], products[i]];
}

// ── STATE ──
let activeCategory = 'all';
let currentPage = 1;
const PER_PAGE = 12; // Show 12 products per page
let filtered = [...products];

function setCategory(cat, btn) {
  activeCategory = cat;
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  currentPage = 1;
  filterProducts();
}

function filterProducts() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  const sort = document.getElementById('sortSelect').value;
  
  filtered = products.filter(p => {
    const matchCat = activeCategory === 'all' || p.category === activeCategory;
    const matchQ = !q || p.name.toLowerCase().includes(q) || p.color.toLowerCase().includes(q);
    return matchCat && matchQ;
  });
  
  if (sort === 'price-asc') filtered.sort((a, b) => a.price - b.price);
  else if (sort === 'price-desc') filtered.sort((a, b) => b.price - a.price);
  else if (sort === 'name') filtered.sort((a, b) => a.name.localeCompare(b.name));
  
  currentPage = 1;
  renderGrid();
}

function renderGrid() {
  const grid = document.getElementById('productsGrid');
  const start = (currentPage - 1) * PER_PAGE;
  const page = filtered.slice(start, start + PER_PAGE);
  const totalPages = Math.ceil(filtered.length / PER_PAGE);
  document.getElementById('productCount').textContent = `${filtered.length} sản phẩm`;

  if (page.length === 0) {
    grid.innerHTML = '<div class="empty-state"><strong>KHÔNG TÌM THẤY</strong>Thử từ khóa khác nhé.</div>';
    document.getElementById('pagination').innerHTML = '';
    return;
  }

  grid.innerHTML = page.map((p, i) => `
    <div class="product-card fade-up visible" style="transition-delay:${(i % 4) * 0.05}s" onclick="openModal(${p.id})">
      <div class="product-img">
        <img src="${p.image}?w=600&h=800&fit=crop" alt="${p.name}">
        ${p.tag ? `<span class="product-tag-badge">${p.tag}</span>` : ''}
        <button class="wishlist-btn" onclick="event.stopPropagation();addToCartFromCard(${p.id})">♡</button>
      </div>
      <div class="product-info">
        <div class="product-name">${p.name}</div>
        <div class="product-sub">${p.color}</div>
        <div class="product-footer">
          <div class="product-price">${p.price.toLocaleString('vi-VN')} ₫</div>
          <div class="product-sizes">${p.sizes.slice(0, 3).map(s => `<div class="size-dot">${s}</div>`).join('')}</div>
        </div>
      </div>
    </div>
  `).join('');

  renderPagination(totalPages);
}

window.addToCartFromCard = function(id) {
  const product = products.find(p => p.id === id);
  if (product) {
    addToCart(product, product.sizes[0]);
  }
};

function renderPagination(totalPages) {
  if (totalPages <= 1) { 
    document.getElementById('pagination').innerHTML = ''; 
    return; 
  }
  
  let html = `<button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;
  
  // Show first page
  if (currentPage > 3) {
    html += `<button class="page-btn" onclick="goPage(1)">1</button>`;
    if (currentPage > 4) html += `<span style="color:var(--light);line-height:38px;">…</span>`;
  }
  
  // Show pages around current
  for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
  }
  
  // Show last page
  if (currentPage < totalPages - 2) {
    if (currentPage < totalPages - 3) html += `<span style="color:var(--light);line-height:38px;">…</span>`;
    html += `<button class="page-btn" onclick="goPage(${totalPages})">${totalPages}</button>`;
  }
  
  html += `<button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>›</button>`;
  document.getElementById('pagination').innerHTML = html;
}

window.goPage = function(p) {
  if (p < 1 || p > Math.ceil(filtered.length / PER_PAGE)) return;
  currentPage = p;
  renderGrid();
  document.getElementById('collection').scrollIntoView({ behavior: 'smooth', block: 'start' });
};

function capitalize(s) { 
  if (!s) return '';
  return s.charAt(0).toUpperCase() + s.slice(1); 
}

// ── MODAL ──
let currentProduct = null;
let selectedSize = null;

window.openModal = function(id) {
  currentProduct = products.find(p => p.id === id);
  if (!currentProduct) return;
  selectedSize = currentProduct.sizes[0];
  
  const categoryDisplay = getCategoryDisplay(currentProduct.category);
  
  document.getElementById('modalTag').textContent = `${categoryDisplay} — ${currentProduct.tag || 'MIST'}`;
  document.getElementById('modalName').textContent = currentProduct.name;
  document.getElementById('modalSub').textContent = `Màu: ${currentProduct.color}`;
  document.getElementById('modalPrice').textContent = currentProduct.price.toLocaleString('vi-VN') + ' ₫';
  document.getElementById('modalDesc').textContent = currentProduct.desc;
  document.getElementById('modalImg').innerHTML = `<img src="${currentProduct.image}?w=600&h=800&fit=crop" alt="${currentProduct.name}">`;
  document.getElementById('modalSizes').innerHTML = currentProduct.sizes.map(s =>
    `<button class="modal-size-btn ${s === selectedSize ? 'sel' : ''}" onclick="selectSize(this,'${s}')">${s}</button>`
  ).join('');
  document.getElementById('modalMeta').innerHTML = `SKU: MIST-${String(currentProduct.id).padStart(4, '0')} &nbsp;·&nbsp; ${currentProduct.isNew ? 'Hàng mới về' : 'Còn hàng'} &nbsp;·&nbsp; Miễn phí ship`;
  document.getElementById('modalAddBtn').onclick = () => {
    if (selectedSize) {
      addToCart(currentProduct, selectedSize);
      document.getElementById('productModal').classList.remove('open');
    } else {
      showToast('Vui lòng chọn size!');
    }
  };
  document.getElementById('productModal').classList.add('open');
};

function getCategoryDisplay(category) {
  const map = {
    hoodie: 'Hoodie',
    tshirt: 'T-Shirt',
    cargo: 'Cargo Pants',
    jacket: 'Jacket',
    shorts: 'Shorts',
    cap: 'Cap',
    bag: 'Bag',
    longsleeve: 'Long Sleeve'
  };
  return map[category] || category;
}

window.selectSize = function(btn, size) {
  document.querySelectorAll('.modal-size-btn').forEach(b => b.classList.remove('sel'));
  btn.classList.add('sel');
  selectedSize = size;
};

// Modal close
document.querySelector('.modal-close').addEventListener('click', () => {
  document.getElementById('productModal').classList.remove('open');
});

document.getElementById('productModal').addEventListener('click', (e) => {
  if (e.target === document.getElementById('productModal')) {
    document.getElementById('productModal').classList.remove('open');
  }
});

// Event listeners
document.getElementById('searchInput').addEventListener('input', filterProducts);
document.getElementById('sortSelect').addEventListener('change', filterProducts);

document.querySelectorAll('.pill').forEach(btn => {
  btn.addEventListener('click', () => {
    const cat = btn.getAttribute('data-category');
    setCategory(cat, btn);
  });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const target = this.getAttribute('href');
    if (target === '#') return;
    const element = document.querySelector(target);
    if (element) {
      e.preventDefault();
      element.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ── INIT ──
loadCartFromLocal();
renderGrid();

// Log product count
console.log(`✅ Đã tạo ${products.length} sản phẩm thành công!`);
console.log(`📊 Phân bố: Hoodie: ${products.filter(p => p.category === 'hoodie').length}, T-Shirt: ${products.filter(p => p.category === 'tshirt').length}, Cargo: ${products.filter(p => p.category === 'cargo').length}, Jacket: ${products.filter(p => p.category === 'jacket').length}, Shorts: ${products.filter(p => p.category === 'shorts').length}, Cap: ${products.filter(p => p.category === 'cap').length}, Bag: ${products.filter(p => p.category === 'bag').length}, Long Sleeve: ${products.filter(p => p.category === 'longsleeve').length}`);
