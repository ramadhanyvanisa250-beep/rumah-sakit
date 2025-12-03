<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

// Query data pasien
$query = "SELECT * FROM pasien ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();

// Header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="data_pasien_'.date('Y-m-d').'.xls"');
header('Cache-Control: max-age=0');

echo "Data Pasien Rumah Sakit Sehat\n\n";
echo "Tanggal Export: " . date('d/m/Y H:i') . "\n\n";

echo "ID\tNama\tAlamat\tNo Telepon\tJenis Kelamin\tTanggal Lahir\tTanggal Daftar\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id'] . "\t";
    echo $row['nama'] . "\t";
    echo $row['alamat'] . "\t";
    echo $row['no_telepon'] . "\t";
    echo $row['jenis_kelamin'] . "\t";
    echo date('d/m/Y', strtotime($row['tanggal_lahir'])) . "\t";
    echo date('d/m/Y H:i', strtotime($row['tanggal_daftar'])) . "\n";
}
exit();
?>