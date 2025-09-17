<?php
session_start();
require_once 'db_actions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

exportStudentsToJson();
?>
