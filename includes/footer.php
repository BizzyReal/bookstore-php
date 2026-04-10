<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="text-white fw-bold">CariBuku</h5>
                <p>Toko buku online terlengkap dan terpercaya.</p>
            </div>
            <div class="col-md-4">
                <h5 class="text-white fw-bold">Tautan</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="about.php">Tentang Kami</a></li>
                    <li><a href="search.php">Semua Buku</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="text-white fw-bold">Kontak</h5>
                <p><i class="fas fa-envelope me-2"></i> support@caribuku.com</p>
                <p><i class="fas fa-phone me-2"></i> +62 813 8722 1775</p>
                <p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="inbox.php" class="text-white text-decoration-none"><i class="fas fa-comment me-2"></i> Hubungi Admin</a>
                    <?php else: ?>
                        <a href="login.php" class="text-white text-decoration-none"><i class="fas fa-sign-in-alt me-2"></i> Login untuk menghubungi admin</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">&copy; <?= date('Y') ?> CariBuku. All rights reserved.</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>