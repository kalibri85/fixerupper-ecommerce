<?php
    function isAdmin() {
        return isset($_SESSION['admin']);
    }

    function requireAdmin() {
        if (!isAdmin()) {
            header("Location: login.php");
            exit;
        }
    }
    function parseMultiInput($input) {
        $items = explode('|', $input);

        $items = array_map(fn($v) => trim($v), $items);
        $items = array_filter($items);
        $items = array_unique($items);

        return $items;
    }
    function paginate($total, $perPage = 10) {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        return [
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'offset' => $offset
        ];
    }
    function renderPagination($totalPages, $currentPage, $baseUrl) {
        if ($totalPages <= 1) return;

        echo '<nav><ul class="pagination justify-content-center pt-2 pb-2">';

        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i == $currentPage ? 'active' : '';
            echo "<li class='page-item $active'>
                    <a class='page-link' href='{$baseUrl}page=$i'>$i</a>
                </li>";
        }

        echo '</ul></nav>';
    }
    function uploadImage(array $file, string $uploadDir): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        if (!is_writable($uploadDir)) {
            throw new Exception("Upload folder is not writable");
        }

        $tmp = $file['tmp_name'];

        // 1. extension
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            throw new Exception("Invalid file extension");
        }

        // 2. MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowedMime)) {
            throw new Exception("Invalid image type");
        }

        // 3. real image check
        if (@getimagesize($tmp) === false) {
            throw new Exception("File is not a valid image");
        }

        // 4. safe filename
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        // 5. move
        if (!move_uploaded_file($tmp, $uploadDir . $filename)) {
            throw new Exception("Upload failed");
        }

        return $filename;
    }
?>