<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

if($_POST){
    $database = new Database();
    $db = $database->getConnection();
    
    $nama = $_POST['nama'];
    $spesialisasi = $_POST['spesialisasi'];
    $no_telepon = $_POST['no_telepon'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    
    $query = "INSERT INTO dokter SET nama=?, spesialisasi=?, no_telepon=?, email=?, status=?";
    $stmt = $db->prepare($query);
    
    if($stmt->execute([$nama, $spesialisasi, $no_telepon, $email, $status])){
        $_SESSION['success_message'] = "Dokter berhasil ditambahkan!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal menambahkan dokter.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dokter - Rumah Sakit Sehat</title>
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
            <h2><i class="fas fa-user-plus me-2"></i>Tambah Dokter Baru</h2>
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
                            <label class="form-label">Nama Lengkap Dokter</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spesialisasi</label>
                            <select class="form-select" name="spesialisasi" required>
                                <option value="">Pilih Spesialisasi</option>
                                <option value="Umum">Dokter Umum</option>
                                <option value="Anak">Dokter Anak</option>
                                <option value="Penyakit Dalam">Penyakit Dalam</option>
                                <option value="Bedah">Bedah</option>
                                <option value="Kandungan">Kandungan</option>
                                <option value="Kulit dan Kelamin">Kulit dan Kelamin</option>
                                <option value="Mata">Mata</option>
                                <option value="THT">THT</option>
                                <option value="Jantung">Jantung</option>
                                <option value="Saraf">Saraf</option>
                                <option value="Psikiatri">Psikiatri</option>
                                <option value="Radiologi">Radiologi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="no_telepon" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Dokter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>