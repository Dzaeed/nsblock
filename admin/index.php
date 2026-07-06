<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once('../db_connect.php');
require_once('../auth/check_users.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

enforceAdminAccess();

// Active section selector
$allowedSections = ['overview', 'products', 'orders', 'testimonials', 'customers'];
$activeSection = 'overview';
if (isset($_GET['section']) && in_array($_GET['section'], $allowedSections, true)) {
    $activeSection = $_GET['section'];
}

$editId = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
$editProduct = null;
if ($editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $editProduct = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$products = [];
$productResult = $conn->query("SELECT * FROM products ORDER BY id DESC");
if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

$users = [];
$userResult = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

$testimonials = [];
$testimonialResult = $conn->query("SELECT * FROM testimonials ORDER BY created_at DESC");
if ($testimonialResult) {
    while ($row = $testimonialResult->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

$productCount = count($products);
$userCount = count($users);
$testimonialCount = count($testimonials);
$approvedCount = 0;
foreach ($testimonials as $testimonial) {
    if (!empty($testimonial['is_approved'])) {
        $approvedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NS BLOCK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-admin.php">
</head>
<body>
    <div class="dashboard-shell">
        <header class="topbar">
            <div class="topbar-left">
                <div class="brand"><i class="fas fa-cube"></i> NSBLOCK Admin</div>
                <a href="../index.php" class="dashboard-home-link">Beranda</a>
            </div>
            <div class="top-actions">
                <a href="../auth/logout.php" class="btn btn-danger logout-button" onclick="return confirm('Yakin ingin logout?');"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="sidebar-brand">Kontrol Utama</div>
                <nav class="nav-menu" aria-label="Menu Admin">
                    <button type="button" class="nav-link<?php echo $activeSection === 'overview' ? ' active' : ''; ?>" data-target="overview">
                        <i class="fas fa-home"></i> Ringkasan
                    </button>
                    <button type="button" class="nav-link<?php echo $activeSection === 'products' ? ' active' : ''; ?>" data-target="products">
                        <i class="fas fa-box-open"></i> Produk
                    </button>
                    <button type="button" class="nav-link<?php echo $activeSection === 'orders' ? ' active' : ''; ?>" data-target="orders">
                        <i class="fas fa-receipt"></i> Pesanan
                    </button>
                    <button type="button" class="nav-link<?php echo $activeSection === 'testimonials' ? ' active' : ''; ?>" data-target="testimonials">
                        <i class="fas fa-comments"></i> Testimoni
                    </button>
                    <button type="button" class="nav-link<?php echo $activeSection === 'customers' ? ' active' : ''; ?>" data-target="customers">
                        <i class="fas fa-users"></i> Pelanggan
                    </button>
                </nav>
            </aside>

            <main class="main-panel">
                <section id="overview" class="section<?php echo $activeSection === 'overview' ? ' active' : ''; ?>">
                    <div class="section-header">
                        <div>
                            <h1>Dashboard Admin</h1>
                            <p>Semua kontrol produk, testimoni, dan pelanggan sekarang berada dalam satu halaman.</p>
                        </div>
                    </div>

                    <div class="cards-grid">
                        <article class="metric-card">
                            <h3>Total Produk</h3>
                            <span><?php echo $productCount; ?></span>
                        </article>
                        <article class="metric-card">
                            <h3>Total Pelanggan</h3>
                            <span><?php echo $userCount; ?></span>
                        </article>
                        <article class="metric-card">
                            <h3>Total Testimoni</h3>
                            <span><?php echo $testimonialCount; ?></span>
                        </article>
                        <article class="metric-card">
                            <h3>Testimoni Disetujui</h3>
                            <span><?php echo $approvedCount; ?></span>
                        </article>
                        <article class="metric-card">
                            <h3>Total Pesanan</h3>
                            <span id="adminTotalOrders">0</span>
                        </article>
                        <article class="metric-card">
                            <h3>Menunggu Konfirmasi</h3>
                            <span id="adminWaitingOrders">0</span>
                        </article>
                        <article class="metric-card">
                            <h3>Diproses</h3>
                            <span id="adminProcessingOrders">0</span>
                        </article>
                        <article class="metric-card">
                            <h3>Selesai</h3>
                            <span id="adminDoneOrders">0</span>
                        </article>
                    </div>
                </section>

                <section id="products" class="section<?php echo $activeSection === 'products' ? ' active' : ''; ?>">
                    <div class="section-header">
                        <div>
                            <h2>Kontrol Produk</h2>
                            <p>Kelola daftar produk Anda dan tambahkan produk baru langsung dari dashboard ini.</p>
                        </div>
                        <button id="toggleForm" type="button" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?php echo $editProduct ? 'Edit Produk' : 'Tambah Produk Baru'; ?>
                        </button>
                    </div>

                    <div id="productFormPanel" class="panel<?php echo $editProduct ? ' active' : ''; ?>">
                        <form action="proses.php?aksi=<?php echo $editProduct ? 'edit&id=' . $editProduct['id'] : 'tambah'; ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-tag"></i> Nama Produk</label>
                                <input type="text" id="name" name="name" placeholder="Masukkan nama produk" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="category"><i class="fas fa-list"></i> Kategori</label>
                                <select id="category" name="category" required>
                                    <option value="">Pilih kategori</option>
                                    <?php
                                    $categories = ['Roster', 'Paving Block', 'Buis Beton', 'Lainnya'];
                                    foreach ($categories as $category) {
                                        $selected = $editProduct && $editProduct['category'] === $category ? 'selected' : '';
                                        echo "<option value=\"$category\" $selected>$category</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="ukuran"><i class="fas fa-ruler-combined"></i> Ukuran</label>
                                <input type="text" id="ukuran" name="ukuran" placeholder="Masukkan ukuran produk" required value="<?php echo $editProduct && isset($editProduct['ukuran']) ? htmlspecialchars($editProduct['ukuran']) : ''; ?>">
                                <small class="field-help">Contoh: 20x10x5 cm atau 60x40 cm.</small>
                            </div>

                            <div class="form-group" id="pavingRateGroup">
                                <label for="paving_rate"><i class="fas fa-th-large"></i> Kebutuhan Paving per m²</label>
                                <select id="paving_rate" name="paving_rate">
                                    <?php $selectedPavingRate = $editProduct && isset($editProduct['paving_rate']) ? (int) $editProduct['paving_rate'] : 27; ?>
                                    <option value="27" <?php echo $selectedPavingRate === 27 ? 'selected' : ''; ?>>27 pcs/m²</option>
                                    <option value="44" <?php echo $selectedPavingRate === 44 ? 'selected' : ''; ?>>44 pcs/m²</option>
                                </select>
                                <small class="field-help">Khusus kategori Paving Block. Pelanggan hanya mengisi panjang dan lebar.</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="harga"><i class="fas fa-money-bill-wave"></i> Harga</label>
                                    <input type="number" id="harga" name="harga" min="0" step="100" placeholder="Masukkan harga produk" required value="<?php echo $editProduct && isset($editProduct['harga']) ? htmlspecialchars((string) $editProduct['harga']) : '0'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="stok"><i class="fas fa-boxes"></i> Stok</label>
                                    <input type="number" id="stok" name="stok" min="0" step="1" placeholder="Masukkan stok produk" required value="<?php echo $editProduct && isset($editProduct['stok']) ? htmlspecialchars((string) $editProduct['stok']) : '0'; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description"><i class="fas fa-align-left"></i> Deskripsi Produk</label>
                                <textarea id="description" name="description" rows="5" placeholder="Masukkan deskripsi detail produk" required><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="image"><i class="fas fa-image"></i> Gambar Produk</label>
                                <input type="file" id="image" name="image" accept="image/*" class="file-input" <?php echo $editProduct ? '' : 'required'; ?>>
                                <small class="file-help">Format JPG, JPEG, PNG. Maksimal 2MB.</small>
                            </div>

                            <?php if ($editProduct): ?>
                                <div class="form-group current-image">
                                    <label>Gambar Saat Ini</label>
                                    <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($editProduct['image']); ?>" alt="<?php echo htmlspecialchars($editProduct['name']); ?>" class="product-image">
                                </div>
                            <?php endif; ?>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?php echo $editProduct ? 'Perbarui Produk' : 'Simpan Produk'; ?>
                                </button>
                                <?php if ($editProduct): ?>
                                    <a href="index.php?section=products" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Ukuran</th>
                                    <th>Paving/m²</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada produk.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                                            <td><?php echo htmlspecialchars(isset($product['ukuran']) ? $product['ukuran'] : ''); ?></td>
                                            <td><?php echo $product['category'] === 'Paving Block' ? (int) (!empty($product['paving_rate']) ? $product['paving_rate'] : 27) . ' pcs/m²' : '-'; ?></td>
                                            <td>Rp <?php echo number_format((float) ($product['harga'] ?? 0), 0, ',', '.'); ?></td>
                                            <td><?php echo (int) ($product['stok'] ?? 0); ?></td>
                                            <td class="table-action">
                                                <a href="index.php?section=products&edit_id=<?php echo (int) $product['id']; ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Edit</a>
                                                <a href="proses.php?aksi=hapus&id=<?php echo (int) $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="orders" class="section<?php echo $activeSection === 'orders' ? ' active' : ''; ?>">
                    <div class="section-header">
                        <div>
                            <h2>Transaksi Pesanan</h2>
                            <p>Kelola pesanan pelanggan yang masuk dari alur checkout dan berbagai metode pembayaran.</p>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>WhatsApp</th>
                                    <th>Produk</th>
                                    <th>Total</th>
                                    <th>Metode</th>
                                    <th>Bukti</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="adminOrdersBody">
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Belum ada pesanan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="testimonials" class="section<?php echo $activeSection === 'testimonials' ? ' active' : ''; ?>">
                    <div class="section-header">
                        <div>
                            <h2>Manajemen Testimoni</h2>
                            <p>Review, setujui, atau hapus testimoni pelanggan dari satu tampilan.</p>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Pesan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($testimonials)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada testimoni.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <?php
                                        $isApproved = !empty($testimonial['is_approved']);
                                        $statusText = $isApproved ? 'Disetujui' : 'Menunggu';
                                        $approveAction = $isApproved ? 'unapprove' : 'approve';
                                        $approveLabel = $isApproved ? 'Sembunyikan' : 'Tampilkan';
                                        $approveIcon = $isApproved ? 'fa-eye-slash' : 'fa-eye';
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['email']); ?></td>
                                            <td class="message-cell"><?php echo nl2br(htmlspecialchars(mb_strimwidth($testimonial['message'], 0, 120, '...'))); ?></td>
                                            <td><span class="status-badge <?php echo $isApproved ? 'status-approved' : 'status-pending'; ?>"><?php echo $statusText; ?></span></td>
                                            <td><?php echo date('d M Y', strtotime($testimonial['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <a href="proses_testimoni.php?action=<?php echo $approveAction; ?>&id=<?php echo (int) $testimonial['id']; ?>" class="btn btn-secondary"><i class="fas <?php echo $approveIcon; ?>"></i> <?php echo $approveLabel; ?></a>
                                                <a href="proses_testimoni.php?action=delete&id=<?php echo (int) $testimonial['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus testimoni ini?');"><i class="fas fa-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="customers" class="section<?php echo $activeSection === 'customers' ? ' active' : ''; ?>">
                    <div class="section-header">
                        <div>
                            <h2>Data Pelanggan</h2>
                            <p>Daftar pengguna yang terdaftar dalam sistem.</p>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada data pelanggan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo (int) $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="../js/commerce.js"></script>
    <script>
        const navButtons = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('.section');
        const formPanel = document.getElementById('productFormPanel');
        const toggleForm = document.getElementById('toggleForm');
        const categorySelect = document.getElementById('category');
        const pavingRateGroup = document.getElementById('pavingRateGroup');
        const pavingRateSelect = document.getElementById('paving_rate');

        function togglePavingRateField() {
            if (!categorySelect || !pavingRateGroup || !pavingRateSelect) return;
            const isPaving = categorySelect.value === 'Paving Block';
            pavingRateGroup.style.display = isPaving ? 'block' : 'none';
            pavingRateSelect.disabled = !isPaving;
        }

        function activateSection(target) {
            sections.forEach(section => {
                section.classList.toggle('active', section.id === target);
            });
            navButtons.forEach(button => {
                button.classList.toggle('active', button.dataset.target === target);
            });
            if (window.location.hash !== '#' + target) {
                history.replaceState(null, '', '#'+target);
            }
        }

        navButtons.forEach(button => {
            button.addEventListener('click', () => {
                activateSection(button.dataset.target);
            });
        });

        if (window.location.hash) {
            const hashTarget = window.location.hash.replace('#', '');
            if (document.getElementById(hashTarget)) {
                activateSection(hashTarget);
            }
        }

        toggleForm.addEventListener('click', () => {
            formPanel.classList.toggle('active');
            formPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        if (categorySelect) {
            categorySelect.addEventListener('change', togglePavingRateField);
            togglePavingRateField();
        }
    </script>
</body>
</html>
