<?php
/**
 * ESM wrapper for the vendored zxcvbn UMD bundle.
 *
 * The raw bundle is a UMD module that assigns a global (window.zxcvbn) when no
 * AMD/CommonJS loader is present. Elgg 7 only registers `.mjs` views in the
 * importmap, so we inline the bundle here and re-export the resolved global as
 * the default export (mirrors how Elgg core wraps jQuery in elgg/jquery.mjs.php).
 */

echo elgg_view('zxcvbn/zxcvbn.js');
?>

export default window.zxcvbn;
