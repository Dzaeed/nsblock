<?php
/**
 * File: produk.php
 * Deskripsi: Halaman detail produk
 * Author: NS BLOCK Team
 * Date: 2025
 */

require_once('db_connect.php');
require_once(__DIR__ . '/auth/check_users.php');
session_start();

// Ambil ID produk dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID produk tidak valid.");
}

// Query untuk mengambil data produk
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Produk tidak ditemukan.");
}

$product = $result->fetch_assoc();
$stmt->close();

// Get current user
$current_user = isset($_SESSION['user_id']) ? getUserById($_SESSION['user_id']) : null;
if (isset($_SESSION['user_id']) && isAdmin($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit();
}

function isAreaCalculatorCategory($category) {
    return in_array($category, ['Roster', 'Paving Block'], true);
}

// Define breadcrumb
$breadcrumbs = [
    ['name' => 'Beranda', 'url' => 'index.php'],
    ['name' => 'Menu', 'url' => 'index.php#products'],
    ['name' => htmlspecialchars($product['category']), 'url' => 'index.php#produk-' . strtolower(str_replace(' ', '-', $product['category']))],
    ['name' => htmlspecialchars($product['name']), 'url' => '#']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - NS BLOCK</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css?v=account-menu-2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Breadcrumb Navigation */
        .breadcrumb {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .breadcrumb a {
            color: var(--primary-red);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 0.5rem;
            color: var(--text-muted);
        }

        /* Product Detail Container */
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: flex-start;
        }
        
        /* Product Image Section */
        .product-image-section {
            position: relative;
        }
        
        .product-main-image-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
        }
        
        .product-main-image {
            max-width: 100%;
            max-height: 100%;
            height: auto;
            width: auto;
            transition: var(--transition);
            object-fit: contain;
        }
        
        .product-main-image:hover {
            transform: scale(1.02);
        }
        
        /* Product Info Section */
        .product-info-section h1 {
            color: var(--primary-red);
            font-size: 1.75rem;
            margin-bottom: 1rem;
            line-height: 1.3;
            font-weight: 700;
        }
        
        .product-category-badge {
            background-color: #d4a83b;
            color: #111111;
            padding: 0.4rem 0.9rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .product-description {
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .product-price-section {
            display: grid;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: var(--surface-alt);
            border-radius: 10px;
        }

        .product-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-red);
        }

        .product-stock {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .product-stock.in-stock {
            color: #10b981;
        }

        .product-stock.low-stock {
            color: #f59e0b;
        }

        .product-stock.out-of-stock {
            color: #ef4444;
        }

        /* Quantity Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .quantity-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-main);
            min-width: 70px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            background: var(--surface);
        }

        .qty-btn {
            padding: 0.4rem 0.8rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-main);
            transition: var(--transition);
            flex: 0 0 auto;
        }

        .qty-btn:hover {
            background: var(--surface-alt);
            color: var(--primary-red);
        }

        .qty-input {
            width: 60px;
            padding: 0.5rem;
            border: none;
            text-align: center;
            font-size: 1rem;
            background: transparent;
            color: var(--text-main);
        }

        .qty-input:focus {
            outline: none;
        }

        /* Action Buttons */
        .product-actions {
            display: flex;
            gap: 0.75rem;
            flex-direction: column;
        }

        .btn-add-to-cart {
            padding: 0.9rem 1.8rem;
            background: var(--primary-red);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-add-to-cart:hover {
            background: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-back {
            padding: 0.7rem 1.4rem;
            background: transparent;
            color: var(--primary-red);
            border: 2px solid var(--primary-red);
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-back:hover {
            background: var(--primary-red);
            color: var(--white);
        }

        .btn-back i {
            font-size: 0.8rem;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .product-detail {
                gap: 2rem;
            }

            .product-info-section h1 {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 768px) {
            .product-detail-container {
                padding: 1rem;
            }

            .product-detail {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .product-info-section h1 {
                font-size: 1.6rem;
            }

            .product-price {
                font-size: 1.5rem;
            }

            .quantity-selector {
                margin-bottom: 1.5rem;
            }

            .product-actions {
                flex-direction: column;
            }

            .btn-add-to-cart {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .breadcrumb {
                font-size: 0.8rem;
                padding: 0.6rem 1rem;
            }

            .product-detail-container {
                padding: 0.75rem;
            }

            .product-info-section h1 {
                font-size: 1.35rem;
                margin-bottom: 0.75rem;
            }

            .product-category-badge {
                padding: 0.35rem 0.8rem;
                font-size: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .product-description {
                font-size: 0.9rem;
                line-height: 1.6;
                margin-bottom: 1rem;
            }

            .product-price-section {
                gap: 0.4rem;
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .product-price {
                font-size: 1.4rem;
            }

            .product-stock {
                font-size: 0.85rem;
            }

            .quantity-selector {
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .quantity-label {
                font-size: 0.85rem;
                min-width: auto;
            }

            .quantity-controls {
                width: 100%;
            }

            .qty-input {
                width: 50px;
                padding: 0.35rem;
                font-size: 0.9rem;
            }

            .qty-btn {
                padding: 0.35rem 0.6rem;
                font-size: 0.9rem;
            }

            .product-actions {
                gap: 0.5rem;
            }

            .btn-add-to-cart {
                padding: 0.8rem 1.2rem;
                font-size: 0.85rem;
                width: 100%;
            }

            .btn-back {
                width: 100%;
                padding: 0.65rem 1.2rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body data-user-id="<?php echo isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 'guest'; ?>">
    <!-- ========================================
         HEADER SECTION
         ======================================== -->
    <header>    
        <a href="index.php#home" class="logo">NS<span>BLOCK</span></a>
        
        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <nav class="navbar">
            <a href="index.php#home">Home</a>
            <li class="dropdown">
                <a href="index.php#products" class="drop-btn">Produk <i class="fas fa-caret-down"></i></a>
                <div class="dropdown-content">
                    <a href="index.php#produk-roster">Roster</a>
                    <a href="index.php#produk-paving-block">Paving Block</a>
                    <a href="index.php#produk-buis-beton">Buis Beton</a>
                    <a href="index.php#produk-lainnya">Lainnya</a>
                </div>
            </li>
            <a href="index.php#about">Tentang</a>
            <a href="index.php#contact">Kontak</a>
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
         BREADCRUMB SECTION
         ======================================== -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <?php foreach ($breadcrumbs as $key => $breadcrumb): ?>
            <?php if ($key > 0): ?>
                <span class="breadcrumb-separator">·</span>
            <?php endif; ?>
            <?php if ($breadcrumb['url'] !== '#'): ?>
                <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['name']; ?></a>
            <?php else: ?>
                <span><?php echo $breadcrumb['name']; ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- ========================================
         PRODUCT DETAIL SECTION
         ======================================== -->
    <section style="padding-top: 2rem; padding-bottom: 4rem; min-height: auto; background-color: var(--page-bg);">
        <div class="product-detail-container">
            <div class="product-detail">
                <!-- Product Images Section -->
                <div class="product-image-section">
                    <div class="product-main-image-wrapper">
                        <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image'], ENT_QUOTES); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="product-main-image"
                             id="mainProductImage">
                    </div>
                </div>
                
                <!-- Product Info Section -->
                <div class="product-info-section">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <span class="product-category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                    
                    <p class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>

                    <!-- Price and Stock Section -->
                    <div class="product-price-section">
                        <div class="product-price">
                            Rp <?php echo number_format((float) ($product['harga'] ?? 0), 0, ',', '.'); ?>
                        </div>
                        <div class="product-stock <?php echo ((int)$product['stok'] === 0) ? 'out-of-stock' : (((int)$product['stok'] < 10) ? 'low-stock' : 'in-stock'); ?>">
                            <?php 
                                $stock = (int)($product['stok'] ?? 0);
                                echo "10 pcs per pack · Stok: $stock pcs";
                            ?>
                        </div>
                    </div>

                    <!-- Quantity Selector -->
                    <div class="quantity-selector">
                        <label class="quantity-label">Jumlah:</label>
                        <div class="quantity-controls">
                            <button type="button" class="qty-btn" id="decreaseQtyBtn" aria-label="Kurangi jumlah">−</button>
                            <input type="number" id="quantityInput" class="qty-input" value="1" min="1" max="<?php echo max(1, (int)$product['stok']); ?>" aria-label="Jumlah produk">
                            <button type="button" class="qty-btn" id="increaseQtyBtn" aria-label="Tambah jumlah">+</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="product-actions">
                        <button type="button" class="btn-add-to-cart add-to-cart-button"
                            data-product-id="<?php echo (int) $product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>"
                            data-product-category="<?php echo htmlspecialchars($product['category'], ENT_QUOTES); ?>"
                            data-product-ukuran="<?php echo htmlspecialchars(isset($product['ukuran']) ? $product['ukuran'] : '', ENT_QUOTES); ?>"
                            data-product-price="<?php echo htmlspecialchars((string) ($product['harga'] ?? 0), ENT_QUOTES); ?>"
                            data-product-stock="<?php echo htmlspecialchars((string) ($product['stok'] ?? 0), ENT_QUOTES); ?>"
                            data-product-paving-rate="<?php echo htmlspecialchars((string) ($product['category'] === 'Paving Block' ? (!empty($product['paving_rate']) ? $product['paving_rate'] : 27) : ''), ENT_QUOTES); ?>"
                            data-product-image="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($product['image'], ENT_QUOTES); ?>"
                            data-requires-calculator="<?php echo isAreaCalculatorCategory($product['category']) ? '1' : '0'; ?>"
                            id="mainAddToCartBtn">
                            <i class="fas fa-cart-plus"></i> TAMBAH KE KERANJANG
                        </button>
                        
                        <a href="index.php#products" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
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
    <script src="js/script.js?v=account-menu-2"></script>
    <script src="js/commerce.js"></script>
    
    <script>
        // Quantity selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantityInput');
            const decreaseBtn = document.getElementById('decreaseQtyBtn');
            const increaseBtn = document.getElementById('increaseQtyBtn');
            const maxStock = <?php echo (int)($product['stok'] ?? 1); ?>;

            if (decreaseBtn && quantityInput) {
                decreaseBtn.addEventListener('click', function() {
                    let value = parseInt(quantityInput.value) || 1;
                    if (value > 1) {
                        quantityInput.value = value - 1;
                    }
                });
            }

            if (increaseBtn && quantityInput) {
                increaseBtn.addEventListener('click', function() {
                    let value = parseInt(quantityInput.value) || 1;
                    if (value < maxStock) {
                        quantityInput.value = value + 1;
                    }
                });
            }

            if (quantityInput) {
                quantityInput.addEventListener('change', function() {
                    let value = parseInt(this.value) || 1;
                    if (value < 1) this.value = 1;
                    if (value > maxStock) this.value = maxStock;
                });
            }

            // Get quantity when adding to cart
            const addToCartBtn = document.getElementById('mainAddToCartBtn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function(e) {
                    const quantity = parseInt(quantityInput.value) || 1;
                    // Store quantity in data attribute for commerce.js to use
                    this.dataset.quantity = quantity;
                });
            }
        });
    </script>
</body>
</html>
