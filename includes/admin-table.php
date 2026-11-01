<?php
// includes/admin-table.php — Pembantu tabel admin: tanggal/waktu Indonesia, nomor telepon, status inquiry, paginasi, kondisi kosong.

const ADMIN_PER_PAGE = 10;

const INQUIRY_STATUSES = [
    // kunci => [label, kelas pill, warna titik]
    'new'       => ['Baru',      'bg-amber-50 text-amber-800 border-amber-300', '#f59e0b'],
    'contacted' => ['Dihubungi', 'bg-blue-50 text-blue-800 border-blue-200',    '#3b82f6'],
    'closed'    => ['Selesai',   'bg-green-50 text-green-800 border-green-200', '#16a34a'],
];

/* ------------------------------------------------------------------ TANGGAL & WAKTU */

function admin_month_short(int $m): string {
    return ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$m];
}

/** "31 Okt 2026" (tahun dihilangkan bila tahun ini dan $short = true). */
function admin_date(string $ts, bool $short = false): string {
    $t = strtotime($ts);
    return (int)date('j', $t) . ' ' . admin_month_short((int)date('n', $t)) . ($short && date('Y', $t) === date('Y') ? '' : ' ' . date('Y', $t));
}

/** Selisih waktu singkat: "baru saja", "5 menit lalu", "3 jam lalu", "2 hari lalu". */
function admin_ago(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)        return 'baru saja';
    if ($d < 3600)      return (int)($d / 60) . ' menit lalu';
    if ($d < 86400)     return (int)($d / 3600) . ' jam lalu';
    if ($d < 86400 * 30) return (int)($d / 86400) . ' hari lalu';
    return '';
}

/** Dua baris untuk kolom Waktu: ["Hari ini" | "Kemarin" | "31 Okt 2026", "21:45 · 3 jam lalu"]. */
function admin_time_lines(string $ts): array {
    $t = strtotime($ts);
    $day = date('Y-m-d', $t);
    $line1 = $day === date('Y-m-d') ? 'Hari ini' : ($day === date('Y-m-d', strtotime('-1 day')) ? 'Kemarin' : admin_date($ts, true));
    $ago = admin_ago($ts);
    return [$line1, date('H:i', $t) . ($ago !== '' && time() - $t < 86400 * 7 ? ' · ' . $ago : '')];
}

/* ------------------------------------------------------------------ TELEPON */

/** "628127128112" -> "+62 812-7128-112" (nomor non-Indonesia ditampilkan apa adanya dengan +). */
function admin_phone(string $digits): string {
    $d = preg_replace('/\D/', '', $digits);
    if (!str_starts_with($d, '62') || strlen($d) < 10) return '+' . $d;
    $n = substr($d, 2);
    return '+62 ' . substr($n, 0, 3) . '-' . substr($n, 3, 4) . (strlen($n) > 7 ? '-' . substr($n, 7) : '');
}

/** Nomor + tombol salin. $digits tanpa tanda +. */
function admin_phone_copy(string $digits): void {
    $d = preg_replace('/\D/', '', $digits);
    ?><span class="inline-flex items-center gap-1.5 whitespace-nowrap">
      <span class="font-jakarta text-[13px] text-dark tabular-nums"><?= htmlspecialchars(admin_phone($d)) ?></span>
      <button type="button" data-copy="+<?= htmlspecialchars($d) ?>" class="adm-copy" data-tip="Salin nomor" aria-label="Salin nomor <?= htmlspecialchars(admin_phone($d)) ?>">
        <svg class="size-[14px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 012-2h9"/></svg>
      </button>
    </span><?php
}

/* ------------------------------------------------------------------ PAGINASI */

/** @return array{page:int,pages:int,per:int,total:int,offset:int,from:int,to:int} */
function admin_paginate(int $total, int $per = ADMIN_PER_PAGE): array {
    $pages  = max(1, (int)ceil($total / $per));
    $page   = min($pages, max(1, (int)($_GET['page'] ?? 1)));
    $offset = ($page - 1) * $per;
    return ['page' => $page, 'pages' => $pages, 'per' => $per, 'total' => $total, 'offset' => $offset,
            'from' => $total ? $offset + 1 : 0, 'to' => min($total, $offset + $per)];
}

function admin_page_url(int $page): string {
    $q = $_GET; unset($q['page']);
    if ($page > 1) $q['page'] = $page;
    $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    return $path . ($q ? '?' . http_build_query($q) : '');
}

/** Baris paginasi; tampil hanya bila data lebih dari satu halaman (> 10). */
function admin_pagination(array $pg, bool $in_card = true): void {
    if ($pg['pages'] <= 1) return;
    $nums = [1];
    for ($i = $pg['page'] - 1; $i <= $pg['page'] + 1; $i++) if ($i > 1 && $i < $pg['pages']) $nums[] = $i;
    $nums[] = $pg['pages'];
    $nums = array_values(array_unique($nums));
    $btn  = 'inline-flex items-center justify-center min-w-[34px] h-[34px] px-2 rounded-[6px] border font-jakarta text-[12px] font-semibold transition-colors';
    ?>
    <nav class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 <?= $in_card ? 'border-t border-border bg-cream/40' : 'mt-4' ?>" aria-label="Halaman">
      <p class="font-jakarta text-muted text-[12px]">Menampilkan <b class="text-dark"><?= $pg['from'] ?>–<?= $pg['to'] ?></b> dari <b class="text-dark"><?= $pg['total'] ?></b></p>
      <div class="flex items-center gap-1.5">
        <?php if ($pg['page'] > 1): ?>
          <a href="<?= htmlspecialchars(admin_page_url($pg['page'] - 1)) ?>" class="<?= $btn ?> border-border bg-white text-dark hover:border-brand/40" aria-label="Halaman sebelumnya">‹ <span class="hidden sm:inline ml-1">Sebelumnya</span></a>
        <?php else: ?>
          <span class="<?= $btn ?> border-border bg-white text-muted/40 cursor-not-allowed" aria-hidden="true">‹ <span class="hidden sm:inline ml-1">Sebelumnya</span></span>
        <?php endif; ?>
        <?php $prev = 0; foreach ($nums as $n): ?>
          <?php if ($prev && $n - $prev > 1): ?><span class="px-1 text-muted text-[12px]">…</span><?php endif; ?>
          <?php if ($n === $pg['page']): ?>
            <span class="<?= $btn ?> border-brand bg-brand text-white" aria-current="page"><?= $n ?></span>
          <?php else: ?>
            <a href="<?= htmlspecialchars(admin_page_url($n)) ?>" class="<?= $btn ?> border-border bg-white text-dark hover:border-brand/40" aria-label="Halaman <?= $n ?>"><?= $n ?></a>
          <?php endif; ?>
        <?php $prev = $n; endforeach; ?>
        <?php if ($pg['page'] < $pg['pages']): ?>
          <a href="<?= htmlspecialchars(admin_page_url($pg['page'] + 1)) ?>" class="<?= $btn ?> border-border bg-white text-dark hover:border-brand/40" aria-label="Halaman berikutnya"><span class="hidden sm:inline mr-1">Berikutnya</span> ›</a>
        <?php else: ?>
          <span class="<?= $btn ?> border-border bg-white text-muted/40 cursor-not-allowed" aria-hidden="true"><span class="hidden sm:inline mr-1">Berikutnya</span> ›</span>
        <?php endif; ?>
      </div>
    </nav>
    <?php
}

/* ------------------------------------------------------------------ KONDISI KOSONG & STATUS */

/** Baris tabel kosong dengan ikon, judul, keterangan, dan (opsional) tombol. */
function admin_empty_row(int $cols, string $title, string $text = '', string $cta_href = '', string $cta_label = ''): void {
    ?>
    <tr><td colspan="<?= $cols ?>" class="px-5 py-14 text-center">
      <span class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-cream-dark text-muted">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0h-5l-1 2h-4l-1-2H4"/></svg>
      </span>
      <p class="font-jakarta font-semibold text-dark text-[14px]"><?= htmlspecialchars($title) ?></p>
      <?php if ($text): ?><p class="font-jakarta text-muted text-[12px] mt-1"><?= htmlspecialchars($text) ?></p><?php endif; ?>
      <?php if ($cta_href): ?><a href="<?= htmlspecialchars($cta_href) ?>" class="inline-block mt-4 font-jakarta font-semibold text-[12px] text-brand hover:underline"><?= htmlspecialchars($cta_label) ?> →</a><?php endif; ?>
    </td></tr>
    <?php
}

/** Pill status inquiry (hanya baca). */
function admin_status_pill(string $status): string {
    [$label, $cls, $dot] = INQUIRY_STATUSES[$status] ?? [$status, 'bg-gray-50 text-gray-700 border-gray-200', '#9ca3af'];
    return '<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 font-jakarta text-[11px] font-semibold ' . $cls . '">'
         . '<span class="size-1.5 rounded-full" style="background:' . $dot . '"></span>' . htmlspecialchars($label) . '</span>';
}
