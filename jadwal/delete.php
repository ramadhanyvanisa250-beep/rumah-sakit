<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

$query = "DELETE FROM jadwal_praktik WHERE id = ?";
$stmt = $db->prepare($query);

if($stmt->execute([$id])){
    $_SESSION['success_message'] = "Jadwal praktik berhasil dihapus!";
} else {
    $_SESSION['error_message'] = "Gagal menghapus jadwal praktik.";
}

header("Location: index.php");
exit();
?>