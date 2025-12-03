<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

// Filter
$dokter_id = isset($_GET['dokter_id']) ? $_GET['dokter_id'] : '';
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

$query = "SELECT j.*, d.nama as nama_dokter, d.spesialisasi 
          FROM jadwal_praktik j 
          JOIN dokter d ON j.dokter_id = d.id 
          WHERE 1=1";
$params = [];

if(!empty($dokter_id)) {
    $query .= " AND j.dokter_id = ?";
    $params[] = $dokter_id;
}

if(!empty($hari)) {
    $query .= " AND j.hari = ?";
    $params[] = $hari;
}

$query .= " ORDER BY 
            FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
            j.jam_mulai";
$stmt = $db->prepare($query);
$stmt->execute($params);

// Get data dokter untuk filter
$queryDokter = "SELECT id, nama FROM dokter WHERE status = 'Aktif' ORDER BY nama";
$stmtDokter = $db->prepare($queryDokter);
$stmtDokter->execute();

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Praktik - Rumah Sakit Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-hospital"></i> Rumah Sakit Sehat
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo $_SESSION['nama_lengkap']; ?>
                </span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Jadwal Praktik Dokter</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Jadwal
            </a>
        </div>

        <?php
        if(isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    '.$_SESSION['success_message'].'
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            unset($_SESSION['success_message']);
        }
        if(isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    '.$_SESSION['error_message'].'
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Search and Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Pilih Dokter</label>
                        <select class="form-select" name="dokter_id">
                            <option value="">Semua Dokter</option>
                            <?php while ($dokter = $stmtDokter->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $dokter['id']; ?>" <?php echo ($dokter_id == $dokter['id']) ? 'selected' : ''; ?>>
                                    <?php echo $dokter['nama']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Hari</label>
                        <select class="form-select" name="hari">
                            <option value="">Semua Hari</option>
                            <?php foreach($days as $day): ?>
                                <option value="<?php echo $day; ?>" <?php echo ($hari == $day) ? 'selected' : ''; ?>>
                                    <?php echo $day; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <?php if($stmt->rowCount() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Hari</th>
                                <th>Dokter</th>
                                <th>Spesialisasi</th>
                                <th>Jam Praktik</th>
                                <th>Durasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                $jam_mulai = date('H:i', strtotime($row['jam_mulai']));
                                $jam_selesai = date('H:i', strtotime($row['jam_selesai']));
                                $durasi = (strtotime($row['jam_selesai']) - strtotime($row['jam_mulai'])) / 3600;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['hari']; ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_dokter']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $row['spesialisasi']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $jam_mulai; ?></span> - 
                                    <span class="badge bg-secondary"><?php echo $jam_selesai; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo $durasi; ?> jam</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus jadwal?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada jadwal praktik</h5>
                    <p class="text-muted">Silakan tambah jadwal praktik untuk dokter.</p>
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Jadwal Pertama
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>