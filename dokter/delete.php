<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

// Cek apakah dokter memiliki jadwal
$queryCheck = "SELECT COUNT(*) as total FROM jadwal_praktik WHERE dokter_id = ?";
$stmtCheck = $db->prepare($queryCheck);
$stmtCheck->execute([$id]);
$hasSchedule = $stmtCheck->fetch(PDO::FETCH_ASSOC)['total'] > 0;

if($hasSchedule) {
    $_SESSION['error_message'] = "Tidak dapat menghapus dokter karena masih memiliki jadwal praktik. Hapus jadwal terlebih dahulu.";
} else {
    $query = "DELETE FROM dokter WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if($stmt->execute([$id])){
        $_SESSION['success_message'] = "Dokter berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus dokter.";
    }
}

header("Location: index.php");
exit();
?>