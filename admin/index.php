<?php
session_start();
require_once __DIR__ . '/includes/header.php';

// Statistik
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];

// Data untuk chart (6 bulan terakhir)
$chartLabels = [];
$chartData = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $namaBulan = date('M Y', strtotime("-$i months"));
    $chartLabels[] = $namaBulan;
    $result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = '$bulan'");
    $chartData[] = $result->fetch_assoc()['total'];
}

// 5 Pesanan terbaru dengan nama user
$recentOrders = $conn->query("
    SELECT orders.*, users.name as user_name 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    ORDER BY order_date DESC 
    LIMIT 5
");
?>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h1 class="display-6 fw-semibold">Selamat datang, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?> 👋</h1>
        <p class="text-muted">Kelola toko buku Anda dengan mudah dan cepat.</p>
    </div>
    <div class="mt-2 mt-sm-0">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
            <i class="fas fa-calendar-alt me-1"></i> <?= date('l, d F Y') ?>
        </span>
    </div>
</div>

<!-- 4 Statistik Card -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div><p class="stat-title mb-1">Total Buku</p><h2 class="stat-number"><?= $totalBooks ?></h2></div>
                    <div class="stat-icon bg-primary bg-gradient"><i class="fas fa-book"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div><p class="stat-title mb-1">Kategori</p><h2 class="stat-number"><?= $totalCategories ?></h2></div>
                    <div class="stat-icon bg-success bg-gradient"><i class="fas fa-tags"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div><p class="stat-title mb-1">User Terdaftar</p><h2 class="stat-number"><?= $totalUsers ?></h2></div>
                    <div class="stat-icon bg-info bg-gradient"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div><p class="stat-title mb-1">Total Pesanan</p><h2 class="stat-number"><?= $totalOrders ?></h2></div>
                    <div class="stat-icon bg-warning bg-gradient"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Grafik -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-4">
                <h5 class="fw-semibold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Tren Pesanan per Bulan</h5>
            </div>
            <div class="card-body">
                <canvas id="ordersChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <!-- Aksi cepat -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4">
                <h5 class="fw-semibold mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <a href="books.php" class="btn btn-primary quick-btn"><i class="fas fa-plus-circle me-2"></i>Tambah Buku Baru</a>
                <a href="categories.php" class="btn btn-success quick-btn"><i class="fas fa-tag me-2"></i>Kelola Kategori</a>
                <a href="orders.php" class="btn btn-info quick-btn text-white"><i class="fas fa-truck me-2"></i>Lihat Semua Pesanan</a>
                <a href="messages.php" class="btn btn-secondary quick-btn"><i class="fas fa-envelope me-2"></i>Baca Pesan Masuk</a>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Pesanan Terbaru dengan Nama User -->
<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-header bg-transparent border-0 pt-4">
        <h5 class="fw-semibold mb-0"><i class="fas fa-clock me-2 text-info"></i>Pesanan Terbaru</h5>
    </div>
    <div class="card-body">
        <?php if ($recentOrders->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($order['user_name']) ?></td>
                            <td>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                            <td><span class="badge <?= $order['status']=='pending' ? 'bg-warning' : 'bg-primary' ?>"><?= $order['status'] ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><a href="orders.php?detail=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Belum ada pesanan.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Jumlah Pesanan',
                data: <?= json_encode($chartData) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#1e293b',
                pointBorderColor: '#fff',
                pointRadius: 5,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: { y: { beginAtZero: true, stepSize: 1 } }
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>