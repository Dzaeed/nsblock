<?php
session_start();
require_once('db_connect.php');
require_once(__DIR__ . '/auth/check_users.php');

enforceUserAccess();

$current_user = isset($_SESSION['user_id']) ? getUserById($_SESSION['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NS BLOCK</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body data-commerce-page="checkout" data-user-id="<?php echo isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 'guest'; ?>" data-user-name="<?php echo $current_user ? htmlspecialchars($current_user['username'], ENT_QUOTES) : ''; ?>" data-user-email="<?php echo $current_user ? htmlspecialchars($current_user['email'], ENT_QUOTES) : ''; ?>">
    <header>
        <a href="<?php echo BASE_URL; ?>index.php#home" class="logo">NS<span>BLOCK</span></a>
        <nav class="navbar">
            <a href="<?php echo BASE_URL; ?>index.php#home">Home</a>
            <a href="<?php echo BASE_URL; ?>index.php#products">Produk</a>
            <a href="<?php echo BASE_URL; ?>orders.php">Pesanan Saya</a>
        </nav>
        <div class="navbar-auth-actions">
            <button type="button" class="auth-icon-btn cart-header-button" id="cartOpenBtn" aria-label="Checkout" title="Checkout">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cartBadge">0</span>
            </button>
            <button type="button" class="auth-icon-btn" id="userOrdersOpenBtn" aria-label="Lihat pesanan" title="Pesanan Saya">
                <i class="fas fa-receipt"></i>
            </button>
        </div>
        <button class="theme-toggle" type="button" aria-label="Aktifkan mode gelap" title="Ganti tema">
            <i class="fas fa-moon"></i>
        </button>
    </header>

    <main class="commerce-page">
        <section class="commerce-page-section">
            <div class="section-title-container section-hero-panel">
                <span class="section-kicker">Checkout</span>
                <h1 class="section-title">Selesaikan <span>Pesanan</span></h1>
                <p class="section-subtitle">Lengkapi data, pilih metode pembayaran, lalu kirim pesanan Anda.</p>
            </div>

            <form id="checkoutForm" class="checkout-grid checkout-page-grid">
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
                <aside class="checkout-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div id="checkoutSummaryItems"></div>
                    <div class="checkout-total"><span>Total Pembayaran</span><strong id="checkoutTotal">Rp 0</strong></div>
                    <button type="submit" class="btn">Buat Pesanan</button>
                    <a href="<?php echo BASE_URL; ?>index.php#products" class="btn btn-secondary commerce-page-link">Tambah Produk</a>
                </aside>
            </form>
        </section>
    </main>

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
                    <ol id="paymentSteps"></ol>
                    <label class="proof-upload">Upload bukti pembayaran
                        <input type="file" id="paymentProofInput" accept="image/*">
                    </label>
                    <img id="paymentProofPreview" class="proof-preview" alt="Preview bukti pembayaran">
                    <button type="button" class="btn" id="submitProofBtn">Kirim Bukti Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script src="js/commerce.js"></script>
</body>
</html>
