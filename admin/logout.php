<?php
/**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
session_start();
include('../includes/connection.php');
include('./includes/header.php');
$_SESSION = [];
session_destroy();
header("Location: ../index.php")
?>
