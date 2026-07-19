/* =========================================================
   НОРМАПЛЮС — script.js
   ========================================================= */

// =========================================================
// 1. PRODUCT DATA
// =========================================================
const PRODUCTS = {
  large: {
    title:   'Для крупных и средних пород',
    granule: '1.5 см',
    img:     'images/bag-large.png',
    packs: [
      { kg: 1,    label: '1 кг',    type: 'Дой-пак ZIP', img: 'images/pack-1kg.png',   price: 550,   badge: null        },
      { kg: 2.5,  label: '2.5 кг', type: 'Дой-пак ZIP', img: 'images/pack-2-5kg.png', price: 1375,  badge: null        },
      { kg: 25,   label: '25 кг',  type: 'Мешок',       img: 'images/pack-25kg.png',  price: 12500, badge: '−9%'       }
    ]
  },
  small: {
    title:   'Для средних и мелких пород',
    granule: '0.5 см',
    img:     'images/bag-small.png',
    packs: [
      { kg: 1,    label: '1 кг',   type: 'Дой-пак ZIP', img: 'images/pack-1kg.png',   price: 550,  badge: null },
      { kg: 2.5,  label: '2.5 кг', type: 'Дой-пак ZIP', img: 'images/pack-2-5kg.png', price: 1375, badge: null }
    ]
  }
};

const PACK_IMAGES = {
  1:    'images/pack-1kg.png',
  2.5:  'images/pack-2-5kg.png',
  25:   'images/pack-25kg.png'
};

let currentBreed = 'large';
let selectedPackIdx = 0;

// =========================================================
// 2. PRODUCT SECTION — RENDER
// =========================================================
function updateProductImage() {
  const img = document.getElementById('productImage');
  if (!img) return;
  const p    = PRODUCTS[currentBreed];
  const pack = p.packs[selectedPackIdx] || p.packs[0];
  img.src = PACK_IMAGES[pack.kg] || p.img;
}

function renderProduct () {
  const p    = PRODUCTS[currentBreed];
  const pack = p.packs[selectedPackIdx] || p.packs[0];

  /* labels */
  const lblLarge = document.getElementById('label-large');
  const lblSmall = document.getElementById('label-small');
  if (lblLarge) { lblLarge.classList.toggle('active', currentBreed === 'large'); }
  if (lblSmall) { lblSmall.classList.toggle('active', currentBreed === 'small'); }

  /* product image */
  updateProductImage();

  /* title */
  const title = document.getElementById('productTitle');
  if (title) { title.textContent = p.title; }

  /* granule */
  const gran = document.getElementById('granuleSize');
  if (gran) { gran.textContent = p.granule; }

  /* pack grid */
  renderPackGrid(p);

  /* price */
  updateProductPrice(pack);
}

function renderPackGrid (p) {
  const grid = document.getElementById('packGrid');
  if (!grid) return;

  // 25kg only exists for large breed — adjust cols
  grid.className = 'pack-grid' + (p.packs.length === 2 ? ' cols-2' : '');
  grid.innerHTML = '';

  p.packs.forEach((pack, i) => {
    const card = document.createElement('div');
    card.className = 'pack-card' + (i === selectedPackIdx ? ' active' : '');
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');
    card.setAttribute('aria-pressed', i === selectedPackIdx ? 'true' : 'false');
    card.setAttribute('aria-label', pack.label + ' — ' + formatPrice(pack.price));

    card.innerHTML = `
      ${pack.badge ? `<div class="pack-badge">${pack.badge}</div>` : ''}
      <img src="${pack.img}" alt="${pack.label}" loading="lazy">
      <div class="pack-weight">${pack.label}</div>
      <div class="pack-price">${formatPrice(pack.price)}</div>
      <div class="pack-type">${pack.type}</div>
    `;

    const select = (idx) => {
      if (selectedPackIdx === idx) return;
      selectedPackIdx = idx;
      updateProductImage();
      renderProduct();
    };

    card.addEventListener('click',    () => select(i));
    card.addEventListener('keydown',  (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select(i); } });
    grid.appendChild(card);
  });
}

function updateProductPrice (pack) {
  const priceEl = document.getElementById('productPrice');
  const lineEl  = document.getElementById('packPriceLine');
  if (!pack) return;

  const perKg = Math.round(pack.price / pack.kg);

  if (priceEl) {
    priceEl.textContent = formatPrice(pack.price) + '\u00a0₽';
  }
  if (lineEl) {
    const isDiscount = pack.kg >= 15; // 25kg → wholesale
    lineEl.innerHTML = `${formatPrice(perKg)}\u00a0₽/кг${isDiscount ? ' <span class="discount-tag">оптовая цена</span>' : ''}`;
  }
}

// =========================================================
// 3. BREED TOGGLE
// =========================================================
const toggleEl = document.getElementById('breedToggle');
if (toggleEl) {
  toggleEl.addEventListener('change', () => {
    currentBreed    = toggleEl.checked ? 'small' : 'large';
    selectedPackIdx = 0;
    const img = document.getElementById('productImage');
    if (img) img.src = PRODUCTS[currentBreed].img;
    renderProduct();
  });
}

/* clicking label toggles the switch */
['label-large', 'label-small'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.cursor = 'pointer';
  el.addEventListener('click', () => {
    const wantSmall = (id === 'label-small');
    if (toggleEl) toggleEl.checked = wantSmall;
    currentBreed    = wantSmall ? 'small' : 'large';
    selectedPackIdx = 0;
    const img = document.getElementById('productImage');
    if (img) img.src = PRODUCTS[currentBreed].img;
    renderProduct();
  });
});

// =========================================================
// 5. ORDER FORM — dynamic pack options + price calculator
// =========================================================
const packOptions = {
  large: [
    { value: '1|550',      label: '1 кг — Дой-пак ZIP · 550 ₽'           },
    { value: '2.5|1375',   label: '2,5 кг — Дой-пак ZIP · 1 375 ₽'      },
    { value: '25|12500',   label: '25 кг — Мешок · 12 500 ₽ (−9%)'      }
  ],
  small: [
    { value: '1|550',      label: '1 кг — Дой-пак ZIP · 550 ₽'      },
    { value: '2.5|1375',   label: '2,5 кг — Дой-пак ZIP · 1 375 ₽'  }
  ]
};

const formBreed = document.getElementById('formBreed');
const formPack  = document.getElementById('formPack');
const formQty   = document.getElementById('formQty');
const totalEl   = document.getElementById('orderTotal');

function rebuildPackSelect (breed) {
  if (!formPack) return;
  formPack.innerHTML = '';
  const opts = packOptions[breed] || packOptions.large;
  opts.forEach(opt => {
    const o = new Option(opt.label, opt.value);
    formPack.appendChild(o);
  });
  calcOrderTotal();
}

function calcOrderTotal () {
  if (!formPack || !formQty || !totalEl) return;
  const [kgStr, priceStr] = (formPack.value || '1|550').split('|');
  const kg      = parseFloat(kgStr)   || 1;
  const pkgPrice = parseInt(priceStr) || 550;
  const qty     = Math.max(1, parseInt(formQty.value) || 1);
  const total   = pkgPrice * qty;
  const totalKg = kg * qty;

  totalEl.textContent = formatPrice(total) + '\u00a0₽';

  /* green hint when wholesale threshold reached */
  if (totalKg >= 15) {
    totalEl.style.color = 'var(--green-2, #6fd391)';
    totalEl.title = 'Оптовая цена применена — от 15 кг выгоднее!';
  } else {
    totalEl.style.color = '';
    totalEl.title = '';
  }
}

if (formBreed) {
  formBreed.addEventListener('change', () => {
    rebuildPackSelect(formBreed.value || 'large');
  });
  /* init with first valid breed */
  rebuildPackSelect(formBreed.value || 'large');
}

if (formPack) formPack.addEventListener('change', calcOrderTotal);
if (formQty)  { formQty.addEventListener('input', calcOrderTotal); formQty.addEventListener('change', calcOrderTotal); }

const goToOrderBtn = document.getElementById('goToOrderBtn');
if (goToOrderBtn && formBreed && formPack) {
  goToOrderBtn.addEventListener('click', () => {
    /* подставляем выбранную породу и фасовку из блока "Подберите корм" */
    formBreed.value = currentBreed;
    rebuildPackSelect(currentBreed);

    const chosenPack = PRODUCTS[currentBreed].packs[selectedPackIdx] || PRODUCTS[currentBreed].packs[0];
    const matchOpt = Array.from(formPack.options).find(o => parseFloat(o.value.split('|')[0]) === chosenPack.kg);
    if (matchOpt) formPack.value = matchOpt.value;

    calcOrderTotal();
  });
}

// =========================================================
// 6. ORDER FORM — submit (sends to korm@normaplus.ru via formsubmit.co)
// =========================================================
const orderForm      = document.getElementById('orderForm');
const successMsg     = document.getElementById('successMessage');
const formTotalHidden = document.getElementById('formTotalHidden');

if (orderForm && successMsg) {
  orderForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    /* защита от спама — слайдер должен быть перетащен до конца */
    if (typeof captchaVerified !== 'undefined' && !captchaVerified) {
      return;
    }

    /* simple required-field check */
    const name  = document.getElementById('formName');
    const phone = document.getElementById('formPhone');
    if (name && !name.value.trim()) { name.focus(); return; }
    if (phone && !phone.value.trim()) { phone.focus(); return; }

    /* carry the computed total along in the email */
    if (formTotalHidden && totalEl) {
      formTotalHidden.value = totalEl.textContent.trim();
    }

    const submitBtn = orderForm.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Отправляем…'; }

    try {
      const formData = new FormData(orderForm);
      const res = await fetch(orderForm.action, {
        method: 'POST',
        body: formData,
        headers: { Accept: 'application/json' }
      });

      const data = await res.json().catch(() => null);
      if (!res.ok || !data || data.ok !== true) throw new Error('send failed');

      orderForm.style.display  = 'none';
      successMsg.style.display = 'flex';
      successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } catch (err) {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Отправить заявку'; }
      alert('Не удалось отправить заявку. Проверьте соединение и попробуйте ещё раз, либо позвоните нам напрямую.');
    }
  });
}

// =========================================================
// 7. SCROLL-TO-TOP BUTTON
// =========================================================
const topBtn = document.getElementById('topBtn');

if (topBtn) {
  window.addEventListener('scroll', () => {
    topBtn.style.display = window.scrollY > 600 ? 'flex' : 'none';
  }, { passive: true });

  topBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// =========================================================
// 8. HEADER SHRINK ON SCROLL
// =========================================================
const header = document.getElementById('header') || document.querySelector('.header');
if (header) {
  window.addEventListener('scroll', () => {
    header.classList.toggle('header--compact', window.scrollY > 80);
  }, { passive: true });
}

// =========================================================
// 9. SMOOTH ENTRANCE ANIMATION (Intersection Observer)
// =========================================================
if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.card, .section-head, .product-showcase').forEach(el => {
    el.classList.add('fade-in-up');
    observer.observe(el);
  });
}

// =========================================================
// 10. DOCUMENTS / CERTIFICATES — auto-loaded from documents/manifest.json
// =========================================================
async function renderDocuments () {
  const grid = document.getElementById('certGrid');
  const emptyNote = document.getElementById('certEmptyNote');
  if (!grid) return;

  try {
    const res = await fetch('documents-list.php', { cache: 'no-store' });
    if (!res.ok) throw new Error('manifest not found');
    const items = await res.json();

    if (!Array.isArray(items) || items.length === 0) {
      grid.innerHTML = '';
      if (emptyNote) emptyNote.style.display = 'block';
      return;
    }

    if (emptyNote) emptyNote.style.display = 'none';

    grid.innerHTML = items.map((doc) => {
      const href = 'documents/' + encodeURIComponent(doc.file);
      const isImage = /^(JPG|JPEG|PNG|WEBP)$/i.test(doc.type);
      const preview = isImage
        ? `<img src="${href}" alt="${doc.title}" loading="lazy">`
        : `<div class="cert-card-icon">${doc.type}</div>`;

      return `
        <a class="card cert-card" href="${href}" target="_blank" rel="noopener">
          ${preview}
          <div class="cert-caption"><span>${doc.title}</span><span>${doc.type}</span></div>
        </a>
      `;
    }).join('');
  } catch (err) {
    grid.innerHTML = '';
    if (emptyNote) emptyNote.style.display = 'block';
  }
}

renderDocuments();

// =========================================================
// 11. MOBILE BURGER MENU
// =========================================================
const burgerBtn   = document.getElementById('burgerBtn');
const mobileNav   = document.getElementById('mobileNavOverlay');

function closeMobileNav () {
  if (!burgerBtn || !mobileNav) return;
  mobileNav.classList.remove('is-open');
  burgerBtn.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('nav-open');
}

if (burgerBtn && mobileNav) {
  burgerBtn.addEventListener('click', () => {
    const isOpen = mobileNav.classList.toggle('is-open');
    burgerBtn.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('nav-open', isOpen);
  });

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMobileNav);
  });
}

// =========================================================
// 12. SLIDER CAPTCHA (drag the dog to confirm you're human)
// =========================================================
const sliderCaptcha = document.getElementById('sliderCaptcha');
const sliderTrack    = document.getElementById('sliderTrack');
const sliderFill     = document.getElementById('sliderFill');
const sliderHandle   = document.getElementById('sliderHandle');
const sliderLabel    = document.getElementById('sliderLabel');
const submitOrderBtn = document.getElementById('submitOrderBtn');

let captchaVerified = false;

if (sliderCaptcha && sliderTrack && sliderHandle && submitOrderBtn) {
  let dragging = false;
  const handleSize = 46;
  const edgePad = 3;

  function maxTravel () {
    return sliderTrack.clientWidth - handleSize - edgePad * 2;
  }

  function setHandlePos (x) {
    const max = maxTravel();
    const clamped = Math.max(0, Math.min(x, max));
    sliderHandle.style.left = (clamped + edgePad) + 'px';
    sliderFill.style.width  = (clamped + handleSize / 2 + edgePad) + 'px';
    return clamped >= max - 2;
  }

  function completeCaptcha () {
    captchaVerified = true;
    sliderCaptcha.classList.add('is-complete');
    sliderLabel.textContent = 'Готово ✓ 100%';
    sliderHandle.textContent = '✓';
    submitOrderBtn.disabled = false;
  }

  function resetCaptcha () {
    captchaVerified = false;
    sliderCaptcha.classList.remove('is-complete');
    sliderLabel.textContent = 'Перетащите собачку, чтобы подтвердить →';
    sliderHandle.textContent = '🐶';
    sliderHandle.style.left = edgePad + 'px';
    sliderFill.style.width  = '0';
    submitOrderBtn.disabled = true;
  }

  function pointerX (e) {
    const rect = sliderTrack.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    return clientX - rect.left - handleSize / 2;
  }

  function onDragStart (e) {
    if (captchaVerified) return;
    dragging = true;
    e.preventDefault();
  }

  function onDragMove (e) {
    if (!dragging || captchaVerified) return;
    const done = setHandlePos(pointerX(e));
    if (done) {
      dragging = false;
      completeCaptcha();
    }
  }

  function onDragEnd () {
    if (!dragging) return;
    dragging = false;
    if (!captchaVerified) resetCaptcha();
  }

  sliderHandle.addEventListener('mousedown', onDragStart);
  sliderHandle.addEventListener('touchstart', onDragStart, { passive: false });
  window.addEventListener('mousemove', onDragMove);
  window.addEventListener('touchmove', onDragMove, { passive: false });
  window.addEventListener('mouseup', onDragEnd);
  window.addEventListener('touchend', onDragEnd);

  resetCaptcha();
}

// =========================================================
// 13. "ЗАКАЗАТЬ ЕЩЁ" — reload the page like F5
// =========================================================
const orderAgainBtn = document.getElementById('orderAgainBtn');
if (orderAgainBtn) {
  orderAgainBtn.addEventListener('click', () => {
    window.location.reload();
  });
}

// =========================================================
// HELPERS
// =========================================================
function formatPrice (n) {
  return n.toLocaleString('ru-RU');
}

// =========================================================
// INIT
// =========================================================
renderProduct();
calcOrderTotal();
