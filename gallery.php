<?php
// gallery.php — Galeri Portofolio Wintom Curtain
require_once __DIR__ . '/includes/db.php';

$page_title  = 'Galeri Portofolio';
$active_nav  = 'gallery';

// --- Query & Filter ---
$allowed_cats = ['Semua Proyek', 'Living Room', 'Penthouse Sanctuary', 'Dining & Atelier', 'Office & Commercial', 'Hotel & Private Villa'];
$active_cat   = $_GET['cat'] ?? 'Semua Proyek';
if (!in_array($active_cat, $allowed_cats)) $active_cat = 'Semua Proyek';

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
}

if ($active_cat === 'Semua Proyek') {
    $galleries = $db->query("SELECT * FROM galleries ORDER BY created_at DESC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM galleries WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$active_cat]);
    $galleries = $stmt->fetchAll();
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- ===== PAGE HERO ===== -->
<section class="bg-cream-dark border-b border-border pt-[64px] pb-[60px]">
  <div class="max-w-[1280px] mx-auto px-12">
    <p class="font-jakarta font-semibold text-brand text-[11px] tracking-[2.64px] uppercase leading-[16.5px] mb-4">PORTFOLIO GALLERY</p>
    <h1 class="font-playfair font-bold text-dark text-[48px] leading-[56px] tracking-[-1px] max-w-[640px] mb-4">
      Karya Kami, Cerita<br>Setiap Ruang
    </h1>
    <p class="font-jakarta text-muted text-[15px] leading-[24px] max-w-[520px]">
      Setiap instalasi adalah komitmen presisi — dari pengukuran, penjahitan atelier, hingga pemasangan rapi di lokasi Anda.
    </p>
  </div>
</section>

<!-- ===== CATEGORY FILTER TABS ===== -->
<section class="bg-cream border-b border-border sticky top-[80px] z-40">
  <div class="max-w-[1280px] mx-auto px-12">
    <div class="flex items-center gap-0 overflow-x-auto">
      <?php foreach ($allowed_cats as $cat):
        $is_active = ($cat === $active_cat);
        $href = '?cat=' . urlencode($cat);
      ?>
      <a href="<?= $href ?>"
         class="shrink-0 font-jakarta font-semibold text-[11px] tracking-[1.65px] uppercase whitespace-nowrap px-5 py-[18px] border-b-2 transition-all duration-200
                <?= $is_active ? 'border-brand text-brand' : 'border-transparent text-muted hover:text-dark hover:border-muted' ?>">
        <?= htmlspecialchars($cat) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== GALLERY GRID ===== -->
<section class="py-[64px] bg-cream">
  <div class="max-w-[1280px] mx-auto px-12">

    <?php if (empty($galleries)): ?>
    <div class="flex flex-col items-center justify-center py-[80px] text-center">
      <p class="font-playfair text-dark text-[22px] mb-2">Belum ada portofolio</p>
      <p class="font-jakarta text-muted text-[14px]">Portofolio untuk kategori ini sedang disiapkan.</p>
    </div>

    <?php else: ?>
    <div class="columns-1 sm:columns-2 lg:columns-3 gap-5" id="gallery-grid">
      <?php foreach ($galleries as $item): ?>
      <div class="break-inside-avoid mb-5 group relative overflow-hidden rounded-[4px] bg-border cursor-pointer"
           onclick="openLightbox('<?= htmlspecialchars($item['image_url']) ?>', '<?= htmlspecialchars(addslashes($item['title'])) ?>', '<?= htmlspecialchars($item['category']) ?>')">
        <img src="<?= htmlspecialchars($item['image_url']) ?>"
             alt="<?= htmlspecialchars($item['title']) ?>"
             class="w-full object-cover transition-transform duration-700 group-hover:scale-105"
             loading="lazy" />
        <div class="absolute inset-0 bg-gradient-to-t from-[rgba(34,34,32,0.80)] via-[rgba(34,34,32,0.15)] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex flex-col justify-end p-5">
          <span class="font-jakarta font-semibold text-white/60 text-[9px] tracking-[2px] uppercase mb-1"><?= htmlspecialchars($item['category']) ?></span>
          <p class="font-playfair text-white text-[16px] leading-[22px]"><?= htmlspecialchars($item['title']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="text-center font-jakarta text-muted text-[12px] tracking-[1.5px] uppercase mt-10">
      Menampilkan <?= count($galleries) ?> karya<?= $active_cat !== 'Semua Proyek' ? ' dalam kategori ' . htmlspecialchars($active_cat) : '' ?>
    </p>
    <?php endif; ?>
  </div>
</section>

<!-- ===== LIGHTBOX MODAL ===== -->
<div id="lightbox" class="fixed inset-0 z-[9999] bg-black/90 hidden items-center justify-center p-4" onclick="closeLightboxBg(event)">
  <div class="relative max-w-[90vw] max-h-[90vh] flex flex-col items-center">
    <button onclick="closeLightbox()" class="absolute -top-10 right-0 text-white/60 hover:text-white text-[28px] font-light leading-none transition-colors">&times;</button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[80vh] object-contain rounded-[3px] shadow-2xl" />
    <div class="mt-4 text-center">
      <p id="lightbox-cat" class="font-jakarta font-semibold text-white/50 text-[10px] tracking-[2px] uppercase mb-1"></p>
      <p id="lightbox-title" class="font-playfair text-white text-[18px]"></p>
    </div>
  </div>
</div>

<!-- ===== RESERVATION CTA BANNER ===== -->
<section class="bg-[#741122] py-[72px]">
  <div class="max-w-[1280px] mx-auto px-12 flex flex-col lg:flex-row items-center justify-between gap-10">
    <div>
      <p class="font-jakarta font-semibold text-white/60 text-[11px] tracking-[2.64px] uppercase mb-3">MULAI PROYEK ANDA</p>
      <h2 class="font-playfair font-bold text-white text-[38px] leading-[46px] tracking-[-0.8px] max-w-[480px]">
        Jadwalkan Survei &amp; Pengukuran Gratis ke Lokasi Anda
      </h2>
    </div>
    <div class="flex flex-col items-start lg:items-end gap-4 shrink-0">
      <p class="font-jakarta text-white/70 text-[14px] leading-[22px] max-w-[300px] lg:text-right">
        Tim konsultan kami siap datang ke lokasi untuk survei, membawa swatch kain fisik, dan memberikan estimasi biaya transparan tanpa biaya apapun.
      </p>
      <a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+jadwalkan+survei+gratis+untuk+proyek+saya"
         target="_blank" rel="noopener"
         class="inline-flex items-center gap-3 bg-white text-brand font-semibold text-[12px] tracking-[1.8px] uppercase px-7 py-[14px] rounded-[2px] hover:bg-cream transition-colors duration-200">
        JADWALKAN SURVEI GRATIS
      </a>
    </div>
  </div>
</section>

<script>
function openLightbox(src, title, cat) {
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox-title').textContent = title;
  document.getElementById('lightbox-cat').textContent = cat;
  const lb = document.getElementById('lightbox');
  lb.classList.remove('hidden');
  lb.classList.add('flex');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  const lb = document.getElementById('lightbox');
  lb.classList.add('hidden');
  lb.classList.remove('flex');
  document.body.style.overflow = '';
}
function closeLightboxBg(e) {
  if (e.target === document.getElementById('lightbox')) closeLightbox();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
