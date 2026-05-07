<?php
/**
 * Admin functions — admin-specific only
 * paginate, renderPagination, isCustomer, requireCustomer are in includes/functions.php
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
 
// ================= ADMIN AUTH =================
function isAdmin() {
    return isset($_SESSION['admin']);
}
 
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit;
    }
}
 
// ================= IMAGE UPLOAD =================
function uploadImage(array $file, string $uploadDir): ?string {
    if (empty($file['name'])) {
        return null;
    }
 
    if (!is_writable($uploadDir)) {
        throw new Exception("Upload folder is not writable");
    }
 
    $tmp = $file['tmp_name'];
 
    // 1. Extension
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
 
    if (!in_array($ext, $allowedExt)) {
        throw new Exception("Invalid file extension");
    }
 
    // 2. MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);
 
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
 
    if (!in_array($mime, $allowedMime)) {
        throw new Exception("Invalid image type");
    }
 
    // 3. Real image check
    if (@getimagesize($tmp) === false) {
        throw new Exception("File is not a valid image");
    }
 
    // 4. Safe filename
    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
 
    // 5. Move
    if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
        throw new Exception("Upload failed");
    }
 
    return $filename;
}
 
// ================= MISC =================
function parseMultiInput($input) {
    $items = explode('|', $input);
    $items = array_map(fn($v) => trim($v), $items);
    $items = array_filter($items);
    $items = array_unique($items);
 
    return $items;
}