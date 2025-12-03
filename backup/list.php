<?php
session_start();
include_once "../config/auth.php";

checkAuth();
checkRole(['admin']);

$backup_dir = __DIR__ . '/backups/';
$backups = [];

if (file_exists($backup_dir)) {
    $files = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $file_path = $backup_dir . $file;
            $backups[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'date' => date('Y-m-d H:i:s', filemtime($file_path)),
                'path' => $file_path
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Backup - Rumah Sakit Sehat</title>
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
            <h2><i class="fas fa-database me-2"></i>List Backup Database</h2>
            <div>
                <a href="backup_manual.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Backup Baru
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <?php if(empty($backups)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-database fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada backup</h5>
                        <p class="text-muted">Buat backup pertama Anda</p>
                        <a href="backup_manual.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Buat Backup
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($backups as $backup): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file-archive text-primary me-2"></i>
                                        <?php echo $backup['name']; ?>
                                    </td>
                                    <td><?php echo round($backup['size'] / 1024, 2); ?> KB</td>
                                    <td><?php echo $backup['date']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="download.php?file=<?php echo urlencode($backup['name']); ?>" 
                                               class="btn btn-success" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="restore.php?file=<?php echo urlencode($backup['name']); ?>" 
                                               class="btn btn-warning" 
                                               onclick="return confirm('Restore backup ini? Semua data saat ini akan diganti.')"
                                               title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                            <a href="delete_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Hapus backup ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>