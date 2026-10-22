<?php
// includes/product-card.php — Card produk katalog & "koleksi pendamping".
// Harga = harga per meter kategori (default) atau harga custom produk — lihat product_price().
// Butuh kolom: slug, name, description, features, cover_image, category, category_id, price_per_meter,
//              use_category_price, price, price_unit.

function render_product_card(array $prod, int $delay = 0): void
{
    $detail_url = BASE_URL . '/product-detail.php?slug=' . urlencode($prod['slug']);
    $pp         = product_price($prod);
    $feat_list  = !empty($prod['features']) ? array_slice(explode('|', $prod['features']), 0, 2) : [];
    ?>
    <article class="group bg-white border border-border rounded-[4px] overflow-hidden flex flex-col hover:border-brand/40 hover:shadow-xl transition-[border-color,box-shadow] duration-300"
             data-reveal style="--d:<?= $delay ?>ms">

      <!-- Foto (rasio 3/4) + label kategori -->
      <a href="<?= $detail_url ?>" class="relative aspect-[3/4] overflow-hidden bg-cream block" tabindex="-1" aria-hidden="true">
        <img src="<?= htmlspecialchars($prod['cover_image']) ?>" alt="" loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" />
        <div class="absolute inset-0 bg-gradient-to-t from-dark/60 via-transparent to-black/10 opacity-60 group-hover:opacity-80 transition-opacity"></div>
        <span class="absolute top-3.5 left-3.5 bg-white/95 backdrop-blur-[4px] text-dark font-jakarta font-semibold text-[10px] tracking-[1.2px] uppercase px-2.5 py-1 rounded-[2px] shadow-sm">
          <?= htmlspecialchars($prod['category']) ?>
        </span>
      </a>

      <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
        <div>
          <h3 class="font-playfair text-[19px] md:text-[20px] font-bold text-dark leading-snug mb-2 group-hover:text-brand transition-colors">
            <a href="<?= $detail_url ?>"><?= htmlspecialchars($prod['name']) ?></a>
          </h3>
          <?php if (!empty($prod['description'])): ?>
          <p class="font-jakarta text-[13px] text-muted leading-relaxed line-clamp-2 mb-4"><?= htmlspecialchars($prod['description']) ?></p>
          <?php endif; ?>
          <?php if ($feat_list): ?>
          <div class="space-y-1.5 mb-5 pb-5 border-b border-border/70">
            <?php foreach ($feat_list as $f): ?>
            <div class="flex items-center gap-2 text-[11px] text-dark/80 font-jakarta min-w-0">
              <svg class="w-3.5 h-3.5 text-brand shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span class="truncate"><?= htmlspecialchars(trim($f)) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <div>
          <!-- Harga: per meter kategori atau harga custom produk -->
          <div class="mb-4">
            <div class="text-[11px] font-jakarta tracking-wider uppercase text-muted mb-0.5"><?= $pp['custom'] ? 'Harga' : 'Harga per meter' ?></div>
            <?php if ($pp['amount'] > 0): ?>
            <div class="flex flex-wrap items-baseline gap-x-1.5">
              <span class="font-playfair font-bold text-[22px] sm:text-2xl text-brand">Rp <?= number_format($pp['amount'], 0, ',', '.') ?></span>
              <span class="text-[11px] font-jakarta text-muted"><?= htmlspecialchars($pp['unit']) ?></span>
            </div>
            <?php else: ?>
            <span class="font-jakarta text-[13px] text-dark">Hubungi kami untuk harga</span>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <a href="<?= $detail_url ?>"
               class="w-full text-center py-2.5 border border-dark/30 hover:border-brand hover:text-brand text-dark font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase rounded-[3px] transition-colors">
              LIHAT DETAIL
            </a>
            <button type="button" data-open-quote
                    data-quote-category="<?= (int)$prod['category_id'] ?>"
                    data-quote-notes="<?= htmlspecialchars('Produk: ' . $prod['name']) ?>"
                    class="w-full text-center py-2.5 bg-brand hover:bg-[#6a041c] text-white font-jakarta font-semibold text-[11px] tracking-widest-2 uppercase rounded-[3px] transition-colors">
              KONSULTASI
            </button>
          </div>
        </div>
      </div>
    </article>
    <?php
}
