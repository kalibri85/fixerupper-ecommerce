<?php
/**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
require_once __DIR__ . '/includes/init.php';

if (isAdmin()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
