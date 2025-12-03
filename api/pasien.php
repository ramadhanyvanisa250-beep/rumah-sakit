<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get all patients or single patient
        if(isset($_GET['id'])) {
            $query = "SELECT * FROM pasien WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query = "SELECT * FROM pasien ORDER BY id DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($result);
        break;
        
    case 'POST':
        // Create new patient
        $data = json_decode(file_get_contents("php://input"));
        $query = "INSERT INTO pasien SET nama=?, alamat=?, no_telepon=?, jenis_kelamin=?, tanggal_lahir=?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->nama, $data->alamat, $data->no_telepon, $data->jenis_kelamin, $data->tanggal_lahir])) {
            echo json_encode(["message" => "Pasien berhasil ditambahkan"]);
        } else {
            echo json_encode(["message" => "Gagal menambahkan pasien"]);
        }
        break;
        
    case 'PUT':
        // Update patient
        $data = json_decode(file_get_contents("php://input"));
        $query = "UPDATE pasien SET nama=?, alamat=?, no_telepon=?, jenis_kelamin=?, tanggal_lahir=? WHERE id=?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->nama, $data->alamat, $data->no_telepon, $data->jenis_kelamin, $data->tanggal_lahir, $data->id])) {
            echo json_encode(["message" => "Pasien berhasil diperbarui"]);
        } else {
            echo json_encode(["message" => "Gagal memperbarui pasien"]);
        }
        break;
        
    case 'DELETE':
        // Delete patient
        $data = json_decode(file_get_contents("php://input"));
        $query = "DELETE FROM pasien WHERE id=?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->id])) {
            echo json_encode(["message" => "Pasien berhasil dihapus"]);
        } else {
            echo json_encode(["message" => "Gagal menghapus pasien"]);
        }
        break;
}
?>