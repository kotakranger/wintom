<?php
// One-time import: database/curtains.sqlite -> MySQL.
// Usage:  php database/migrate_sqlite_to_mysql.php [--force]
// IDs are preserved. Aborts if MySQL already has data, unless --force (which empties the tables first).

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$sqlite_path = __DIR__ . '/curtains.sqlite';
if (!is_file($sqlite_path)) {
    fwrite(STDERR, "SQLite file not found: $sqlite_path\n");
    exit(1);
}

$src = new PDO('sqlite:' . $sqlite_path);
$src->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$src->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$dst = connect_db();
ensure_schema($dst);

$has_data = (int)$dst->query('SELECT COUNT(*) FROM products')->fetchColumn() > 0
         || (int)$dst->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
if ($has_data) {
    if (!in_array('--force', $argv, true)) {
        fwrite(STDERR, "MySQL database '" . DB_NAME . "' already has data. Re-run with --force to replace it.\n");
        exit(1);
    }
    $dst->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['inquiries', 'product_variant_options', 'product_variants', 'product_images', 'products', 'galleries', 'categories', 'admins'] as $t) {
        $dst->exec("TRUNCATE TABLE `$t`");
    }
    $dst->exec('SET FOREIGN_KEY_CHECKS = 1');
}

// Placeholder base prices for the five default categories; admin can edit them afterwards.
$default_ppm = [
    'Curtain'                => 150000,
    'Vitrase & Sheer'        => 100000,
    'Roller Blind'           => 100000,
    'Motorized Smart System' => 750000,
    'Wood & Bamboo Blind'    => 450000,
];

function copy_rows(PDO $src, PDO $dst, string $table, array $cols, ?callable $map = null): int {
    $rows = $src->query("SELECT * FROM $table ORDER BY id")->fetchAll();
    if (!$rows) {
        return 0;
    }
    $names = '`' . implode('`,`', array_keys($map ? $map($rows[0]) : array_intersect_key($rows[0], array_flip($cols)))) . '`';
    $first = $map ? $map($rows[0]) : array_intersect_key($rows[0], array_flip($cols));
    $stmt = $dst->prepare("INSERT INTO `$table` ($names) VALUES (" . implode(',', array_fill(0, count($first), '?')) . ')');
    foreach ($rows as $row) {
        $stmt->execute(array_values($map ? $map($row) : array_intersect_key($row, array_flip($cols))));
    }
    return count($rows);
}

$dst->beginTransaction();
try {
    $report = [];
    $report['admins'] = copy_rows($src, $dst, 'admins', ['id', 'username', 'password_hash']);

    $report['categories'] = copy_rows($src, $dst, 'categories', [], fn($r) => [
        'id'              => $r['id'],
        'name'            => $r['name'],
        'price_per_meter' => $default_ppm[$r['name']] ?? 0,
        'sort_order'      => $r['sort_order'],
        'created_at'      => $r['created_at'],
    ]);

    $cat_ids = $dst->query('SELECT name, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    $report['products'] = copy_rows($src, $dst, 'products', [], function ($r) use ($cat_ids) {
        if (!isset($cat_ids[$r['category']])) {
            throw new RuntimeException("Product '{$r['name']}' uses unknown category '{$r['category']}'");
        }
        return [
            'id'             => $r['id'],
            'slug'           => $r['slug'],
            'name'           => $r['name'],
            'category_id'    => $cat_ids[$r['category']],
            'badge'          => $r['badge'],
            'ref_code'       => $r['ref_code'],
            'price'          => $r['price'],
            'original_price' => $r['original_price'],
            'price_unit'     => $r['price_unit'],
            'description'    => $r['description'],
            'features'       => $r['features'],
            'options_json'   => $r['options_json'],
            'cover_image'    => $r['cover_image'],
            'is_active'      => $r['is_active'] ?? 1,
            'stock'          => $r['stock'] ?? null,
            'created_at'     => $r['created_at'],
        ];
    });

    $report['product_images']          = copy_rows($src, $dst, 'product_images', ['id', 'product_id', 'image_url', 'sort_order', 'is_cover']);
    $report['product_variants']        = copy_rows($src, $dst, 'product_variants', ['id', 'product_id', 'label', 'is_image_linked', 'sort_order']);
    $report['product_variant_options'] = copy_rows($src, $dst, 'product_variant_options', ['id', 'variant_id', 'value', 'linked_image_id', 'sort_order']);
    $report['galleries']               = copy_rows($src, $dst, 'galleries', ['id', 'title', 'category', 'image_url', 'created_at']);

    $dst->commit();
} catch (Throwable $e) {
    $dst->rollBack();
    fwrite(STDERR, 'Migration failed, nothing was written: ' . $e->getMessage() . "\n");
    exit(1);
}

foreach ($report as $table => $n) {
    printf("%-26s %d rows\n", $table, $n);
}
echo "Done. Source SQLite file was left untouched.\n";
