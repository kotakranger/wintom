<?php
// admin/products-import.php — Impor produk massal dari CSV: unduh template -> unggah -> pratinjau -> konfirmasi.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/product-import.php';
require_login();
start_session();
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_check();

$db = get_db();
$categories = $db->query('SELECT name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_COLUMN);

// Unduh template
if (isset($_GET['template'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="template-produk-wintom.csv"');
    header('Cache-Control: no-store');
    echo pimport_template_csv($categories);
    exit;
}

$err = ''; $preview = null; $result = null;
$action = $_POST['_action'] ?? '';

// Langkah 2: unggah & validasi (belum menyimpan apa pun)
if ($action === 'preview') {
    $f = $_FILES['csv'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        $err = 'Pilih file CSV terlebih dahulu.';
    } elseif ($f['size'] > PIMPORT_MAX_FILE) {
        $err = 'Ukuran file maksimal 1 MB.';
    } elseif (!preg_match('/\.(csv|txt)$/i', $f['name'])) {
        $err = 'File harus berformat .csv (di Excel: Save As → CSV).';
    } else {
        $parsed = pimport_parse_csv($f['tmp_name']);
        if ($parsed['error']) {
            $err = $parsed['error'];
        } else {
            $preview = pimport_validate($db, $parsed['rows']);
            $_SESSION['product_import'] = ['file' => $f['name'], 'rows' => $preview, 'at' => time()];
        }
    }
}

// Langkah 3: simpan baris yang valid
if ($action === 'confirm') {
    $job = $_SESSION['product_import'] ?? null;
    if (!$job || time() - $job['at'] > 1800) {
        $err = 'Data pratinjau sudah kedaluwarsa. Unggah ulang file CSV.';
        unset($_SESSION['product_import']);
    } else {
        unset($_SESSION['product_import']);   // cegah impor ganda bila tombol ditekan dua kali
        session_write_close();
        @set_time_limit(300);
        $result = ['ok' => [], 'fail' => [], 'skip' => 0];
        foreach ($job['rows'] as $row) {
            if ($row['status'] === 'skip') { $result['skip']++; continue; }
            if ($row['status'] === 'error') { $result['fail'][] = ['line' => $row['line'], 'name' => $row['name'], 'msg' => implode(' ', $row['messages'])]; continue; }
            try {
                $r = pimport_save($db, $row['data']);
                $result['ok'][] = ['line' => $row['line'], 'name' => $row['name'], 'id' => $r['id'], 'warnings' => $r['warnings'], 'active' => $row['data']['is_active']];
            } catch (Throwable $e) {
                error_log('product import: ' . $e->getMessage());
                $result['fail'][] = ['line' => $row['line'], 'name' => $row['name'], 'msg' => 'Gagal menyimpan ke database.'];
            }
        }
    }
}

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES);
$count = ['ok' => 0, 'warn' => 0, 'error' => 0, 'skip' => 0];
foreach ($preview ?? [] as $r) $count[$r['status']]++;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Impor Produk (CSV) — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>
*{box-sizing:border-box;} textarea,input,select{outline:none;}
.label{display:block;font-family:Arial,Helvetica,sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;}
.input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#242424;background:#fff;transition:border-color 0.15s;}
.input:focus{border-color:#860524;}
select.input{appearance:auto;}
.peer:checked + .sw-track{background:#10b981;}
.peer:checked + .sw-track .sw-knob{transform:translateX(20px);}
.variant-card{border:1px solid #ece8e1;border-radius:6px;background:#fff;padding:16px;margin-bottom:12px;}
.variant-card.is-size{border-left:3px solid #860524;}
.opt-row{display:flex;gap:8px;align-items:center;margin-top:6px;}
.opt-input{flex:1;border:1px solid #ece8e1;border-radius:3px;padding:6px 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#242424;background:#fff;}
.opt-input:focus{border-color:#860524;}
.btn-sm{padding:4px 10px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.5px;text-transform:uppercase;border-radius:3px;cursor:pointer;transition:all 0.15s;}
.cover-badge{position:absolute;top:4px;left:4px;background:#860524;color:#fff;font-size:9px;letter-spacing:1px;text-transform:uppercase;padding:2px 6px;border-radius:2px;font-weight:600;}
</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">

<!-- ===== SIDEBAR ===== -->
<aside class="w-[220px] shrink-0 bg-white border-r border-border flex flex-col min-h-screen fixed top-0 left-0 h-full z-20">
  <div class="p-6 border-b border-border">
    <a href="../" target="_blank" class="flex flex-col group">
      <span class="font-playfair font-bold text-brand text-[22px] leading-[28px]">wintom</span>
      <span class="font-jakarta font-semibold text-muted text-[8px] tracking-[2.5px] uppercase group-hover:text-brand transition-colors">CURTAIN ADMIN</span>
    </a>
  </div>
  <nav class="flex flex-col p-4 gap-1 flex-1">
    <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Dashboard</a>
    <a href="inquiries.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Inquiry</a>
    <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium bg-brand/10 text-brand">Produk</a>
    <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Kategori</a>
    <a href="gallery.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Galeri Portofolio</a>
    <a href="videos.php" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium text-muted hover:bg-cream hover:text-dark transition-colors">Konten Video</a>
  </nav>
  <div class="p-4 border-t border-border">
    <form method="POST" action="logout.php">
      <?= csrf_field() ?>
      <button type="submit" class="block w-full text-left px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors bg-transparent border-0 cursor-pointer">Logout</button>
    </form>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<main class="ml-[220px] flex-1 p-8 min-h-screen max-w-[1100px]">

  <div class="mb-6">
    <a href="products.php" class="font-jakarta text-muted text-[12px] hover:text-brand transition-colors">← Kembali ke Manajemen Produk</a>
    <h1 class="font-playfair font-semibold text-dark text-[26px] mt-2">Impor Produk dari CSV</h1>
    <p class="font-jakarta text-muted text-[13px] mt-0.5">Tambahkan banyak produk sekaligus (maksimal <?= PIMPORT_MAX_ROWS ?> per file). Satu baris = satu produk.</p>
  </div>

  <?php if ($err): ?><div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]"><?= $h($err) ?></div><?php endif; ?>

<?php if ($result): ?>
  <!-- ============ HASIL ============ -->
  <div class="bg-white border border-border rounded-[6px] p-6">
    <h2 class="font-playfair font-semibold text-[20px] text-dark">Impor selesai</h2>
    <p class="font-jakarta text-[13px] text-muted mt-1">
      <b class="text-emerald-700"><?= count($result['ok']) ?> produk ditambahkan</b>
      <?php if ($result['skip']): ?> · <?= $result['skip'] ?> dilewati<?php endif; ?>
      <?php if ($result['fail']): ?> · <b class="text-red-600"><?= count($result['fail']) ?> gagal</b><?php endif; ?>
    </p>
    <?php if ($result['ok']): ?>
    <ul class="mt-4 divide-y divide-border border border-border rounded-[4px]">
      <?php foreach ($result['ok'] as $r): ?>
      <li class="px-4 py-2.5 flex items-start justify-between gap-4">
        <div>
          <p class="font-jakarta text-[13px] font-semibold text-dark"><?= $h($r['name']) ?> <?php if (!$r['active']): ?><span class="ml-1 font-normal text-[11px] text-amber-700">· Nonaktif</span><?php endif; ?></p>
          <?php foreach ($r['warnings'] as $w): ?><p class="font-jakarta text-[12px] text-amber-700">⚠ <?= $h($w) ?></p><?php endforeach; ?>
        </div>
        <a href="products.php?edit=<?= (int)$r['id'] ?>" class="shrink-0 font-jakarta text-[11px] uppercase tracking-[0.5px] text-brand border border-brand/30 px-3 py-1.5 rounded-[3px] hover:bg-brand/5">Edit</a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php if ($result['fail']): ?>
    <ul class="mt-4 divide-y divide-border border border-red-200 bg-red-50/40 rounded-[4px]">
      <?php foreach ($result['fail'] as $r): ?>
      <li class="px-4 py-2.5 font-jakarta text-[12px] text-red-700"><b>Baris <?= (int)$r['line'] ?> — <?= $h($r['name']) ?>:</b> <?= $h($r['msg']) ?></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <div class="mt-5 flex gap-3">
      <a href="products.php" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">Lihat Daftar Produk</a>
      <a href="products-import.php" class="font-jakarta text-[12px] uppercase tracking-[1px] text-muted border border-border px-5 py-3 rounded-[4px] hover:border-dark/30">Impor Lagi</a>
    </div>
  </div>

<?php elseif ($preview !== null): ?>
  <!-- ============ PRATINJAU ============ -->
  <div class="bg-white border border-border rounded-[6px] p-6">
    <h2 class="font-playfair font-semibold text-[20px] text-dark">Periksa dulu sebelum disimpan</h2>
    <p class="font-jakarta text-[13px] text-muted mt-1">Belum ada yang disimpan. Baris bertanda merah tidak akan diimpor; perbaiki di file lalu unggah ulang.</p>
    <div class="flex flex-wrap gap-2 mt-4 font-jakarta text-[12px]">
      <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><?= $count['ok'] ?> siap</span>
      <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><?= $count['warn'] ?> siap dengan catatan</span>
      <span class="px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200"><?= $count['error'] ?> error</span>
      <span class="px-3 py-1 rounded-full bg-cream-dark text-muted border border-border"><?= $count['skip'] ?> dilewati</span>
    </div>

    <div class="mt-4 border border-border rounded-[4px] overflow-hidden">
      <table class="w-full">
        <thead class="bg-cream-dark"><tr>
          <th class="px-3 py-2 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-14">Baris</th>
          <th class="px-3 py-2 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase">Produk</th>
          <th class="px-3 py-2 text-left font-jakarta font-semibold text-muted text-[11px] tracking-[1px] uppercase w-44">Hasil</th>
        </tr></thead>
        <tbody>
        <?php foreach ($preview as $r):
            $badge = ['ok' => ['Siap', 'bg-emerald-50 text-emerald-700 border-emerald-200'], 'warn' => ['Siap · ada catatan', 'bg-amber-50 text-amber-700 border-amber-200'],
                      'error' => ['Error', 'bg-red-50 text-red-700 border-red-200'], 'skip' => ['Dilewati', 'bg-cream-dark text-muted border-border']][$r['status']];
            $d = $r['data']; ?>
        <tr class="border-t border-border align-top">
          <td class="px-3 py-3 font-jakarta text-[12px] text-muted"><?= (int)$r['line'] ?></td>
          <td class="px-3 py-3">
            <p class="font-jakarta text-[13px] font-semibold text-dark"><?= $h($r['name'] ?: '(tanpa nama)') ?></p>
            <?php if ($d): ?>
            <p class="font-jakarta text-[11px] text-muted mt-0.5">
              <?= count($d['sizes']) ?> ukuran · <?= count($d['variants']) ?> varian · <?= count($d['photos']) ?> foto ·
              <?= $d['use_cat_price'] ? 'harga ikut kategori' : 'Rp ' . number_format($d['price'], 0, ',', '.') . ' / ' . $h($d['unit']) ?> ·
              <?= $d['is_active'] ? 'Aktif' : 'Nonaktif' ?>
            </p>
            <?php endif; ?>
            <?php foreach ($r['messages'] as $m): ?>
            <p class="font-jakarta text-[12px] mt-1 <?= $r['status'] === 'error' ? 'text-red-600' : ($r['status'] === 'warn' ? 'text-amber-700' : 'text-muted') ?>"><?= $r['status'] === 'error' ? '✕' : ($r['status'] === 'warn' ? '⚠' : '↷') ?> <?= $h($m) ?></p>
            <?php endforeach; ?>
          </td>
          <td class="px-3 py-3"><span class="inline-block whitespace-nowrap font-jakarta text-[11px] px-2.5 py-1 rounded-full border <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-3">
      <?php if ($count['ok'] + $count['warn'] > 0): ?>
      <form method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Menyimpan & mengunduh foto…';">
        <input type="hidden" name="_action" value="confirm"/><?= csrf_field() ?>
        <button type="submit" class="bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors disabled:opacity-60">
          Impor <?= $count['ok'] + $count['warn'] ?> Produk
        </button>
      </form>
      <?php endif; ?>
      <a href="products-import.php" class="font-jakarta text-[12px] uppercase tracking-[1px] text-muted border border-border px-5 py-3 rounded-[4px] hover:border-dark/30">Batal / Unggah Ulang</a>
      <p class="font-jakarta text-[11px] text-muted">Foto diunduh saat disimpan, jadi prosesnya bisa memakan waktu beberapa menit.</p>
    </div>
  </div>

<?php else: ?>
  <!-- ============ LANGKAH 1 & 2 ============ -->
  <div class="grid grid-cols-12 gap-5">
    <section class="col-span-12 lg:col-span-6 bg-white border border-border rounded-[6px] p-6 flex flex-col">
      <p class="font-jakarta text-[11px] tracking-[1.5px] uppercase text-brand font-semibold">Langkah 1</p>
      <h2 class="font-playfair font-semibold text-[18px] text-dark mt-1">Unduh template & isi datanya</h2>
      <p class="font-jakarta text-[13px] text-muted mt-2 leading-[20px]">Template berisi 2 baris contoh. Hapus atau ganti dengan produkmu (baris berawalan <b>[CONTOH]</b> otomatis dilewati). Buka dengan Excel atau Google Sheets, lalu simpan sebagai <b>CSV</b>.</p>
      <a href="products-import.php?template=1" class="mt-4 self-start bg-white text-brand border border-brand/40 font-jakarta font-semibold text-[12px] tracking-[1.2px] uppercase px-5 py-3 rounded-[4px] hover:bg-brand/5 transition-colors">⬇ Unduh Template CSV</a>
      <p class="font-jakarta text-[11px] text-muted mt-4 leading-[17px]">Kategori yang tersedia (tulis persis seperti ini):<br><b class="text-dark"><?= $h(implode(' · ', $categories)) ?></b></p>
    </section>

    <section class="col-span-12 lg:col-span-6 bg-white border border-border rounded-[6px] p-6 flex flex-col">
      <p class="font-jakarta text-[11px] tracking-[1.5px] uppercase text-brand font-semibold">Langkah 2</p>
      <h2 class="font-playfair font-semibold text-[18px] text-dark mt-1">Unggah file CSV</h2>
      <form method="POST" enctype="multipart/form-data" class="mt-3 flex flex-col gap-3">
        <input type="hidden" name="_action" value="preview"/><?= csrf_field() ?>
        <label class="flex flex-col items-center justify-center gap-1 border-2 border-dashed border-border rounded-[6px] bg-cream/60 px-4 py-8 text-center cursor-pointer hover:border-brand/50 transition-colors" id="drop">
          <span class="font-jakarta text-[13px] text-dark font-semibold" id="drop-text">Klik untuk memilih file CSV</span>
          <span class="font-jakarta text-[11px] text-muted">atau seret file ke sini · maks. 1 MB</span>
          <input type="file" name="csv" accept=".csv,.txt,text/csv" class="sr-only" required id="csv"/>
        </label>
        <button type="submit" class="self-start bg-brand text-white font-jakarta font-semibold text-[12px] tracking-[1.5px] uppercase px-5 py-3 rounded-[4px] hover:bg-[#6a041c] transition-colors">Periksa File</button>
      </form>
    </section>
  </div>

  <section class="mt-5 bg-white border border-border rounded-[6px] p-6">
    <h2 class="font-playfair font-semibold text-[18px] text-dark">Panduan kolom</h2>
    <div class="mt-3 border border-border rounded-[4px] overflow-hidden">
      <table class="w-full font-jakarta text-[12px]">
        <thead class="bg-cream-dark text-muted"><tr><th class="px-3 py-2 text-left w-36">Kolom</th><th class="px-3 py-2 text-left w-20">Wajib?</th><th class="px-3 py-2 text-left">Isi</th></tr></thead>
        <tbody class="divide-y divide-border text-dark">
          <tr><td class="px-3 py-2 font-semibold">nama_produk</td><td class="px-3 py-2">Ya</td><td class="px-3 py-2">Nama produk. Produk dengan nama yang sudah ada akan dilewati.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">kategori</td><td class="px-3 py-2">Ya</td><td class="px-3 py-2">Nama kategori, persis seperti di daftar kategori.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">badge, kode_ref</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Label kecil (mis. Terlaris) dan kode produk. Kode ref yang sudah dipakai produk lain tetap diimpor, tapi diberi peringatan.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">deskripsi</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Teks deskripsi produk.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">fitur</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Spesifikasi, dipisah garis tegak: <code>Bahan linen|Tidak luntur</code></td></tr>
          <tr><td class="px-3 py-2 font-semibold">status</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2"><code>aktif</code> (default) atau <code>nonaktif</code>.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">stok</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Angka bulat. Kosong = tidak dilacak.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">harga_custom, satuan_harga</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Kosongkan agar ikut harga per meter kategori. Isi untuk harga khusus, mis. <code>450000</code> dan <code>meter</code>.</td></tr>
          <tr><td class="px-3 py-2 font-semibold">ukuran</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Lebar×tinggi dalam cm, dipisah garis tegak: <code>100x200|150x220</code></td></tr>
          <tr><td class="px-3 py-2 font-semibold">varian_1 … varian_5</td><td class="px-3 py-2">Tidak</td><td class="px-3 py-2">Satu varian per kolom: <code>Warna: Putih|Krem|Abu-abu</code></td></tr>
          <tr><td class="px-3 py-2 font-semibold">foto</td><td class="px-3 py-2">Disarankan</td><td class="px-3 py-2">Alamat (URL) foto, dipisah garis tegak. Foto pertama jadi cover, maks. <?= PIMPORT_MAX_PHOTOS ?>. Foto diunduh &amp; dikompres otomatis (maks. 5 MB per foto). Tanpa foto, produk diimpor sebagai Nonaktif.</td></tr>
        </tbody>
      </table>
    </div>
    <p class="font-jakarta text-[11px] text-muted mt-3 leading-[17px]">Tips: foto produk bisa diunggah ke Google Drive/Dropbox/hosting gambar lain dan alamat <i>langsung</i>-nya ditempel di kolom foto. Pilihan varian dengan foto tertaut diatur lewat tombol Edit setelah impor.<br>Jika semua data menumpuk di satu kolom di Excel, buka lewat Google Sheets (File → Import) atau Data → Text to Columns.</p>
  </section>
  <script>
    (function () {
      var drop = document.getElementById('drop'), inp = document.getElementById('csv'), txt = document.getElementById('drop-text');
      inp.addEventListener('change', function () { if (inp.files[0]) txt.textContent = inp.files[0].name; });
      ['dragover', 'dragenter'].forEach(function (e) { drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('border-brand/60'); }); });
      ['dragleave', 'drop'].forEach(function (e) { drop.addEventListener(e, function () { drop.classList.remove('border-brand/60'); }); });
      drop.addEventListener('drop', function (ev) { ev.preventDefault(); if (ev.dataTransfer.files.length) { inp.files = ev.dataTransfer.files; inp.dispatchEvent(new Event('change')); } });
    })();
  </script>
<?php endif; ?>
</main>
</body>
</html>
