<?php
session_start();
include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

$query = "DELETE FROM pasien WHERE id = ?";
$stmt = $db->prepare($query);

if($stmt->execute([$id])){
    $_SESSION['success_message'] = "Pasien berhasil dihapus!";
} else {
    $_SESSION['error_message'] = "Gagal menghapus pasien.";
}

header("Location: index.php");
exit();
?>