<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

// Get active doctors
$queryDokter = "SELECT id, nama, spesialisasi FROM dokter WHERE status = 'Aktif' ORDER BY nama";
$stmtDokter = $db->prepare($queryDokter);
$stmtDokter->execute();

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

if($_POST){
    $dokter_id = $_POST['dokter_id'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    
    // Check for schedule conflict
    $queryCheck = "SELECT COUNT(*) as total FROM jadwal_praktik 
                   WHERE dokter_id = ? AND hari = ? 
                   AND ((jam_mulai <= ? AND jam_selesai > ?) 
                   OR (jam_mulai < ? AND jam_selesai >= ?))";
    $stmtCheck = $db->prepare($queryCheck);
    $stmtCheck->execute([$dokter_id, $hari, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai]);
    $conflict = $stmtCheck->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    
    if($conflict) {
        $error = "Dokter sudah memiliki jadwal pada hari dan jam tersebut!";
    } else {
        $query = "INSERT INTO jadwal_praktik SET dokter_id=?, hari=?, jam_mulai=?, jam_selesai=?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$dokter_id, $hari, $jam_mulai, $jam_selesai])){
            $_SESSION['success_message'] = "Jadwal praktik berhasil ditambahkan!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Gagal menambahkan jadwal praktik.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal - Rumah Sakit Sehat</title>
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
            <h2><i class="fas fa-calendar-plus me-2"></i>Tambah Jadwal Praktik</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pilih Dokter</label>
                            <select class="form-select" name="dokter_id" required>
                                <option value="">Pilih Dokter</option>
                                <?php while ($dokter = $stmtDokter->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $dokter['id']; ?>">
                                        <?php echo $dokter['nama']; ?> - <?php echo $dokter['spesialisasi']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hari</label>
                            <select class="form-select" name="hari" required>
                                <option value="">Pilih Hari</option>
                                <?php foreach($days as $day): ?>
                                    <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" name="jam_mulai" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" name="jam_selesai" required>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Pastikan jam selesai lebih besar dari jam mulai. Sistem akan mengecek konflik jadwal secara otomatis.
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>