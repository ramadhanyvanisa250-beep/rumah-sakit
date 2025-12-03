<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];
$query = "SELECT * FROM pasien WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$pasien = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$pasien) {
    $_SESSION['error_message'] = "Pasien tidak ditemukan!";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasien - Rumah Sakit Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-hospital"></i> Rumah Sakit Sehat
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user me-2"></i>Detail Pasien</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informasi Pasien</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>ID Pasien:</strong>
                                <p class="text-muted"><?php echo $pasien['id']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal Daftar:</strong>
                                <p class="text-muted"><?php echo date('d/m/Y H:i', strtotime($pasien['tanggal_daftar'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Nama Lengkap:</strong>
                                <p class="text-muted"><?php echo htmlspecialchars($pasien['nama']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Jenis Kelamin:</strong>
                                <p>
                                    <span class="badge bg-<?php echo $pasien['jenis_kelamin'] == 'Laki-laki' ? 'primary' : 'danger'; ?>">
                                        <?php echo $pasien['jenis_kelamin']; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Tanggal Lahir:</strong>
                                <p class="text-muted"><?php echo date('d/m/Y', strtotime($pasien['tanggal_lahir'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Umur:</strong>
                                <?php
                                $birthDate = new DateTime($pasien['tanggal_lahir']);
                                $today = new DateTime();
                                $age = $today->diff($birthDate)->y;
                                ?>
                                <p class="text-muted"><?php echo $age; ?> tahun</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Alamat:</strong>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($pasien['alamat'])); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>No. Telepon:</strong>
                            <p class="text-muted">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($pasien['no_telepon']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="edit.php?id=<?php echo $pasien['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Data
                        </a>
                        <a href="delete.php?id=<?php echo $pasien['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('Yakin ingin menghapus pasien ini?')">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>ID Pasien</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3" id="qrcode"></div>
                        <small class="text-muted">Scan untuk melihat detail pasien</small>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistik</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Status:</span>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Kunjungan:</span>
                            <span class="badge bg-primary">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Terakhir Diperbarui:</span>
                            <span class="text-muted"><?php echo date('d/m/Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "ID Pasien: <?php echo $pasien['id']; ?>\nNama: <?php echo $pasien['nama']; ?>",
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>