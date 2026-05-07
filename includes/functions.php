<?php
/**
 * Public functions — shared by all pages (public + admin)
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */

// ================= CUSTOMER AUTH =================
function isCustomer() {
    return isset($_SESSION['customer_id']) && isset($_SESSION['customer_role']) && $_SESSION['customer_role'] === 0;
}

function requireCustomer() {
    if (!isCustomer()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: auth.php");
        exit;
    }
}

// ================= PAGINATION =================
function paginate($total, $perPage = 10) {
    $page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $totalPages = ceil($total / $perPage);
    $offset     = ($page - 1) * $perPage;

    return [
        'page'       => $page,
        'perPage'    => $perPage,
        'totalPages' => $totalPages,
        'offset'     => $offset
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
