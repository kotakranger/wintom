<?php
// gallery.php — Galeri Portofolio Wintom Curtain
require_once __DIR__ . '/includes/db.php';

$page_title  = 'Galeri Portofolio';
$active_nav  = 'gallery';

// --- Query & Filter ---
$db = get_db();


// Seed sample gallery entries if empty
$count = (int)$db->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
if ($count === 0) {
    $samples = [
        ['title' => 'Ripple Fold Linen — Grand Penthouse PIK', 'category' => 'Penthouse Sanctuary', 'image_url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85'],
        ['title' => 'Sheer Voile Living Room — BSD City', 'category' => 'Living Room', 'image_url' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85'],
        ['title' => 'Velour Blackout Master Bedroom — Kebayoran', 'category' => 'Living Room', 'image_url' => 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=1200&q=85'],
        ['title' => 'Motorized Smart Curtain — SCBD Office Tower', 'category' => 'Office & Commercial', 'image_url' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85'],
        ['title' => 'Wood Blind Paulownia — Resort Bali', 'category' => 'Hotel & Private Villa', 'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85'],
        ['title' => 'Double-Track Drape & Sheer — Dining Room Menteng', 'category' => 'Dining & Atelier', 'image_url' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85'],
        ['title' => 'S-Fold Wave — Penthouse Pakubuwono', 'category' => 'Penthouse Sanctuary', 'image_url' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85'],
        ['title' => 'Sunscreen Roller — Co-Working Space Sudirman', 'category' => 'Office & Commercial', 'image_url' => 'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85'],
        ['title' => 'French Linen Drape — Private Villa Seminyak', 'category' => 'Hotel & Private Villa', 'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85'],
        ['title' => 'Motorized Dual-Layer — Atelier Studio Kemang', 'category' => 'Dining & Atelier', 'image_url' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85'],
        ['title' => 'Bamboo Shade — Living Room Pondok Indah', 'category' => 'Living Room', 'image_url' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85'],
        ['title' => 'Penthouse Full-Wall Draping — Ciputra World', 'category' => 'Penthouse Sanctuary', 'image_url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85'],
    ];
    $ins = $db->prepare("INSERT INTO galleries (title, category, image_url) VALUES (?,?,?)");
    foreach ($samples as $s) $ins->execute([$s['title'], $s['category'], $s['image_url']]);
    migrate_gallery_categories($db);   // hubungkan contoh ke kategori produk berdasarkan judul
}

// Filter = kategori produk yang punya proyek portofolio (portofolio menunjukkan hasil nyata produk yang dijual)
$allowed_cats = array_merge(['Semua Proyek'], $db->query(
    "SELECT c.name FROM categories c WHERE EXISTS (SELECT 1 FROM galleries g WHERE g.category_id = c.id)
     ORDER BY c.sort_order ASC, c.name ASC")->fetchAll(PDO::FETCH_COLUMN));
$active_cat   = $_GET['cat'] ?? 'Semua Proyek';
if (!in_array($active_cat, $allowed_cats, true)) $active_cat = 'Semua Proyek';

$gal_sql = "SELECT g.*, COALESCE(c.name, '') AS category FROM galleries g LEFT JOIN categories c ON c.id = g.category_id";
if ($active_cat === 'Semua Proyek') {
    $galleries = $db->query($gal_sql . " ORDER BY g.created_at DESC, g.id DESC")->fetchAll();
} else {
    $stmt = $db->prepare($gal_sql . " WHERE c.name = ? ORDER BY g.created_at DESC, g.id DESC");
    $stmt->execute([$active_cat]);
    $galleries = $stmt->fetchAll();
}

// Foto per proyek: cover (galleries.image_url) + foto tambahan (gallery_images) -> slider
$photos = [];
foreach ($galleries as $g) $photos[$g['id']] = [$g['image_url']];
if ($galleries) {
    $ids = implode(',', array_map('intval', array_keys($photos)));
    foreach ($db->query("SELECT gallery_id, image_url FROM gallery_images WHERE gallery_id IN ($ids) ORDER BY sort_order, id") as $row) {
        $photos[$row['gallery_id']][] = $row['image_url'];
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== PAGE HERO ===== -->
<section class="bg-cream-dark border-b border-border pt-12 pb-12 md:pt-[64px] md:pb-[60px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">
    <p class="wt-in font-jakarta font-semibold text-brand text-[12px] tracking-[2.64px] uppercase leading-[16.5px] mb-4" style="--d:60ms">PORTOFOLIO PROYEK</p>
    <h1 class="wt-in font-playfair font-bold text-dark text-[32px] sm:text-[40px] md:text-[48px] leading-[1.15] tracking-[-1px] max-w-[760px] mb-5" style="--d:160ms">
      Sentuhan Elegan di Setiap Sudut Ruangan
    </h1>
    <p class="wt-in font-jakarta text-muted text-[16px] md:text-[17px] leading-[1.6] max-w-[680px] mb-8" style="--d:260ms">
      Jelajahi portofolio hasil pemasangan gorden kustom Wintom serta dokumentasi video proyek kami. Dari pemilihan bahan berkualitas hingga instalasi yang presisi untuk menyempurnakan estetika ruang Anda.
    </p>
    <div class="wt-in flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 sm:gap-4" style="--d:360ms">
      <button type="button" data-open-quote
         class="inline-flex justify-center bg-brand text-white font-jakarta font-semibold text-[14px] px-8 py-[14px] rounded-[3px] hover:bg-[#6a041c] transition-colors duration-200">Konsultasi Gratis</button>
      <a href="#galeri"
         class="inline-flex justify-center border border-dark text-dark font-jakarta font-semibold text-[14px] px-8 py-[13px] rounded-[3px] hover:bg-dark hover:text-white transition-colors duration-200">Lihat Proyek Kami</a>
    </div>
  </div>
</section>

<!-- ===== CATEGORY FILTER TABS ===== -->
<section class="bg-cream border-b border-border sticky top-[80px] z-40">
  <div class="max-w-[1280px] mx-auto">
    <div class="flex items-center gap-0 overflow-x-auto no-scrollbar px-2 sm:px-6 lg:px-12">
      <?php foreach ($allowed_cats as $cat):
        $is_active = ($cat === $active_cat);
        $href = '?cat=' . urlencode($cat);
      ?>
      <a href="<?= $href ?>"
         class="shrink-0 font-jakarta font-semibold text-[11px] tracking-[1.65px] uppercase whitespace-nowrap px-4 sm:px-5 py-4 sm:py-[18px] border-b-2 transition-all duration-200
                <?= $is_active ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-dark hover:border-muted' ?>">
        <?= htmlspecialchars($cat) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== GALLERY GRID ===== -->
<section id="galeri" class="py-10 md:py-[64px] bg-cream scroll-mt-[140px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12">

    <?php if (empty($galleries)): ?>
    <div class="flex flex-col items-center justify-center py-[80px] text-center">
      <p class="font-playfair text-dark text-[22px] mb-2">Belum ada portofolio</p>
      <p class="font-jakarta text-muted text-[14px]">Portofolio untuk kategori ini sedang disiapkan.</p>
    </div>

    <?php else: ?>
    <div class="columns-2 lg:columns-3 gap-3 sm:gap-5" id="gallery-grid">
      <?php foreach ($galleries as $gi => $item):
          $imgs = array_map('asset_url', $photos[$item['id']]);
          $multi = count($imgs) > 1;
      ?>
      <figure data-reveal style="--d:<?= ($gi % 3) * 80 ?>ms"
              class="g-card break-inside-avoid mb-3 sm:mb-5 group relative overflow-hidden rounded-[4px] bg-border m-0"
              data-title="<?= htmlspecialchars($item['title']) ?>" data-cat="<?= htmlspecialchars($item['category']) ?>"
              data-images="<?= htmlspecialchars(json_encode($imgs, JSON_UNESCAPED_SLASHES)) ?>">
        <!-- Slider: foto pertama menentukan tinggi kartu, foto lain mengisi ruang yang sama -->
        <div class="g-track flex overflow-x-auto snap-x snap-mandatory no-scrollbar cursor-zoom-in" tabindex="-1">
          <?php foreach ($imgs as $k => $src): ?>
          <div class="relative shrink-0 w-full snap-start">
            <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($item['title']) . ($multi ? ' — foto ' . ($k + 1) : '') ?>" loading="lazy" draggable="false"
                 class="<?= $k === 0 ? 'block w-full' : 'absolute inset-0 w-full h-full' ?> object-cover transition-transform duration-700 group-hover:scale-[1.03]" />
          </div>
          <?php endforeach; ?>
        </div>

        <figcaption class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[rgba(34,34,32,0.80)] via-[rgba(34,34,32,0.15)] to-transparent opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-3 sm:p-5">
          <span class="font-jakarta font-semibold text-white/60 text-[9px] tracking-[2px] uppercase mb-1"><?= htmlspecialchars($item['category']) ?></span>
          <span class="font-playfair text-white text-[13px] sm:text-[16px] leading-[1.35]"><?= htmlspecialchars($item['title']) ?></span>
        </figcaption>

        <?php if ($multi): ?>
        <span class="g-count pointer-events-none absolute top-2.5 right-2.5 bg-black/55 backdrop-blur-[2px] text-white font-jakarta text-[10px] tracking-[0.5px] px-2 py-0.5 rounded-full">1/<?= count($imgs) ?></span>
        <button type="button" data-g-prev aria-label="Foto sebelumnya"
                class="hidden lg:flex absolute left-2.5 top-1/2 -translate-y-1/2 size-9 items-center justify-center rounded-full bg-white/90 text-dark shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <button type="button" data-g-next aria-label="Foto berikutnya"
                class="hidden lg:flex absolute right-2.5 top-1/2 -translate-y-1/2 size-9 items-center justify-center rounded-full bg-white/90 text-dark shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>

    <p class="text-center font-jakarta text-muted text-[12px] tracking-[1.5px] uppercase mt-10">
      Menampilkan <?= count($galleries) ?> proyek<?= $active_cat !== 'Semua Proyek' ? ' dalam kategori ' . htmlspecialchars($active_cat) : '' ?>
    </p>
    <?php endif; ?>
  </div>
</section>

<!-- ===== LIGHTBOX (slider) ===== -->
<div id="lightbox" class="fixed inset-0 z-[9999] bg-black/90 hidden flex-col items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="lightbox-title">
  <button type="button" data-lb-close aria-label="Tutup"
          class="absolute top-3 right-3 sm:top-5 sm:right-5 z-10 size-11 flex items-center justify-center rounded-full text-white/70 hover:text-white hover:bg-white/10 transition-colors">
    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
  </button>
  <div class="relative w-full max-w-[1200px] px-0 sm:px-16">
    <div id="lb-track" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar"></div>
    <button type="button" data-lb-prev aria-label="Foto sebelumnya"
            class="hidden sm:flex absolute left-2 top-1/2 -translate-y-1/2 size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <button type="button" data-lb-next aria-label="Foto berikutnya"
            class="hidden sm:flex absolute right-2 top-1/2 -translate-y-1/2 size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
      <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>
  </div>
  <div class="mt-4 px-4 text-center">
    <p id="lightbox-cat" class="font-jakarta font-semibold text-white/50 text-[10px] tracking-[2px] uppercase mb-1"></p>
    <p id="lightbox-title" class="font-playfair text-white text-[16px] sm:text-[18px] m-0"></p>
    <p id="lightbox-count" class="font-jakarta text-white/50 text-[12px] mt-2 m-0"></p>
  </div>
</div>

<!-- ===== RESERVATION CTA BANNER ===== -->
<section class="bg-[#860524] py-14 md:py-[72px]">
  <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-12 flex flex-col lg:flex-row lg:items-center justify-between gap-8 lg:gap-10">
    <div data-reveal>
      <p class="font-jakarta font-semibold text-white/60 text-[11px] tracking-[2.64px] uppercase mb-3">MULAI PROYEK ANDA</p>
      <h2 class="font-playfair font-bold text-white text-[28px] sm:text-[38px] leading-[1.2] tracking-[-0.8px] max-w-[480px]">
        Jadwalkan Survei &amp; Pengukuran Gratis ke Lokasi Anda
      </h2>
    </div>
    <div class="flex flex-col items-stretch sm:items-start lg:items-end gap-4 shrink-0" data-reveal style="--d:120ms">
      <p class="font-jakarta text-white/70 text-[14px] leading-[22px] max-w-[300px] lg:text-right">
        Tim konsultan kami siap datang ke lokasi untuk survei, membawa swatch kain fisik, dan memberikan estimasi biaya transparan tanpa biaya apapun.
      </p>
      <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+jadwalkan+survei+gratis+untuk+proyek+saya"
         target="_blank" rel="noopener"
         class="inline-flex items-center justify-center gap-3 bg-white text-brand font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[14px] rounded-[2px] hover:bg-cream transition-colors duration-200">
        JADWALKAN SURVEI GRATIS
      </a>
    </div>
  </div>
</section>

<script>
(function () {
  // ---- Slider di kartu: panah (desktop, saat hover), swipe/scroll (mobile & trackpad) ----
  const slideTo = function (track, dir) { track.scrollBy({ left: dir * track.clientWidth, behavior: 'smooth' }); };
  document.querySelectorAll('.g-card').forEach(function (card) {
    const track = card.querySelector('.g-track');
    const count = card.querySelector('.g-count');
    const total = track.children.length;
    const index = function () { return Math.round(track.scrollLeft / Math.max(1, track.clientWidth)); };
    if (count) track.addEventListener('scroll', function () { count.textContent = (index() + 1) + '/' + total; }, { passive: true });
    card.querySelector('[data-g-prev]')?.addEventListener('click', function (e) { e.stopPropagation(); slideTo(track, -1); });
    card.querySelector('[data-g-next]')?.addEventListener('click', function (e) { e.stopPropagation(); slideTo(track, 1); });
    track.addEventListener('click', function () {
      openLightbox(JSON.parse(card.dataset.images), index(), card.dataset.title, card.dataset.cat);
    });
  });

  // ---- Lightbox slider ----
  const lb = document.getElementById('lightbox');
  const lbTrack = document.getElementById('lb-track');
  const lbCount = document.getElementById('lightbox-count');
  const lbPrev = lb.querySelector('[data-lb-prev]'), lbNext = lb.querySelector('[data-lb-next]');
  let lbTotal = 0, lastFocus = null;
  const lbIndex = function () { return Math.round(lbTrack.scrollLeft / Math.max(1, lbTrack.clientWidth)); };
  const lbUpdate = function () {
    const i = lbIndex();
    lbCount.textContent = lbTotal > 1 ? (i + 1) + ' / ' + lbTotal : '';
    lbPrev.style.visibility = lbTotal > 1 && i > 0 ? 'visible' : 'hidden';
    lbNext.style.visibility = lbTotal > 1 && i < lbTotal - 1 ? 'visible' : 'hidden';
  };

  window.openLightbox = function (images, start, title, cat) {
    lastFocus = document.activeElement;
    lbTotal = images.length;
    lbTrack.replaceChildren.apply(lbTrack, images.map(function (src, k) {
      const slide = document.createElement('div');
      slide.className = 'shrink-0 w-full snap-center flex items-center justify-center h-[70vh] sm:h-[76vh] px-3 sm:px-0';
      const img = document.createElement('img');
      img.src = src;
      img.alt = title + (images.length > 1 ? ' — foto ' + (k + 1) : '');
      img.className = 'max-w-full max-h-full object-contain rounded-[3px] shadow-2xl select-none';
      img.draggable = false;
      slide.appendChild(img);
      return slide;
    }));
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-cat').textContent = cat;
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.documentElement.style.overflow = 'hidden';
    lbTrack.scrollLeft = (start || 0) * lbTrack.clientWidth;
    lbUpdate();
    lb.querySelector('[data-lb-close]').focus({ preventScroll: true });
  };
  window.closeLightbox = function () {
    if (lb.classList.contains('hidden')) return;
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.documentElement.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
  };

  lbTrack.addEventListener('scroll', lbUpdate, { passive: true });
  lbPrev.addEventListener('click', function () { slideTo(lbTrack, -1); });
  lbNext.addEventListener('click', function () { slideTo(lbTrack, 1); });
  lb.querySelector('[data-lb-close]').addEventListener('click', closeLightbox);
  lb.addEventListener('click', function (e) {   // klik area gelap di luar foto = tutup
    if (e.target === lb || (e.target.parentElement === lbTrack)) closeLightbox();
  });
  document.addEventListener('keydown', function (e) {
    if (lb.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    else if (e.key === 'ArrowLeft') slideTo(lbTrack, -1);
    else if (e.key === 'ArrowRight') slideTo(lbTrack, 1);
  });
})();
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
