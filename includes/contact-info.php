<?php
// includes/contact-info.php — Kartu peta showroom + detail kontak (homepage #contact & about).
$maps_q = urlencode('Ruko Soho Ebony, Pantai Indah Kapuk, Jakarta Utara');
?>
<div class="h-full flex flex-col bg-white border border-border rounded-[16px] sm:rounded-[24px] shadow-sm overflow-hidden min-w-0">
  <!-- Di desktop peta mengisi sisa tinggi agar kartu sejajar dengan form di sebelahnya -->
  <div class="relative aspect-[4/3] sm:aspect-[16/10] lg:aspect-auto lg:flex-1 lg:min-h-[280px] bg-border">
    <iframe
      src="https://maps.google.com/maps?q=<?= $maps_q ?>&z=15&output=embed&hl=id"
      class="absolute inset-0 w-full h-full" style="border:0;" allowfullscreen="" loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      title="Lokasi Wintom Curtain Showroom"></iframe>
    <a href="https://www.google.com/maps/search/?api=1&query=<?= $maps_q ?>" target="_blank" rel="noopener"
       class="absolute top-3 right-3 sm:top-4 sm:right-4 inline-flex items-center gap-2 bg-white text-dark font-jakarta font-medium text-[12px] sm:text-[13px] px-3.5 sm:px-4 py-2.5 rounded-full shadow-md hover:shadow-lg transition-shadow">
      <span class="text-brand"><svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.686-7-11a7 7 0 1 1 14 0c0 5.314-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span> Buka di Google Maps
    </a>
  </div>
  <ul class="px-5 sm:px-7 py-6 space-y-3.5 font-jakarta text-[14px] leading-[22px] text-dark list-none m-0">
    <li class="flex items-start gap-3"><svg class="size-[18px] shrink-0 mt-0.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg><span><?= SITE_ADDRESS ?></span></li>
    <li class="flex items-start gap-3"><svg class="size-[18px] shrink-0 mt-0.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg><span><?= SITE_HOURS ?></span></li>
    <li class="flex items-start gap-3"><svg class="size-[18px] shrink-0 mt-0.5 text-[#25d366]" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.667A8.333 8.333 0 0 0 2.92 13.742L1.667 18.333l4.725-1.237A8.333 8.333 0 1 0 10 1.667Z"/></svg><span><a href="<?= WA_URL ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener" class="hover:text-brand transition-colors whitespace-nowrap"><?= SITE_PHONE ?></a> &middot; <a href="<?= WA_URL_2 ?>?text=Halo+Wintom%2C+saya+ingin+konsultasi" target="_blank" rel="noopener" class="hover:text-brand transition-colors whitespace-nowrap"><?= SITE_PHONE_2 ?></a> <span class="text-muted">(WhatsApp)</span></span></li>
    <li class="flex items-start gap-3"><svg class="size-[18px] shrink-0 mt-0.5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg><a href="mailto:<?= SITE_EMAIL ?>" class="hover:text-brand transition-colors break-all"><?= SITE_EMAIL ?></a></li>
  </ul>
</div>
