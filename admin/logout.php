<?php
    /**
     *
     * @author Lana (Svetlana Muraveckaja-Odincova)
     */
    require_once dirname(__DIR__).'/includes/init.php';
    session_regenerate_id(true);
    $_SESSION = [];
    session_destroy();
    redirect('../index.php');
?>
