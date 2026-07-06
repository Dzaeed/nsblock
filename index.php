<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * File: index.php
 * Deskripsi: Halaman utama website NS BLOCK
 * Author: NS BLOCK Team
 * Date: 2025
 */

session_start();
require_once('db_connect.php');
require_once(__DIR__ . '/auth/check_users.php');

$auth_message = $_SESSION['message'] ?? '';
$auth_message_type = $_SESSION['message_type'] ?? '';
if ($auth_message) {
    unset($_SESSION['message'], $_SESSION['message_type']);
}
$current_user = isset($_SESSION['user_id']) ? getUserById($_SESSION['user_id']) : null;
if (isset($_SESSION['user_id']) && isAdmin($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit();
}

function isAreaCalculatorCategory($category) {
    return in_array($category, ['Roster', 'Paving Block'], true);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NS BLOCK - Solusi Material Bangunan Modern</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css?v=account-menu-2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body data-user-id="<?php echo isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 'guest'; ?>" data-user-name="<?php echo $current_user ? htmlspecialchars($current_user['username'], ENT_QUOTES) : ''; ?>" data-user-email="<?php echo $current_user ? htmlspecialchars($current_user['email'], ENT_QUOTES) : ''; ?>">
    <?php if ($auth_message): ?>
        <div class="page-toast <?php echo $auth_message_type === 'error' ? 'toast-error' : 'toast-success'; ?>" id="pageToast">
            <span><?php echo htmlspecialchars($auth_message); ?></span>
            <button type="button" class="toast-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ========================================
         HEADER SECTION
         ======================================== -->
    <header>    
        <a href="#home" class="logo">NS<span>BLOCK</span></a>
        
        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <nav class="navbar">
            <a href="#home" class="active">Home</a>
            <li class="dropdown">
                <a href="#products" class="drop-btn">Produk <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="#produk-roster">Roster</a>
                    <a href="#produk-paving-block">Paving Block</a>
                    <a href="#produk-buis-beton">Buis Beton</a>
                    <a href="#produk-lainnya">Lainnya</a>
                </div>
            </li>
            <a href="#about">Tentang</a>
            <a href="#contact">Kontak</a>
        </nav>
        <div class="navbar-auth-actions account-menu">
            <button type="button" class="auth-icon-btn account-menu-toggle" id="accountMenuToggle" aria-label="Buka menu akun" aria-expanded="false" aria-controls="accountMenuDropdown" title="Menu akun">
                <i class="fas fa-user-circle"></i>
                <i class="fas fa-chevron-down account-menu-arrow" aria-hidden="true"></i>
            </button>
            <div class="account-menu-dropdown" id="accountMenuDropdown">
                <div class="account-menu-summary">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <div>
                        <strong><?php echo $current_user ? htmlspecialchars($current_user['username']) : 'Akun'; ?></strong>
                        <span><?php echo isset($_SESSION['user_id']) ? 'Pelanggan' : 'Belum masuk'; ?></span>
                    </div>
                </div>
                <button type="button" class="account-menu-item theme-toggle" aria-label="Aktifkan mode gelap" title="Ganti tema">
                    <i class="fas fa-moon"></i>
                    <span>Tema</span>
                </button>
                <button type="button" class="account-menu-item cart-header-button" id="cartOpenBtn">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Keranjang</span>
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>
                <button type="button" class="account-menu-item" id="userOrdersOpenBtn">
                    <i class="fas fa-receipt"></i>
                    <span>Pesanan Saya</span>
                </button>
                <div class="account-menu-divider"></div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="account-menu-item logout-link" href="<?php echo BASE_URL; ?>auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                <?php else: ?>
                    <a class="account-menu-item" href="<?php echo BASE_URL; ?>auth/login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ========================================
         HERO SECTION
         ======================================== -->
    <section id="home">
        <div class="home-content">
            <span class="hero-kicker">Roster &bull; Paving Block &bull; Buis Beton</span>
            <h1>NS <span>BLOCK</span></h1>
            <p>Material bangunan berkualitas untuk proyek rumah, taman, dan konstruksi komersial dengan hasil cetak rapi, kuat, dan siap dipesan.</p>
            <div class="hero-actions">
                <a href="#products" class="btn">Lihat Produk</a>
                <a href="#contact" class="btn btn-secondary">Hubungi Kami</a>
            </div>
            <div class="hero-product-links" aria-label="Kategori produk">
                <a href="#produk-roster">Roster</a>
                <a href="#produk-paving-block">Paving Block</a>
                <a href="#produk-buis-beton">Buis Beton</a>
            </div>
        </div>
    </section>

    <!-- ========================================
         PRODUCTS SECTION
         ======================================== -->
    <section id="products">
        <div class="section-title-container section-hero-panel product-hero-panel">
            <div class="product-hero-copy">
                <span class="section-kicker">Katalog Material</span>
                <h2 class="section-title">Pilih material sesuai <span>kebutuhan proyek</span></h2>
                <p class="section-subtitle">Jelajahi roster, paving block, buis beton, dan produk lainnya. Untuk roster dan paving, gunakan kalkulator kebutuhan sebelum masuk keranjang.</p>
            </div>
        </div>

        <?php
        // Definisikan kategori yang ingin Anda tampilkan
        $categories = ['Roster', 'Paving Block', 'Buis Beton', 'Lainnya'];
        
        foreach ($categories as $category) {
            // Buat ID unik untuk setiap section kategori agar bisa di-link dari menu
            $section_id = 'produk-' . strtolower(str_replace(' ', '-', $category));

            // Ambil SEMUA produk dari database untuk kategori ini (tanpa batasan)
            $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY id DESC");
            if (!$stmt) {
                echo '<div style="color:red;">Query error: ' . $conn->error . '</div>';
                continue;
            }
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
        ?>
        <div class="category-section" id="<?php echo $section_id; ?>">
            <div class="category-header">
                <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                <?php if ($result && $result->num_rows > 0) { ?>
                    <div class="product-carousel-controls" aria-label="Navigasi produk <?php echo htmlspecialchars($category); ?>">
                        <button type="button" class="product-carousel-button" data-carousel-direction="prev" aria-label="Produk sebelumnya">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="product-carousel-button" data-carousel-direction="next" aria-label="Produk berikutnya">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php } ?>
            </div>
            
            <?php if ($result && $result->num_rows > 0) { ?>
                <div class="product-carousel">
                    <div class="product-grid" tabindex="0" aria-label="Daftar produk <?php echo htmlspecialchars($category); ?>">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <div class="product-card">
                            <img src="<?php echo BASE_URL . 'uploads/' . $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="product-card-content">
                                <span class="category"><?php echo htmlspecialchars($row['category']); ?></span>
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <div class="product-commerce-meta">
                                    <strong>Rp <?php echo number_format((float) ($row['harga'] ?? 0), 0, ',', '.'); ?></strong>
                                    <span>Stok: <?php echo (int) ($row['stok'] ?? 0); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                                <div class="product-card-actions">
                                    <a href="produk.php?id=<?php echo (int)$row['id']; ?>" class="btn">Lihat Detail</a>
                                    <button type="button" class="btn btn-secondary add-to-cart-button cart-icon-button"
                                        data-product-id="<?php echo $row['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                        data-product-category="<?php echo htmlspecialchars($row['category'], ENT_QUOTES); ?>"
                                        data-product-ukuran="<?php echo htmlspecialchars(isset($row['ukuran']) ? $row['ukuran'] : '', ENT_QUOTES); ?>"
                                        data-product-price="<?php echo htmlspecialchars((string) ($row['harga'] ?? 0), ENT_QUOTES); ?>"
                                        data-product-stock="<?php echo htmlspecialchars((string) ($row['stok'] ?? 0), ENT_QUOTES); ?>"
                                        data-product-paving-rate="<?php echo htmlspecialchars((string) ($row['category'] === 'Paving Block' ? (!empty($row['paving_rate']) ? $row['paving_rate'] : 27) : ''), ENT_QUOTES); ?>"
                                        data-product-image="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($row['image'], ENT_QUOTES); ?>"
                                        data-requires-calculator="<?php echo isAreaCalculatorCategory($row['category']) ? '1' : '0'; ?>"
                                        aria-label="Tambah <?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?> ke keranjang"
                                        title="Tambah ke Keranjang"
                                    ><i class="fas fa-cart-plus" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="product-carousel-status" aria-hidden="true">
                        <span></span>
                    </div>
                </div>
            <?php } else { ?>
                <p class="no-product-message">Produk untuk kategori ini akan segera tersedia.</p>
            <?php } ?>
        </div>
        <?php 
            $stmt->close();
        } 
        ?>
    </section>

    <!-- ========================================
         CART OVERLAY
         ======================================== -->
    <div class="cart-overlay commerce-overlay" id="cartOverlay" aria-hidden="true">
        <aside class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cartTitle">
            <div class="commerce-modal-header">
                <div>
                    <h2 id="cartTitle">Keranjang</h2>
                    <p>Atur pesanan bahan bangunan Anda.</p>
                </div>
                <button type="button" class="auth-modal-close" data-commerce-close="cart" aria-label="Tutup keranjang"><i class="fas fa-times"></i></button>
            </div>
            <div id="cartItems" class="cart-items"></div>
            <div class="cart-summary">
                <div><span>Total Item</span><strong id="cartTotalItems">0</strong></div>
                <div><span>Subtotal</span><strong id="cartSubtotal">Rp 0</strong></div>
            </div>
            <div class="commerce-actions">
                <button type="button" class="btn" id="checkoutOpenBtn">Checkout</button>
                <div class="cart-secondary-actions">
                    <button type="button" class="btn btn-secondary" id="clearCartBtn">Kosongkan</button>
                    <button type="button" class="btn btn-danger" id="cancelCartBtn">Batalkan</button>
                </div>
            </div>
        </aside>
    </div>

    <div class="commerce-overlay" id="checkoutOverlay" aria-hidden="true">
        <div class="commerce-modal checkout-modal" role="dialog" aria-modal="true" aria-labelledby="checkoutTitle">
            <div class="commerce-modal-header">
                <div>
                    <h2 id="checkoutTitle">Checkout</h2>
                    <p>Lengkapi data pesanan sebelum pembayaran.</p>
                </div>
                <button type="button" class="auth-modal-close" data-commerce-close="checkout" aria-label="Tutup checkout"><i class="fas fa-times"></i></button>
            </div>
            <form id="checkoutForm" class="checkout-grid">
                <div class="checkout-fields">
                    <label>Nama pelanggan<input type="text" name="name" required></label>
                    <label>Email<input type="email" name="email" required></label>
                    <label>Nomor WhatsApp<input type="tel" name="whatsapp" required></label>
                    <label>Alamat lengkap<textarea name="address" rows="3" required></textarea></label>
                    <label>Catatan pesanan<textarea name="note" rows="3"></textarea></label>
                    <label>Pengambilan / Pengiriman
                        <select name="delivery" required>
                            <option value="Dikirim">Dikirim</option>
                            <option value="Ambil di toko">Ambil di toko</option>
                        </select>
                    </label>
                    <div class="qris-method-note">
                        <i class="fas fa-qrcode"></i>
                        <div>
                            <span>Metode pembayaran</span>
                            <strong>QRIS NS BLOCK</strong>
                        </div>
                    </div>
                </div>
                <div class="checkout-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div id="checkoutSummaryItems"></div>
                    <div class="checkout-total"><span>Total Pembayaran</span><strong id="checkoutTotal">Rp 0</strong></div>
                    <button type="submit" class="btn">Buat Pesanan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="commerce-overlay" id="paymentOverlay" aria-hidden="true">
        <div class="commerce-modal payment-modal" role="dialog" aria-modal="true" aria-labelledby="paymentTitle">
            <div class="commerce-modal-header">
                <div>
                    <h2 id="paymentTitle">Pembayaran</h2>
                    <p id="paymentOrderId">Nomor pesanan: -</p>
                </div>
                <button type="button" class="auth-modal-close" data-commerce-close="payment" aria-label="Tutup pembayaran"><i class="fas fa-times"></i></button>
            </div>
            <div class="payment-grid">
                <div class="payment-visual" id="qrisPaymentPanel">
                    <?php if (file_exists(__DIR__ . '/assets/qris.png')): ?>
                        <img src="assets/qris.png" alt="QRIS NSBLOCK">
                    <?php else: ?>
                        <div class="qris-placeholder">Tempat QRIS</div>
                    <?php endif; ?>
                </div>
                <div class="payment-info">
                    <h3>Total: <span id="paymentTotal">Rp 0</span></h3>
                    <ol id="paymentSteps">
                        <li>Pilih metode pembayaran.</li>
                        <li>Bayar sesuai total.</li>
                        <li>Upload bukti pembayaran.</li>
                    </ol>
                    <label class="proof-upload">Upload bukti pembayaran
                        <input type="file" id="paymentProofInput" accept="image/*">
                    </label>
                    <img id="paymentProofPreview" class="proof-preview" alt="Preview bukti pembayaran">
                    <button type="button" class="btn" id="submitProofBtn">Kirim Bukti Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    <div class="commerce-overlay" id="ordersOverlay" aria-hidden="true">
        <div class="commerce-modal orders-modal" role="dialog" aria-modal="true" aria-labelledby="ordersTitle">
            <div class="commerce-modal-header">
                <div>
                    <h2 id="ordersTitle">Pesanan Saya</h2>
                    <p>Lihat status, kondisi, dan detail pembayaran pesanan Anda.</p>
                </div>
                <button type="button" class="auth-modal-close" data-commerce-close="orders" aria-label="Tutup pesanan"><i class="fas fa-times"></i></button>
            </div>
            <div id="userOrdersList" class="user-orders-list"></div>
        </div>
    </div>

    <!-- ========================================
         ABOUT SECTION
         ======================================== -->
    <section id="about">
        <div class="section-title-container section-hero-panel">
            <span class="section-kicker">Profil NS BLOCK</span>
            <h2 class="section-title"><span>Tentang</span> Perusahaan</h2>
            <p class="section-subtitle">Mengenal proses, komitmen, dan kualitas material yang kami hadirkan untuk setiap proyek.</p>
        </div>
        <div class="about-container">
            <div class="about-image">
                <img src="Picture/GUDANG.jpg" 
                     alt="Gudang NS BLOCK">
            </div>
            <div class="about-content">
                <h3>Lebih dari Sekedar Produsen, Kami adalah Partner Konstruksi Anda</h3>
                <p style="text-align: center;">Berdiri sejak tahun 2010, NS BLOCK berkomitmen untuk menghadirkan inovasi dalam dunia material bangunan. Kami memadukan teknologi cetak modern dengan bahan baku pilihan untuk menghasilkan produk yang tidak hanya kuat, tetapi juga unggul dalam nilai estetika.</p>
            </div>
        </div>
    </section>

    <!-- ========================================
         CONTACT SECTION
         ======================================== -->
    <section id="contact">
        <div class="section-title-container section-hero-panel">
            <span class="section-kicker">Konsultasi & Pemesanan</span>
            <h2 class="section-title">Hubungi <span>Kami</span></h2>
            <p class="section-subtitle">Butuh rekomendasi produk atau estimasi kebutuhan material? Tim kami siap membantu.</p>
        </div>
        <div class="contact-wrapper">
            <!-- Notifikasi pesan sukses/gagal -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'terkirim'): ?>
                <div class="message success" style="margin-bottom:20px;">
                    <i class="fas fa-check-circle"></i> Testimoni Anda berhasil dikirim dan menunggu persetujuan admin.
                </div>
            <?php endif; ?>
            <!-- Contact Information -->
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Dapatkan Penawaran Terbaik!</h3>
                    <p>Tim kami siap membantu Anda. Jangan ragu untuk menghubungi kami atau tinggalkan pesan di samping.</p>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Jl. kh. Muchtar Tabrani no.57 rt/rw 03/01, Bekasi Utara</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>(021) 8888 2116</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone-alt"></i>
                        <a href="https://wa.me/6281387179890" target="_blank">0813-8717-9890</a>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>slemanmuhammad08@gmail.com</span>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form">
                    <form action="connect_testimoni.php" method="POST">
                        <input type="text" placeholder="Nama Anda" name="name" required>
                        <input type="email" placeholder="Email Anda (Tidak akan ditampilkan)" name="email" required>
                        <textarea rows="6" placeholder="Pesan atau Testimoni Anda" name="message" required></textarea>
                        <button type="submit" class="btn">Kirim Pesan</button>
                    </form>
                </div>
            </div>
            
            <!-- Testimonials Section -->
            <div class="testimonial-section">
                <h3 class="testimonial-title">Apa Kata Mereka?</h3>
                <?php
                // Query untuk mengambil testimoni yang sudah disetujui (tanpa email di frontend)
                $sql_testimonials = "SELECT name, message, created_at FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 5";
                $result_testimonials = $conn->query($sql_testimonials); 
                if (!$result_testimonials) {
                    echo '<div style="color:red;">Query error: ' . $conn->error . '</div>';
                }
                if ($result_testimonials && $result_testimonials->num_rows > 0) {
                    while ($row = $result_testimonials->fetch_assoc()) {
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <i class="fas fa-user-circle"></i>
                        <div class="testimonial-info">
                            <span class="testimonial-name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <span class="testimonial-date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                        </div>
                    </div>
                    <p class="testimonial-message">"<?php echo nl2br(htmlspecialchars($row['message'])); ?>"</p>
                </div>
                <?php 
                    } 
                } else if ($result_testimonials) { 
                    echo "<p class='testimonial-empty'>Jadilah yang pertama memberikan testimoni!</p>"; 
                } 
                ?>
            </div>
        </div>
    </section>

    <!-- ========================================
         FOOTER SECTION
         ======================================== -->
    <footer>
        <p>&copy; 2025 NS BLOCK. All Rights Reserved.</p>
    </footer>

    <!-- JavaScript Files -->
    <script src="js/script.js"></script>
    <script src="js/commerce.js"></script>
    <div class="confirm-modal-overlay" id="confirmLogoutOverlay" role="dialog" aria-modal="true" aria-labelledby="confirmLogoutTitle" aria-hidden="true">
        <div class="confirm-modal">
            <div class="confirm-modal-header">
                <div class="confirm-modal-title-wrap">
                    <span class="confirm-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <div>
                        <h3 id="confirmLogoutTitle">Konfirmasi Logout</h3>
                        <p>Yakin ingin keluar dari akun Anda?</p>
                    </div>
                </div>
                <button type="button" class="confirm-close" id="confirmLogoutClose" aria-label="Tutup konfirmasi">×</button>
            </div>
            <div class="confirm-modal-body">
                <p>Jika Anda keluar, Anda harus masuk kembali untuk mengakses dashboard lagi.</p>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" id="confirmLogoutCancel">Batal</button>
                <button type="button" id="confirmLogoutProceed" class="btn btn-danger" data-logout-url="">Logout</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const pageToast = document.getElementById('pageToast');
            if (pageToast) {
                const toastClose = pageToast.querySelector('.toast-close');
                const hideToast = () => pageToast.classList.add('toast-hidden');
                toastClose && toastClose.addEventListener('click', hideToast);
                window.setTimeout(hideToast, 5000);
            }

            const confirmOverlay = document.getElementById('confirmLogoutOverlay');
            const confirmClose = document.getElementById('confirmLogoutClose');
            const confirmCancel = document.getElementById('confirmLogoutCancel');
            const confirmProceed = document.getElementById('confirmLogoutProceed');
            document.querySelectorAll('a.logout-link').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    if (!confirmOverlay || !confirmProceed) return;
                    confirmProceed.dataset.logoutUrl = this.getAttribute('href');
                    confirmOverlay.setAttribute('aria-hidden', 'false');
                    confirmOverlay.classList.add('active');
                });
            });

            if (confirmProceed) {
                confirmProceed.addEventListener('click', function() {
                    var logoutUrl = this.dataset.logoutUrl;
                    if (logoutUrl) {
                        window.location.href = logoutUrl;
                    }
                });
            }

            function closeConfirm() {
                if (!confirmOverlay) return;
                confirmOverlay.classList.remove('active');
                confirmOverlay.setAttribute('aria-hidden', 'true');
            }

            if (confirmClose) confirmClose.addEventListener('click', closeConfirm);
            if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
            if (confirmOverlay) {
                confirmOverlay.addEventListener('click', function(e) {
                    if (e.target === confirmOverlay) closeConfirm();
                });
            }
        })();
    </script>
</body>
</html>
