<?php
session_start();
include_once "../config/database.php";
include_once "../config/auth.php";

checkAuth();

$database = new Database();
$db = $database->getConnection();

// Build query dengan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$jenis_kelamin = isset($_GET['jenis_kelamin']) ? $_GET['jenis_kelamin'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// Mulai query dengan WHERE 1=1 untuk memudahkan penambahan kondisi
$query = "SELECT * FROM pasien WHERE 1=1";
$params = [];

if(!empty($search)) {
    $query .= " AND nama LIKE ?";
    $params[] = "%$search%";
}

if(!empty($jenis_kelamin)) {
    $query .= " AND jenis_kelamin = ?";
    $params[] = $jenis_kelamin;
}

// Sorting
switch($sort) {
    case 'terlama':
        $query .= " ORDER BY id ASC";
        break;
    case 'nama_asc':
        $query .= " ORDER BY nama ASC";
        break;
    case 'nama_desc':
        $query .= " ORDER BY nama DESC";
        break;
    default:
        $query .= " ORDER BY id DESC";
        break;
}

$stmt = $db->prepare($query);
$stmt->execute($params);

// Hitung total data
$totalQuery = "SELECT COUNT(*) as total FROM pasien WHERE 1=1";
$totalParams = [];
if(!empty($search)) {
    $totalQuery .= " AND nama LIKE ?";
    $totalParams[] = "%$search%";
}
if(!empty($jenis_kelamin)) {
    $totalQuery .= " AND jenis_kelamin = ?";
    $totalParams[] = $jenis_kelamin;
}

$totalStmt = $db->prepare($totalQuery);
$totalStmt->execute($totalParams);
$totalData = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien - Rumah Sakit Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-active {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
        }
        .filter-badge {
            font-size: 0.8rem;
        }
    </style>
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
            <h2><i class="fas fa-users me-2"></i>Data Pasien</h2>
            <div class="d-flex gap-2">
                <a href="../export/excel.php?type=pasien" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Pasien
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
                        <label class="form-label">Cari Nama</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Cari nama pasien...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin">
                            <option value="">Semua</option>
                            <option value="Laki-laki" <?php echo ($jenis_kelamin == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($jenis_kelamin == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urutkan</label>
                        <select class="form-select" name="sort">
                            <option value="terbaru" <?php echo ($sort == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="terlama" <?php echo ($sort == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                            <option value="nama_asc" <?php echo ($sort == 'nama_asc') ? 'selected' : ''; ?>>Nama A-Z</option>
                            <option value="nama_desc" <?php echo ($sort == 'nama_desc') ? 'selected' : ''; ?>>Nama Z-A</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
                
                <!-- Tampilkan filter aktif -->
                <?php if(!empty($search) || !empty($jenis_kelamin)): ?>
                <div class="mt-3">
                    <small class="text-muted">Filter Aktif:</small>
                    <?php if(!empty($search)): ?>
                        <span class="badge bg-info filter-badge me-2">
                            <i class="fas fa-search me-1"></i>Cari: "<?php echo htmlspecialchars($search); ?>"
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['search' => ''])); ?>" class="text-white ms-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($jenis_kelamin)): ?>
                        <span class="badge bg-success filter-badge me-2">
                            <i class="fas fa-venus-mars me-1"></i><?php echo $jenis_kelamin; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['jenis_kelamin' => ''])); ?>" class="text-white ms-1">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    <a href="index.php" class="badge bg-danger filter-badge text-decoration-none">
                        <i class="fas fa-times-circle me-1"></i>Reset Semua Filter
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Hasil Filter -->
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle me-2"></i>
                Menampilkan <strong><?php echo $stmt->rowCount(); ?></strong> dari <strong><?php echo $totalData; ?></strong> pasien
                <?php if(!empty($search)): ?>
                    untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
            </div>
            <div>
                <?php if($stmt->rowCount() == 0): ?>
                    <a href="index.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-redo me-1"></i>Tampilkan Semua
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <?php if($stmt->rowCount() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>No. Telepon</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                $bg_class = '';
                                if(!empty($search) && stripos($row['nama'], $search) !== false) {
                                    $bg_class = 'table-info';
                                }
                            ?>
                            <tr class="<?php echo $bg_class; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if(!empty($search)): ?>
                                        <?php echo highlightSearchTerm($row['nama'], $search); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['jenis_kelamin'] == 'Laki-laki' ? 'primary' : 'danger'; ?>">
                                        <i class="fas fa-<?php echo $row['jenis_kelamin'] == 'Laki-laki' ? 'male' : 'female'; ?> me-1"></i>
                                        <?php echo $row['jenis_kelamin']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo date('d/m/Y', strtotime($row['tanggal_lahir'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($row['tanggal_daftar'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus pasien ini?')"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                            $counter++;
                            endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($totalData > 10): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data pasien</h5>
                    <p class="text-muted mb-4">
                        <?php if(!empty($search) || !empty($jenis_kelamin)): ?>
                            Tidak ditemukan pasien dengan kriteria pencarian Anda.
                        <?php else: ?>
                            Belum ada data pasien yang terdaftar.
                        <?php endif; ?>
                    </p>
                    <?php if(!empty($search) || !empty($jenis_kelamin)): ?>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Tampilkan Semua Pasien
                        </a>
                    <?php else: ?>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Pasien Pertama
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form saat select berubah (opsional)
        document.querySelectorAll('select[name="sort"], select[name="jenis_kelamin"]').forEach(function(select) {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Reset form filter
        document.getElementById('resetFilter').addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>
