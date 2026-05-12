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
     // ================= ORDER NUMBER =================
    // Used in: confirmation.php, myOrder.php, myOrders.php, feedback.php
    function orderNumber(int $id): string {
        return 'FU-' . str_pad($id, 5, '0', STR_PAD_LEFT);
    }

    // ================= RENDER STARS =================
    // Used in: product.php, category.php, myOrder.php, admin/feedback.php
    function renderStars(float $rating, string $size = ''): string {
        $fa_size = $size ? " $size" : '';
        $html    = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= "<i class=\"fa-solid fa-star{$fa_size} star-filled\"></i>";
            } elseif ($rating >= $i - 0.5) {
                $html .= "<i class=\"fa-solid fa-star-half-stroke{$fa_size} star-filled\"></i>";
            } else {
                $html .= "<i class=\"fa-regular fa-star{$fa_size} star-empty\"></i>";
            }
        }
        return $html;
    }

    // ================= STATUS BADGES =================
    // Used in: myOrders.php, myOrder.php, admin/orders.php, admin/order.php
    function orderStatusBadge(string $status): string {
        $class = match($status) {
            'pending'   => 'bg-warning text-dark',
            'shipped'   => 'bg-info text-dark',
            'completed' => 'bg-success',
            default     => 'bg-secondary'
        };
        return "<span class=\"badge {$class}\">" . ucfirst($status) . "</span>";
    }

    // Used in: myOrder.php, admin/order.php, admin/feedback.php
    function deliveryStatusBadge(?string $status): string {
        if (!$status) return "<span class=\"badge bg-secondary\">Pending</span>";
        $class = match($status) {
            'shipped'   => 'bg-info text-dark',
            'delivered' => 'bg-success',
            'failed'    => 'bg-danger',
            default     => 'bg-secondary'
        };
        return "<span class=\"badge {$class}\">" . ucfirst($status) . "</span>";
    }
