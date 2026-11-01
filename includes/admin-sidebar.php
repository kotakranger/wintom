<?php
// includes/admin-sidebar.php — Sidebar admin bersama + kerangka halaman untuk halaman baru.
// Halaman lama cukup memanggil admin_sidebar('products') di tempat <aside> lama.

function admin_role_label(string $role): string {
    return $role === ROLE_SUPER ? 'Super Admin' : 'Admin';
}

/** @param string $active dashboard|inquiries|products|categories|gallery|videos|users|account */
function admin_sidebar(string $active): void {
    $me  = current_admin();
    $nav = [
        'dashboard'  => ['index.php', 'Dashboard'],
        'inquiries'  => ['inquiries.php', 'Inquiry'],
        'products'   => ['products.php', 'Produk'],
        'categories' => ['categories.php', 'Kategori'],
        'gallery'    => ['gallery.php', 'Galeri Portofolio'],
        'videos'     => ['videos.php', 'Konten Video'],
    ];
    if (is_super_admin()) $nav['users'] = ['users.php', 'Pengguna'];
    $on  = 'bg-brand/10 text-brand';
    $off = 'text-muted hover:bg-cream hover:text-dark transition-colors';
    $weak = !empty($_SESSION['weak_pw']);
    ?>
<aside class="w-[220px] shrink-0 bg-white border-r border-border flex flex-col min-h-screen fixed top-0 left-0 h-full z-20">
  <div class="p-6 border-b border-border">
    <a href="../" target="_blank" class="flex flex-col group">
      <span class="font-playfair font-bold text-brand text-[22px] leading-[28px]">wintom</span>
      <span class="font-jakarta font-semibold text-muted text-[8px] tracking-[2.5px] uppercase group-hover:text-brand transition-colors">CURTAIN ADMIN</span>
    </a>
  </div>
  <nav class="flex flex-col p-4 gap-1 flex-1 overflow-y-auto">
    <?php foreach ($nav as $key => [$href, $label]): ?>
    <a href="<?= $href ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-[4px] font-jakarta text-[13px] font-medium <?= $key === $active ? $on : $off ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 border-t border-border flex flex-col gap-2">
    <?php if ($weak): ?>
    <a href="account.php" class="block rounded-[4px] border border-amber-200 bg-amber-50 px-3 py-2 font-jakarta text-[11px] leading-[16px] text-amber-800 hover:bg-amber-100 transition-colors">
      <b>Password masih bawaan.</b> Ganti sekarang →
    </a>
    <?php endif; ?>
    <?php if ($me): ?>
    <a href="account.php" class="flex items-center gap-2.5 rounded-[4px] px-2 py-2 transition-colors <?= $active === 'account' ? $on : 'hover:bg-cream' ?>">
      <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-white font-jakarta font-semibold text-[13px] uppercase"><?= htmlspecialchars(mb_substr($me['username'], 0, 1)) ?></span>
      <span class="min-w-0 leading-tight">
        <span class="block truncate font-jakarta font-semibold text-[13px] text-dark"><?= htmlspecialchars($me['username']) ?></span>
        <span class="block font-jakarta text-[11px] text-muted"><?= admin_role_label($me['role']) ?> · Akun Saya</span>
      </span>
    </a>
    <?php endif; ?>
    <form method="POST" action="logout.php">
      <?= csrf_field() ?>
      <button type="submit" class="block w-full text-left px-3 py-2 text-muted hover:text-brand font-jakarta text-[13px] transition-colors bg-transparent border-0 cursor-pointer">Logout</button>
    </form>
  </div>
</aside>
    <?php
}

/** Kepala halaman lengkap (head + sidebar + <main>) untuk halaman admin baru. Tutup dengan admin_page_end(). */
function admin_page_start(string $title, string $active, string $main_class = 'max-w-[1000px]'): void {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= htmlspecialchars($title) ?> — Wintom Admin</title>
<link rel="icon" type="image/png" href="../assets/images/favicon.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{playfair:['Georgia','"Times New Roman"','serif'],jakarta:['Arial','Helvetica','sans-serif']},colors:{brand:{DEFAULT:'#860524',dark:'#6a041c'},dark:'#242424',muted:'#686561',cream:'#faf8f5','cream-dark':'#f4f1ea',border:'#ece8e1'}}}}</script>
<style>
*{box-sizing:border-box;} textarea,input,select{outline:none;}
[hidden]{display:none !important;}
.label{display:block;font-family:Arial,Helvetica,sans-serif;font-weight:500;font-size:12px;letter-spacing:0.6px;text-transform:uppercase;color:#686561;margin-bottom:6px;}
.input{display:block;width:100%;border:1px solid #ece8e1;border-radius:4px;padding:9px 12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#242424;background:#fff;transition:border-color 0.15s;}
.input:focus{border-color:#860524;}
</style>
</head>
<body class="font-jakarta bg-[#f5f3f0] text-dark antialiased flex min-h-screen">
<?php admin_sidebar($active); ?>
<main class="ml-[220px] flex-1 p-8 min-h-screen min-w-0 <?= $main_class ?>">
    <?php
}

function admin_page_end(): void {
    echo "</main>\n</body>\n</html>\n";
}

/** Pesan sukses/gagal di atas halaman. */
function admin_flash(string $msg, string $err): void {
    if ($msg) echo '<div class="mb-5 bg-green-50 border border-green-200 text-green-800 font-jakarta text-[13px] px-4 py-3 rounded-[4px]">' . htmlspecialchars($msg) . '</div>';
    if ($err) echo '<div class="mb-5 bg-red-50 border border-red-200 text-red-700 font-jakarta text-[13px] px-4 py-3 rounded-[4px]">' . htmlspecialchars($err) . '</div>';
}
