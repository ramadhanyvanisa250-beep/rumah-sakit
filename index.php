<?php
session_start();
include_once "config/database.php";

// Redirect ke login jika belum login
if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Hitung statistik untuk dashboard
$query = "SELECT COUNT(*) as total FROM pasien";
$stmt = $db->prepare($query);
$stmt->execute();
$total_pasien = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM dokter WHERE status = 'Aktif'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_dokter = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Statistik jenis kelamin pasien
$query = "SELECT jenis_kelamin, COUNT(*) as jumlah FROM pasien GROUP BY jenis_kelamin";
$stmt = $db->prepare($query);
$stmt->execute();
$gender_stats = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gender_stats[$row['jenis_kelamin']] = $row['jumlah'];
}

// Statistik pendaftaran bulan ini
$query = "SELECT 
            WEEK(tanggal_daftar) as minggu,
            COUNT(*) as jumlah 
          FROM pasien 
          WHERE MONTH(tanggal_daftar) = MONTH(CURRENT_DATE()) 
          AND YEAR(tanggal_daftar) = YEAR(CURRENT_DATE())
          GROUP BY WEEK(tanggal_daftar)";
$stmt = $db->prepare($query);
$stmt->execute();
$weekly_stats = [0, 0, 0, 0, 0];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $week = $row['minggu'] - date('W') + 4; // Adjust untuk mendapatkan index yang benar
    if($week >= 0 && $week < 5) {
        $weekly_stats[$week] = $row['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Rumah Sakit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #212529;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1586773860418-d37222d8fce3?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover;
            opacity: 0.1;
        }

        .min-vh-75 {
            min-height: 75vh;
        }

        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-card.success {
            border-left-color: var(--success);
        }

        .stat-card.warning {
            border-left-color: var(--warning);
        }

        .stat-card.info {
            border-left-color: var(--info);
        }

        .stat-card .card-body {
            text-align: center;
            padding: 2rem 1rem;
        }

        .stat-card .card-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .stat-card .card-text {
            font-size: 1.1rem;
            color: var(--secondary);
            font-weight: 500;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .quick-action-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            color: inherit;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            border-left: 3px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 15px;
        }

        .activity-item.success {
            border-left-color: var(--success);
        }

        .activity-item.warning {
            border-left-color: var(--warning);
        }

        .activity-item.info {
            border-left-color: var(--info);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital"></i> Rumah Sakit Sehat
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pasien/index.php">
                            <i class="fas fa-users me-1"></i>Pasien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dokter/index.php">
                            <i class="fas fa-user-md me-1"></i>Dokter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="jadwal/index.php">
                            <i class="fas fa-calendar-alt me-1"></i>Jadwal
                        </a>
                    </li>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>Administrasi
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="users/index.php">
                                <i class="fas fa-users-cog me-2"></i>User Management
                            </a></li>
                            <li><a class="dropdown-item" href="backup/backup.php">
                                <i class="fas fa-database me-2"></i>Backup Database
                            </a></li>
                            <li><a class="dropdown-item" href="export/excel.php">
                                <i class="fas fa-file-excel me-2"></i>Export Data
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['nama_lengkap']; ?> 
                        <span class="badge bg-light text-dark ms-1"><?php echo $_SESSION['role']; ?></span>
                    </span>
                    <a href="auth/logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Selamat Datang di Rumah Sakit Sehat</h1>
                    <p class="lead mb-4">Memberikan pelayanan kesehatan terbaik dengan tim medis profesional dan fasilitas modern untuk kesehatan masyarakat.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="pasien/create.php" class="btn btn-light btn-lg">
                            <i class="fas fa-plus me-2"></i>Daftar Pasien Baru
                        </a>
                        <a href="dokter/index.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-md me-2"></i>Lihat Dokter
                        </a>
                        <a href="jadwal/index.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-calendar me-2"></i>Jadwal Praktik
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image">
                        <i class="fas fa-heartbeat fa-10x text-white opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Statistik Rumah Sakit</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h3 class="card-title"><?php echo $total_pasien; ?></h3>
                            <p class="card-text">Total Pasien</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <i class="fas fa-user-md fa-3x text-success mb-3"></i>
                            <h3 class="card-title"><?php echo $total_dokter; ?></h3>
                            <p class="card-text">Dokter Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stat-card warning">
                        <div class="card-body">
                            <i class="fas fa-user-nurse fa-3x text-warning mb-3"></i>
                            <h3 class="card-title"><?php echo $total_users; ?></h3>
                            <p class="card-text">Staff Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stat-card info">
                        <div class="card-body">
                            <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                            <h3 class="card-title">24/7</h3>
                            <p class="card-text">Layanan Darurat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Aksi Cepat</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <a href="pasien/create.php" class="card quick-action-card text-primary h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-plus fa-3x mb-3"></i>
                            <h5>Daftar Pasien Baru</h5>
                            <p class="text-muted">Tambah data pasien baru</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pasien/index.php" class="card quick-action-card text-success h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-list fa-3x mb-3"></i>
                            <h5>Lihat Data Pasien</h5>
                            <p class="text-muted">Kelola data pasien</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="dokter/create.php" class="card quick-action-card text-warning h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-plus-circle fa-3x mb-3"></i>
                            <h5>Tambah Dokter</h5>
                            <p class="text-muted">Tambah data dokter baru</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="jadwal/create.php" class="card quick-action-card text-info h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-plus fa-3x mb-3"></i>
                            <h5>Buat Jadwal</h5>
                            <p class="text-muted">Buat jadwal praktik dokter</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Analisis Data</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Distribusi Jenis Kelamin Pasien</h5>
                        <canvas id="genderChart" height="250"></canvas>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Pendaftaran Pasien Minggu Ini</h5>
                        <canvas id="registrationChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Activity & System Info -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                        <div class="recent-activity">
                            <?php
                            // Query untuk aktivitas terbaru (contoh sederhana)
                            $activities = [
                                ['type' => 'success', 'message' => 'Pasien baru berhasil didaftarkan', 'time' => '2 menit lalu'],
                                ['type' => 'info', 'message' => 'Jadwal dokter diperbarui', 'time' => '1 jam lalu'],
                                ['type' => 'warning', 'message' => 'Data pasien perlu verifikasi', 'time' => '3 jam lalu'],
                                ['type' => 'success', 'message' => 'Backup database berhasil', 'time' => '5 jam lalu'],
                                ['type' => 'info', 'message' => 'Laporan bulanan telah dibuat', 'time' => '1 hari lalu'],
                            ];
                            
                            foreach($activities as $activity): ?>
                            <div class="activity-item <?php echo $activity['type']; ?>">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo $activity['message']; ?></strong>
                                    <small class="text-muted"><?php echo $activity['time']; ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h5>
                        <div class="system-info">
                            <div class="mb-3">
                                <strong>Versi Aplikasi:</strong>
                                <span class="float-end badge bg-primary">v2.0.0</span>
                            </div>
                            <div class="mb-3">
                                <strong>Database:</strong>
                                <span class="float-end badge bg-success">Online</span>
                            </div>
                            <div class="mb-3">
                                <strong>Pengguna Aktif:</strong>
                                <span class="float-end badge bg-info"><?php echo $total_users; ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Server Time:</strong>
                                <span class="float-end text-muted"><?php echo date('d/m/Y H:i:s'); ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Status Sistem:</strong>
                                <span class="float-end badge bg-success">Normal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-hospital me-2"></i>Rumah Sakit Sehat</h5>
                    <p>Memberikan pelayanan kesehatan terbaik untuk masyarakat.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; 2024 Rumah Sakit Sehat. All rights reserved.</p>
                    <p>Developed with <i class="fas fa-heart text-danger"></i> for better healthcare</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [
                        <?php echo $gender_stats['Laki-laki'] ?? 0; ?>, 
                        <?php echo $gender_stats['Perempuan'] ?? 0; ?>
                    ],
                    backgroundColor: ['#36A2EB', '#FF6384'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Registration Chart
        const regCtx = document.getElementById('registrationChart').getContext('2d');
        const regChart = new Chart(regCtx, {
            type: 'bar',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5'],
                datasets: [{
                    label: 'Jumlah Pendaftaran',
                    data: [<?php echo implode(', ', $weekly_stats); ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Pasien'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Minggu'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>