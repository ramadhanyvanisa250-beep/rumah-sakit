<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];
$query = "SELECT * FROM dokter WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$dokter = $stmt->fetch(PDO::FETCH_ASSOC);

if($_POST){
    $nama = $_POST['nama'];
    $spesialisasi = $_POST['spesialisasi'];
    $no_telepon = $_POST['no_telepon'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    
    $query = "UPDATE dokter SET nama=?, spesialisasi=?, no_telepon=?, email=?, status=? WHERE id=?";
    $stmt = $db->prepare($query);
    
    if($stmt->execute([$nama, $spesialisasi, $no_telepon, $email, $status, $id])){
        $_SESSION['success_message'] = "Data dokter berhasil diperbarui!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal memperbarui data dokter.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokter - Rumah Sakit Sehat</title>
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
            <h2><i class="fas fa-edit me-2"></i>Edit Data Dokter</h2>
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
                            <input type="text" class="form-control" name="nama" value="<?php echo $dokter['nama']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spesialisasi</label>
                            <select class="form-select" name="spesialisasi" required>
                                <option value="Umum" <?php echo $dokter['spesialisasi'] == 'Umum' ? 'selected' : ''; ?>>Dokter Umum</option>
                                <option value="Anak" <?php echo $dokter['spesialisasi'] == 'Anak' ? 'selected' : ''; ?>>Dokter Anak</option>
                                <option value="Penyakit Dalam" <?php echo $dokter['spesialisasi'] == 'Penyakit Dalam' ? 'selected' : ''; ?>>Penyakit Dalam</option>
                                <option value="Bedah" <?php echo $dokter['spesialisasi'] == 'Bedah' ? 'selected' : ''; ?>>Bedah</option>
                                <option value="Kandungan" <?php echo $dokter['spesialisasi'] == 'Kandungan' ? 'selected' : ''; ?>>Kandungan</option>
                                <option value="Kulit dan Kelamin" <?php echo $dokter['spesialisasi'] == 'Kulit dan Kelamin' ? 'selected' : ''; ?>>Kulit dan Kelamin</option>
                                <option value="Mata" <?php echo $dokter['spesialisasi'] == 'Mata' ? 'selected' : ''; ?>>Mata</option>
                                <option value="THT" <?php echo $dokter['spesialisasi'] == 'THT' ? 'selected' : ''; ?>>THT</option>
                                <option value="Jantung" <?php echo $dokter['spesialisasi'] == 'Jantung' ? 'selected' : ''; ?>>Jantung</option>
                                <option value="Saraf" <?php echo $dokter['spesialisasi'] == 'Saraf' ? 'selected' : ''; ?>>Saraf</option>
                                <option value="Psikiatri" <?php echo $dokter['spesialisasi'] == 'Psikiatri' ? 'selected' : ''; ?>>Psikiatri</option>
                                <option value="Radiologi" <?php echo $dokter['spesialisasi'] == 'Radiologi' ? 'selected' : ''; ?>>Radiologi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="no_telepon" value="<?php echo $dokter['no_telepon']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo $dokter['email']; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Aktif" <?php echo $dokter['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Tidak Aktif" <?php echo $dokter['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Perbarui Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>