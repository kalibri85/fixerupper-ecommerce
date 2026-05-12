<?php
    /**
     * Customer Logout
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once __DIR__ . '/includes/init.php';

    // Only destroy customer session data — cart stays
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['customer_role']);
    unset($_SESSION['redirect_after_login']);

    session_regenerate_id(true);

    redirect('index.php');
?>    