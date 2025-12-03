<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

// Build query dengan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$spesialisasi = isset($_GET['spesialisasi']) ? $_GET['spesialisasi'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM dokter WHERE 1=1";
$params = [];

if(!empty($search)) {
    $query .= " AND nama LIKE ?";
    $params[] = "%$search%";
}

if(!empty($spesialisasi)) {
    $query .= " AND spesialisasi = ?";
    $params[] = $spesialisasi;
}

if(!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY nama ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);

// Get unique spesialisasi for filter
$querySpesialisasi = "SELECT DISTINCT spesialisasi FROM dokter ORDER BY spesialisasi";
$stmtSpesialisasi = $db->prepare($querySpesialisasi);
$stmtSpesialisasi->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dokter - Rumah Sakit Sehat</title>
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
            <h2><i class="fas fa-user-md me-2"></i>Data Dokter</h2>
            <div class="d-flex gap-2">
                <a href="../export/excel.php?type=dokter" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Export
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Dokter
                </a>
            </div>
        </div>

        <?php
        if(isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    '.$_SESSION['success_message'].'
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <!-- Search and Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cari Nama Dokter</label>
                        <input type="text" class="form-control" name="search" value="<?php echo $search; ?>" placeholder="Cari nama dokter...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Spesialisasi</label>
                        <select class="form-select" name="spesialisasi">
                            <option value="">Semua Spesialisasi</option>
                            <?php while ($row = $stmtSpesialisasi->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $row['spesialisasi']; ?>" <?php echo ($spesialisasi == $row['spesialisasi']) ? 'selected' : ''; ?>>
                                    <?php echo $row['spesialisasi']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="Aktif" <?php echo ($status == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Tidak Aktif" <?php echo ($status == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama Dokter</th>
                                <th>Spesialisasi</th>
                                <th>No. Telepon</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['spesialisasi']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'Aktif' ? 'success' : 'secondary'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus dokter?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="../jadwal/index.php?dokter_id=<?php echo $row['id']; ?>" class="btn btn-info">
                                            <i class="fas fa-calendar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>