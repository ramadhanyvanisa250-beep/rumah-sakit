<?php
session_start();
include_once "../config/auth.php";

checkAuth();
checkRole(['admin']);

if(isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if(file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        $_SESSION['error_message'] = "File tidak ditemukan.";
        header("Location: list.php");
        exit();
    }
}
?>