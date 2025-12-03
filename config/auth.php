<?php
function checkAuth() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function checkRole($allowedRoles) {
    if(!in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: ../index.php");
        exit();
    }
}

function logout() {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}
?>