<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function require_leader() {
    if(empty($_SESSION['group_id'])) {
        header('Location: group_login.php'); exit;
    }
}
function require_admin() {
    if(empty($_SESSION['admin'])) {
        header('Location: admin/index.php'); exit;
    }
}
?>