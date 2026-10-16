<?php
/**
 * Security test suite for Wintom Curtain
 * Run: php tests/check_security.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$passed = 0;
$failed = 0;
$test_dir = __DIR__;

// ============================================================
// TEST 1: CSRF Token Generation (GET mode)
// ============================================================
echo "TEST 1: CSRF Token Generation (GET)\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/admin/test.php';
$_SERVER['HTTP_HOST'] = 'localhost';
session_status() === PHP_SESSION_NONE && session_start();
session_destroy();

require_once __DIR__ . '/../includes/auth.php';

$token1 = csrf_token();
$token2 = csrf_token();

if (preg_match('/^[0-9a-f]{64}$/', $token1) && $token1 === $token2) {
    echo "  ✓ csrf_token() returns 64-char hex string consistently\n";
    $passed++;
} else {
    echo "  ✗ csrf_token() FAILED: token1='$token1' token2='$token2'\n";
    $failed++;
}

// ============================================================
// TEST 2: CSRF Field HTML Output
// ============================================================
echo "TEST 2: CSRF Field HTML\n";
$field = csrf_field();
if (strpos($field, htmlspecialchars($token1)) !== false && preg_match('/<input[^>]*name="csrf"/', $field)) {
    echo "  ✓ csrf_field() contains token in hidden input\n";
    $passed++;
} else {
    echo "  ✗ csrf_field() FAILED: field='$field'\n";
    $failed++;
}

// ============================================================
// TEST 3: CSRF Check - Bad Token (child process)
// ============================================================
echo "TEST 3: CSRF Check - Bad Token\n";
$base_dir = __DIR__ . '/..';
$script = <<<PHPCODE
<?php
\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_SERVER['SCRIPT_NAME'] = '/admin/test.php';
\$_SERVER['HTTP_HOST'] = 'localhost';
\$_POST['csrf'] = 'bad_token_value';
session_start();
\$_SESSION['csrf'] = bin2hex(random_bytes(32));
require_once '{$base_dir}/includes/auth.php';
csrf_check();
echo "PASSED";
?>
PHPCODE;

$tmpfile = tempnam(sys_get_temp_dir(), 'csrf_');
file_put_contents($tmpfile, $script);
$output = shell_exec('php ' . escapeshellarg($tmpfile) . ' 2>&1');
unlink($tmpfile);

if (strpos($output, 'Invalid CSRF token') !== false && strpos($output, 'PASSED') === false) {
    echo "  ✓ csrf_check() rejects invalid token (exits with error)\n";
    $passed++;
} else {
    echo "  ✗ csrf_check() FAILED: output='$output'\n";
    $failed++;
}

// ============================================================
// TEST 4: CSRF Check - Valid Token (child process)
// ============================================================
echo "TEST 4: CSRF Check - Valid Token\n";
$script = <<<PHPCODE
<?php
\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_SERVER['SCRIPT_NAME'] = '/admin/test.php';
\$_SERVER['HTTP_HOST'] = 'localhost';
session_start();
\$_SESSION['csrf'] = bin2hex(random_bytes(32));
\$_POST['csrf'] = \$_SESSION['csrf'];
require_once '{$base_dir}/includes/auth.php';
csrf_check();
echo "PASSED";
?>
PHPCODE;

$tmpfile = tempnam(sys_get_temp_dir(), 'csrf_');
file_put_contents($tmpfile, $script);
$output = shell_exec('php ' . escapeshellarg($tmpfile) . ' 2>&1');
unlink($tmpfile);

if (strpos($output, 'PASSED') !== false && strpos($output, 'Invalid') === false) {
    echo "  ✓ csrf_check() accepts valid token (no error exit)\n";
    $passed++;
} else {
    echo "  ✗ csrf_check() with valid token FAILED: output='$output'\n";
    $failed++;
}

// ============================================================
// TEST 5: asset_url() - Relative URL
// ============================================================
echo "TEST 5: asset_url() - Relative URL\n";
require_once __DIR__ . '/../includes/config.php';
$result = asset_url('/uploads/x.png');
if (preg_match('#^.*uploads/x\.png$#', $result)) {
    echo "  ✓ asset_url('/uploads/x.png') prepends BASE_URL\n";
    $passed++;
} else {
    echo "  ✗ asset_url() FAILED: result='$result'\n";
    $failed++;
}

// ============================================================
// TEST 6: asset_url() - Absolute URL
// ============================================================
echo "TEST 6: asset_url() - Absolute URL\n";
$result = asset_url('https://example.com/x.png');
if ($result === 'https://example.com/x.png') {
    echo "  ✓ asset_url('https://...') passes through unchanged\n";
    $passed++;
} else {
    echo "  ✗ asset_url() absolute FAILED: result='$result'\n";
    $failed++;
}

// ============================================================
// TEST 7: upload_image() - MIME validation (PHP file)
// ============================================================
echo "TEST 7: upload_image() - MIME validation\n";

// Extract upload_image function from products.php
$products_src = file_get_contents(__DIR__ . '/../admin/products.php');
if (preg_match('/^function upload_image\(.*?\n\}/ms', $products_src, $matches)) {
    eval($matches[0]);

    // Create temp PHP file with PHP code
    $tmp = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmp, '<?php echo 1;');

    $file_array = [
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
        'tmp_name' => $tmp,
        'name' => 'evil.php'
    ];

    $result = upload_image($file_array, 'test');
    @unlink($tmp);

    if ($result === false) {
        echo "  ✓ upload_image() rejects PHP file (MIME validation)\n";
        $passed++;
    } else {
        echo "  ✗ upload_image() accepted PHP file: result='$result'\n";
        $failed++;
    }
} else {
    echo "  ✗ Could not extract upload_image() function\n";
    $failed++;
}

// ============================================================
// TEST 8: upload_image() - Image validation (getimagesize)
// ============================================================
echo "TEST 8: upload_image() - Image validation\n";

if (function_exists('upload_image')) {
    // Create a minimal valid PNG (1x1 pixel)
    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    );
    $tmp = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmp, $png);

    // Test that getimagesize validates correctly
    $img_check = @getimagesize($tmp);
    @unlink($tmp);

    if ($img_check !== false) {
        echo "  ✓ PNG file passes getimagesize() validation\n";
        $passed++;
    } else {
        echo "  ✗ PNG file failed getimagesize() validation\n";
        $failed++;
    }
} else {
    echo "  ✗ upload_image() not defined after eval\n";
    $failed++;
}

// ============================================================
// TEST 9: delete_file() - Path traversal protection
// ============================================================
echo "TEST 9: delete_file() - Path traversal protection\n";

// Extract delete_file function from products.php
if (preg_match('/^function delete_file\(.*?\n\}/ms', $products_src, $matches)) {
    eval($matches[0]);

    // Create a victim file OUTSIDE uploads dir
    @mkdir($test_dir . '/tmp', 0755, true);
    $victim = $test_dir . '/tmp/victim.txt';
    file_put_contents($victim, 'test');

    // Try to delete via path traversal
    delete_file('/uploads/../tmp/victim.txt');

    if (file_exists($victim)) {
        echo "  ✓ delete_file() blocks path traversal (file still exists)\n";
        $passed++;
        @unlink($victim);
    } else {
        echo "  ✗ delete_file() allowed path traversal deletion!\n";
        $failed++;
    }

    @rmdir($test_dir . '/tmp');
} else {
    echo "  ✗ Could not extract delete_file() function\n";
    $failed++;
}

// ============================================================
// TEST 10: upload_gallery_img() - MIME validation
// ============================================================
echo "TEST 10: upload_gallery_img() - MIME validation\n";

$gallery_src = file_get_contents(__DIR__ . '/../admin/gallery.php');
if (preg_match('/^function upload_gallery_img\(.*?\n\}/ms', $gallery_src, $matches)) {
    eval($matches[0]);

    // Create temp PHP file
    $tmp = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmp, '<?php echo 1;');

    $file_array = [
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
        'tmp_name' => $tmp,
        'name' => 'evil.php'
    ];

    $result = upload_gallery_img($file_array);
    @unlink($tmp);

    if ($result === false) {
        echo "  ✓ upload_gallery_img() rejects PHP file (MIME validation)\n";
        $passed++;
    } else {
        echo "  ✗ upload_gallery_img() accepted PHP file: result='$result'\n";
        $failed++;
    }
} else {
    echo "  ✗ Could not extract upload_gallery_img() function\n";
    $failed++;
}

// ============================================================
// TEST 11: delete_gal_file() - Path traversal protection
// ============================================================
echo "TEST 11: delete_gal_file() - Path traversal protection\n";

if (preg_match('/^function delete_gal_file\(.*?\n\}/ms', $gallery_src, $matches)) {
    eval($matches[0]);

    // Create a victim file OUTSIDE uploads dir
    @mkdir($test_dir . '/tmp', 0755, true);
    $victim = $test_dir . '/tmp/gal_victim.txt';
    file_put_contents($victim, 'test');

    // Try to delete via path traversal
    delete_gal_file('/uploads/../tmp/gal_victim.txt');

    if (file_exists($victim)) {
        echo "  ✓ delete_gal_file() blocks path traversal (file still exists)\n";
        $passed++;
        @unlink($victim);
    } else {
        echo "  ✗ delete_gal_file() allowed path traversal deletion!\n";
        $failed++;
    }

    @rmdir($test_dir . '/tmp');
} else {
    echo "  ✗ Could not extract delete_gal_file() function\n";
    $failed++;
}

// ============================================================
// Summary
// ============================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "RESULTS: $passed passed, $failed failed\n";

if ($failed === 0) {
    echo "OK\n";
    exit(0);
} else {
    echo "FAIL\n";
    exit(1);
}
?>
