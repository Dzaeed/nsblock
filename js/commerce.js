(function() {
    'use strict';

    var ORDER_KEY = 'nsblock_orders';
    var currentPaymentOrderId = null;
    var proofDataUrl = '';

    function pageUrl(path) {
        return path;
    }

    function currency(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function getUserKey() {
        return (document.body && document.body.dataset.userId) ? document.body.dataset.userId : 'guest';
    }

    function isGuestUser() {
        return String(getUserKey()) === 'guest';
    }

    function requireLoginForOrder(message) {
        if (!isGuestUser()) return true;

        notify(message || 'Silakan login atau registrasi terlebih dahulu untuk memesan.', 'error');
        closePurchaseModal();
        closeOverlay('cartOverlay');
        closeOverlay('checkoutOverlay');
        closeOverlay('ordersOverlay');

        var authLink = document.querySelector('a[href$="auth/login.php"]');
        if (authLink) {
            window.setTimeout(function() {
                authLink.click();
            }, 150);
        } else {
            window.setTimeout(function() {
                window.location.href = pageUrl('auth/login.php');
            }, 700);
        }

        return false;
    }

    function cartKey() {
        return 'nsblock_cart_' + getUserKey();
    }

    function readJson(key, fallback) {
        try {
            var value = localStorage.getItem(key);
            return value ? JSON.parse(value) : fallback;
        } catch (err) {
            return fallback;
        }
    }

    function writeJson(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (err) {
            notify('Penyimpanan browser penuh atau tidak tersedia.', 'error');
        }
    }

    function getCart() {
        return readJson(cartKey(), []);
    }

    function saveCart(items) {
        writeJson(cartKey(), items);
        renderCart();
    }

    function getOrders() {
        return readJson(ORDER_KEY, []);
    }

    function saveOrders(orders) {
        writeJson(ORDER_KEY, orders);
        renderAdminOrders();
        renderUserOrders();
    }

    function notify(message, type) {
        var toast = document.createElement('div');
        toast.className = 'page-toast ' + (type === 'error' ? 'toast-error' : 'toast-success');
        toast.innerHTML = '<span>' + escapeHtml(message) + '</span><button type="button" class="toast-close" aria-label="Tutup notifikasi">&times;</button>';
        document.body.appendChild(toast);
        toast.querySelector('.toast-close').addEventListener('click', function() {
            toast.classList.add('toast-hidden');
        });
        window.setTimeout(function() {
            toast.classList.add('toast-hidden');
            window.setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 350);
        }, 3500);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function productFromButton(button, options) {
        options = options || {};
        var product = {
            id: String(button.dataset.productId || ''),
            baseId: String(button.dataset.productId || ''),
            name: button.dataset.productName || 'Produk',
            category: button.dataset.productCategory || '',
            ukuran: button.dataset.productUkuran || '',
            price: Number(button.dataset.productPrice || 0),
            stock: Number(button.dataset.productStock || 0),
            image: button.dataset.productImage || '',
            qty: 1,
            panjang: null,
            lebar: null,
            luas: null,
            jumlahPerMeter: null,
            totalPrice: Number(button.dataset.productPrice || 0)
        };

        if (options.needResult) {
            product.qty = options.needResult.qty;
            product.panjang = options.needResult.panjang;
            product.lebar = options.needResult.lebar;
            product.luas = options.needResult.luas;
            product.jumlahPerMeter = options.needResult.jumlahPerMeter;
            product.ukuranKebutuhan = options.needResult.panjang + ' m x ' + options.needResult.lebar + ' m';
            product.id = product.baseId + ':area:' + options.needResult.panjang + 'x' + options.needResult.lebar + ':' + options.needResult.jumlahPerMeter;
        } else if (options.qty) {
            product.qty = options.qty;
        }

        product.qty = Math.ceil(Number(product.qty || 0));
        product.totalPrice = product.qty * product.price;
        return product;
    }

    function getUnitQuantity(wrapper) {
        var input = wrapper.querySelector('.unit-quantity-input');
        var qty = Math.ceil(Number(input ? input.value : 1));
        if (!Number.isFinite(qty) || qty <= 0) {
            notify('Jumlah barang harus lebih dari 0.', 'error');
            return null;
        }
        if (input) input.value = qty;
        return qty;
    }

    function setCalculatorMessage(calculator, html, type) {
        var message = calculator.querySelector('.calculator-message');
        if (!message) return;
        message.className = 'calculator-message' + (type === 'error' ? ' calculator-error' : (type === 'success' ? ' calculator-success' : ''));
        message.innerHTML = html;
    }

    function calculateNeed(calculator) {
        var lengthInput = calculator.querySelector('.calculator-length');
        var widthInput = calculator.querySelector('.calculator-width');
        var panjangText = lengthInput ? String(lengthInput.value).trim() : '';
        var lebarText = widthInput ? String(widthInput.value).trim() : '';

        if (!panjangText || !lebarText) {
            setCalculatorMessage(calculator, 'Masukkan panjang dan lebar terlebih dahulu.', 'error');
            return null;
        }

        var panjang = Number(panjangText);
        var lebar = Number(lebarText);
        if (!Number.isFinite(panjang) || !Number.isFinite(lebar)) {
            setCalculatorMessage(calculator, 'Masukkan panjang dan lebar terlebih dahulu.', 'error');
            return null;
        }
        if (panjang <= 0 || lebar <= 0) {
            setCalculatorMessage(calculator, 'Ukuran harus lebih dari 0.', 'error');
            return null;
        }

        var type = calculator.dataset.calculatorType || 'roster';
        var configuredPavingRate = Number(calculator.dataset.pavingRate || 27);
        var jumlahPerMeter = type === 'paving' ? configuredPavingRate : 25;
        var luas = panjang * lebar;
        var qty = Math.ceil(luas * jumlahPerMeter);
        if (luas > 0 && qty < 1) qty = 1;

        setCalculatorMessage(
            calculator,
            '<div class="calculator-result-grid">' +
                '<div><span>Luas</span><strong>' + formatDecimal(luas) + ' m&sup2;</strong></div>' +
                '<div><span>Jumlah</span><strong>' + qty + ' pcs</strong></div>' +
            '</div>',
            'success'
        );

        return {
            panjang: trimDecimal(panjang),
            lebar: trimDecimal(lebar),
            luas: trimDecimal(luas),
            jumlahPerMeter: jumlahPerMeter,
            qty: qty
        };
    }

    function formatDecimal(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        });
    }

    function trimDecimal(value) {
        return Number(Number(value || 0).toFixed(4));
    }

    function closePurchaseModal() {
        var overlay = document.getElementById('purchaseModalOverlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        if (!document.querySelector('.commerce-overlay.active, .product-modal-overlay.active')) {
            document.body.style.overflow = '';
        }
    }

    function productCategoryType(button) {
        return String(button.dataset.productCategory || '').toLowerCase() === 'paving block' ? 'paving' : 'roster';
    }

    function purchaseModalHeader(productName) {
        return '<div class="commerce-modal-header">' +
            '<div>' +
                '<h2>' + escapeHtml(productName) + '</h2>' +
                '<p>Atur jumlah sebelum masuk keranjang.</p>' +
            '</div>' +
            '<button type="button" class="auth-modal-close" data-purchase-close aria-label="Tutup"><i class="fas fa-times"></i></button>' +
        '</div>';
    }

    function openPurchaseModal(button) {
        closePurchaseModal();
        var isAreaProduct = button.dataset.requiresCalculator === '1';
        var overlay = document.createElement('div');
        overlay.id = 'purchaseModalOverlay';
        overlay.className = 'commerce-overlay purchase-modal-overlay active';
        overlay.setAttribute('aria-hidden', 'false');

        var productName = button.dataset.productName || 'Produk';
        var productPrice = currency(button.dataset.productPrice || 0);
        var productStock = Number(button.dataset.productStock || 0);
        var body = isAreaProduct ? areaPurchaseBody(button, productPrice, productStock) : unitPurchaseBody(productPrice, productStock);
        overlay.innerHTML = '<div class="commerce-modal purchase-modal" role="dialog" aria-modal="true">' +
            purchaseModalHeader(productName) +
            body +
        '</div>';

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        bindPurchaseModal(overlay, button, isAreaProduct);
    }

    function areaPurchaseBody(button, productPrice, productStock) {
        var type = productCategoryType(button);
        var pavingRate = Number(button.dataset.productPavingRate || 27);
        if ([27, 44].indexOf(pavingRate) === -1) pavingRate = 27;
        var pavingRateInfo = type === 'paving' ?
            '<div class="calculator-rate-note"><span>Tipe paving</span><strong>' + pavingRate + ' pcs/m&sup2;</strong></div>' : '';

        return '<div class="purchase-product-meta">' +
                '<span>' + productPrice + '</span><span>Stok: ' + productStock + '</span>' +
            '</div>' +
            '<div class="product-calculator purchase-calculator" data-calculator-type="' + type + '" data-paving-rate="' + pavingRate + '">' +
                '<div class="calculator-fields">' +
                    '<label>Panjang area (m)<input type="number" class="calculator-length" min="0" step="0.01" inputmode="decimal" placeholder="1.25"></label>' +
                    '<label>Lebar area (m)<input type="number" class="calculator-width" min="0" step="0.01" inputmode="decimal" placeholder="1.25"></label>' +
                '</div>' +
                pavingRateInfo +
                '<button type="button" class="btn calculate-need-button">Hitung Kebutuhan</button>' +
                '<div class="calculator-message" aria-live="polite"></div>' +
            '</div>' +
            '<div class="direct-quantity-panel">' +
                '<span>Atau tambah jumlah langsung</span>' +
                '<div class="unit-quantity purchase-unit-quantity" data-unit-quantity>' +
                    '<button type="button" data-unit-action="decrease" aria-label="Kurangi jumlah">-</button>' +
                    '<input type="number" class="unit-quantity-input" min="1" step="1" value="1" inputmode="numeric" aria-label="Jumlah barang">' +
                    '<button type="button" data-unit-action="increase" aria-label="Tambah jumlah">+</button>' +
                '</div>' +
            '</div>' +
            '<div class="purchase-modal-actions">' +
                '<button type="button" class="btn purchase-add-button" data-purchase-add>Tambah ke Keranjang</button>' +
            '</div>';
    }

    function unitPurchaseBody(productPrice, productStock) {
        return '<div class="purchase-product-meta">' +
                '<span>' + productPrice + '</span><span>Stok: ' + productStock + '</span>' +
            '</div>' +
            '<div class="unit-quantity purchase-unit-quantity" data-unit-quantity>' +
                '<button type="button" data-unit-action="decrease" aria-label="Kurangi jumlah">-</button>' +
                '<input type="number" class="unit-quantity-input" min="1" step="1" value="1" inputmode="numeric" aria-label="Jumlah barang">' +
                '<button type="button" data-unit-action="increase" aria-label="Tambah jumlah">+</button>' +
            '</div>' +
            '<div class="purchase-modal-actions">' +
                '<button type="button" class="btn purchase-add-button" data-purchase-add>Tambah ke Keranjang</button>' +
            '</div>';
    }

    function bindPurchaseModal(overlay, button, isAreaProduct) {
        var needResult = null;

        overlay.addEventListener('click', function(event) {
            if (event.target === overlay || event.target.closest('[data-purchase-close]')) {
                closePurchaseModal();
                return;
            }

            var calculateButton = event.target.closest('.calculate-need-button');
            if (calculateButton) {
                var calculator = overlay.querySelector('.product-calculator');
                needResult = calculator ? calculateNeed(calculator) : null;
                return;
            }

            var actionButton = event.target.closest('[data-unit-action]');
            if (actionButton) {
                var input = overlay.querySelector('.unit-quantity-input');
                var qty = Math.ceil(Number(input ? input.value : 1));
                if (!Number.isFinite(qty) || qty < 1) qty = 1;
                input.value = actionButton.dataset.unitAction === 'increase' ? qty + 1 : Math.max(1, qty - 1);
                return;
            }

            var addButton = event.target.closest('[data-purchase-add]');
            if (addButton) {
                var product = null;
                if (isAreaProduct) {
                    if (needResult) {
                        product = productFromButton(button, { needResult: needResult });
                    } else {
                        var directQtyWrapper = overlay.querySelector('[data-unit-quantity]');
                        var directQty = getUnitQuantity(directQtyWrapper);
                        if (!directQty) return;
                        product = productFromButton(button, { qty: directQty });
                    }
                } else {
                    var qtyWrapper = overlay.querySelector('[data-unit-quantity]');
                    var qty = getUnitQuantity(qtyWrapper);
                    if (!qty) return;
                    product = productFromButton(button, { qty: qty });
                }
                if (addToCart(product)) {
                    closePurchaseModal();
                }
            }
        });

        overlay.addEventListener('input', function(event) {
            if (!isAreaProduct || !event.target.matches('.calculator-length, .calculator-width')) return;
            needResult = null;
            var calculator = overlay.querySelector('.product-calculator');
            if (calculator) setCalculatorMessage(calculator, '', '');
        });
    }

    function addToCart(product) {
        if (!product) return false;
        if (!product.id) return false;
        if (product.stock <= 0) {
            notify('Stok produk belum tersedia.', 'error');
            return false;
        }
        product.qty = Math.ceil(Number(product.qty || 1));
        if (!Number.isFinite(product.qty) || product.qty <= 0) {
            notify('Jumlah barang harus lebih dari 0.', 'error');
            return false;
        }

        var cart = getCart();
        var found = cart.find(function(item) { return item.id === product.id; });
        if (found) {
            if (Number(found.qty || 0) + product.qty > product.stock) {
                notify('Jumlah sudah mencapai stok tersedia.', 'error');
                return false;
            }
            found.qty += product.qty;
            found.totalPrice = found.qty * Number(found.price || 0);
        } else {
            if (product.qty > product.stock) {
                notify('Jumlah melebihi stok tersedia.', 'error');
                return false;
            }
            product.totalPrice = product.qty * Number(product.price || 0);
            cart.push(product);
        }

        saveCart(cart);
        notify('Produk berhasil ditambahkan ke keranjang.');
        return true;
    }

    function cartTotals(items) {
        return items.reduce(function(total, item) {
            total.qty += Number(item.qty || 0);
            total.price += Number(item.qty || 0) * Number(item.price || 0);
            return total;
        }, { qty: 0, price: 0 });
    }

    function renderCart() {
        var cart = getCart();
        var totals = cartTotals(cart);
        var badge = document.getElementById('cartBadge');
        var itemsWrap = document.getElementById('cartItems');
        var totalItems = document.getElementById('cartTotalItems');
        var subtotal = document.getElementById('cartSubtotal');

        if (badge) badge.textContent = totals.qty;
        if (totalItems) totalItems.textContent = totals.qty;
        if (subtotal) subtotal.textContent = currency(totals.price);
        if (!itemsWrap) return;

        if (!cart.length) {
            itemsWrap.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-basket" aria-hidden="true"></i><strong>Keranjang masih kosong</strong><span>Pilih produk dari katalog, lalu atur jumlahnya di sini.</span></div>';
            return;
        }

        itemsWrap.innerHTML = cart.map(function(item) {
            var detail = item.category + (item.ukuran ? ' - ' + item.ukuran : '');
            if (item.ukuranKebutuhan) detail += ' - Area ' + item.ukuranKebutuhan;
            if (item.luas) detail += ' - Luas ' + formatDecimal(item.luas) + ' m2';
            return '<article class="cart-item" data-cart-id="' + escapeHtml(item.id) + '">' +
                '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '">' +
                '<div class="cart-item-info">' +
                    '<h3>' + escapeHtml(item.name) + '</h3>' +
                    '<p>' + escapeHtml(detail) + '</p>' +
                    '<strong>' + currency(item.price) + '</strong>' +
                '</div>' +
                '<div class="qty-control">' +
                    '<button type="button" data-cart-action="decrease" aria-label="Kurangi">-</button>' +
                    '<span>' + Number(item.qty || 0) + '</span>' +
                    '<button type="button" data-cart-action="increase" aria-label="Tambah">+</button>' +
                    '<button type="button" data-cart-action="remove" aria-label="Hapus"><i class="fas fa-trash"></i></button>' +
                '</div>' +
            '</article>';
        }).join('');
    }

    function updateCartItem(id, action) {
        var cart = getCart();
        var index = cart.findIndex(function(item) { return item.id === id; });
        if (index === -1) return;

        if (action === 'remove') {
            if (!window.confirm('Yakin ingin menghapus item ini dari keranjang?')) return;
            cart.splice(index, 1);
        } else if (action === 'increase') {
            if (cart[index].qty >= cart[index].stock) {
                notify('Jumlah sudah mencapai stok tersedia.', 'error');
                return;
            }
            cart[index].qty += 1;
            cart[index].totalPrice = cart[index].qty * Number(cart[index].price || 0);
        } else if (action === 'decrease') {
            cart[index].qty -= 1;
            if (cart[index].qty <= 0) {
                if (!window.confirm('Jumlah menjadi 0. Hapus item dari keranjang?')) {
                    cart[index].qty = 1;
                    cart[index].totalPrice = cart[index].qty * Number(cart[index].price || 0);
                    return;
                }
                cart.splice(index, 1);
            } else {
                cart[index].totalPrice = cart[index].qty * Number(cart[index].price || 0);
            }
        }

        saveCart(cart);
        notify('Jumlah produk diubah.');
    }

    function openOverlay(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeOverlay(id) {
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.commerce-overlay.active')) {
            document.body.style.overflow = '';
        }
    }

    function paymentMethodLabel(method) {
        var labels = {
            qris: 'QRIS'
        };
        return labels[method] || 'QRIS';
    }

    function orderCondition(status) {
        var map = {
            'Menunggu Pembayaran': 'Menunggu pembayaran dari pelanggan.',
            'Menunggu Konfirmasi': 'Bukti pembayaran sedang dicek admin.',
            'Diproses': 'Barang sedang disiapkan oleh tim NS BLOCK.',
            'Siap Diambil': 'Barang sudah siap diambil di toko.',
            'Dalam Pengiriman': 'Barang sedang dalam proses pengiriman.',
            'Selesai': 'Pesanan selesai.',
            'Dibatalkan': 'Pesanan dibatalkan.'
        };
        return map[status] || 'Status pesanan sedang diperbarui.';
    }

    function orderProgress(status) {
        var steps = ['Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dalam Pengiriman', 'Selesai'];
        if (status === 'Siap Diambil') steps = ['Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Siap Diambil', 'Selesai'];
        if (status === 'Dibatalkan') return 0;
        var index = steps.indexOf(status);
        return index === -1 ? 20 : Math.round(((index + 1) / steps.length) * 100);
    }

    function fillCheckout() {
        var cart = getCart();
        var summary = document.getElementById('checkoutSummaryItems');
        var total = document.getElementById('checkoutTotal');
        var totals = cartTotals(cart);
        var form = document.getElementById('checkoutForm');

        if (form && document.body) {
            form.elements.name.value = document.body.dataset.userName || '';
            form.elements.email.value = document.body.dataset.userEmail || '';
        }

        if (summary) {
            summary.innerHTML = cart.map(function(item) {
                var detail = item.ukuranKebutuhan ? ' (' + item.ukuranKebutuhan + ', ' + formatDecimal(item.luas) + ' m2)' : '';
                return '<div class="checkout-line">' +
                    '<span>' + escapeHtml(item.name + detail) + ' x ' + Number(item.qty || 0) + '</span>' +
                    '<small>' + currency(item.price) + ' / item</small>' +
                    '<strong>' + currency(Number(item.price || 0) * Number(item.qty || 0)) + '</strong>' +
                '</div>';
            }).join('');
        }

        if (total) total.textContent = currency(totals.price);
    }

    function nextOrderId(orders) {
        var now = new Date();
        var y = now.getFullYear();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        var prefix = 'NSB-' + y + m + d + '-';
        var count = orders.filter(function(order) {
            return String(order.id || '').indexOf(prefix) === 0;
        }).length + 1;
        return prefix + String(count).padStart(3, '0');
    }

    function handleCheckoutSubmit(event) {
        event.preventDefault();
        if (!requireLoginForOrder()) return;

        var cart = getCart();
        if (!cart.length) {
            notify('Keranjang masih kosong.', 'error');
            return;
        }

        var form = event.currentTarget;
        var orders = getOrders();
        var totals = cartTotals(cart);
        var order = {
            id: nextOrderId(orders),
            userKey: getUserKey(),
            customer: {
                name: form.elements.name.value,
                email: form.elements.email.value,
                whatsapp: form.elements.whatsapp.value,
                address: form.elements.address.value,
                note: form.elements.note.value,
                delivery: form.elements.delivery.value
            },
            items: cart,
            totalItems: totals.qty,
            total: totals.price,
            paymentMethod: 'qris',
            status: 'Menunggu Pembayaran',
            proof: '',
            createdAt: new Date().toISOString()
        };

        orders.push(order);
        saveOrders(orders);
        currentPaymentOrderId = order.id;
        closeOverlay('checkoutOverlay');
        showPayment(order);
        openOverlay('paymentOverlay');
        notify('Checkout berhasil. Silakan lanjutkan pembayaran QRIS.');
    }

    function showPayment(order) {
        var id = document.getElementById('paymentOrderId');
        var title = document.getElementById('paymentTitle');
        var total = document.getElementById('paymentTotal');
        var input = document.getElementById('paymentProofInput');
        var preview = document.getElementById('paymentProofPreview');
        var steps = document.getElementById('paymentSteps');
        var qrisPanel = document.getElementById('qrisPaymentPanel');
        proofDataUrl = '';
        if (title) title.textContent = 'Pembayaran QRIS';
        if (id) id.textContent = 'Nomor pesanan: ' + order.id;
        if (total) total.textContent = currency(order.total);
        if (qrisPanel) qrisPanel.hidden = false;
        if (steps) {
            steps.innerHTML = '<li>Scan QRIS NS BLOCK.</li><li>Bayar sesuai total pesanan.</li><li>Upload bukti pembayaran untuk konfirmasi admin.</li>';
        }
        if (input) input.value = '';
        if (preview) {
            preview.src = '';
            preview.classList.remove('active');
        }
    }

    function submitProof() {
        if (!currentPaymentOrderId) return;
        if (!proofDataUrl) {
            notify('Upload bukti pembayaran terlebih dahulu.', 'error');
            return;
        }

        var orders = getOrders();
        var order = orders.find(function(item) { return item.id === currentPaymentOrderId; });
        if (!order) return;
        order.proof = proofDataUrl;
        order.status = 'Menunggu Konfirmasi';
        order.updatedAt = new Date().toISOString();
        saveOrders(orders);
        saveCart([]);
        closeOverlay('paymentOverlay');
        notify('Bukti pembayaran berhasil dikirim. Status pesanan menunggu konfirmasi admin.');
        if (document.body && document.body.dataset.commercePage) {
            window.location.href = pageUrl('orders.php');
        } else {
            openOverlay('ordersOverlay');
        }
    }

    function statusClass(status) {
        return 'status-' + String(status || '').toLowerCase().replace(/\s+/g, '-');
    }

    function renderAdminOrders() {
        var body = document.getElementById('adminOrdersBody');
        var orders = getOrders();
        var total = document.getElementById('adminTotalOrders');
        var waiting = document.getElementById('adminWaitingOrders');
        var processing = document.getElementById('adminProcessingOrders');
        var done = document.getElementById('adminDoneOrders');

        if (total) total.textContent = orders.length;
        if (waiting) waiting.textContent = orders.filter(function(order) { return order.status === 'Menunggu Konfirmasi'; }).length;
        if (processing) processing.textContent = orders.filter(function(order) {
            return ['Diproses', 'Siap Diambil', 'Dalam Pengiriman'].indexOf(order.status) !== -1;
        }).length;
        if (done) done.textContent = orders.filter(function(order) { return order.status === 'Selesai'; }).length;
        if (!body) return;

        if (!orders.length) {
            body.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Belum ada pesanan.</td></tr>';
            return;
        }

        body.innerHTML = orders.slice().reverse().map(function(order) {
            var proof = order.proof ? '<img src="' + order.proof + '" class="payment-proof-thumb" alt="Bukti pembayaran" data-proof-view>' : '<span class="text-muted">Belum ada</span>';
            var products = '<ul class="order-products-list">' + order.items.map(function(item) {
                var detail = item.ukuranKebutuhan ? ' (' + item.ukuranKebutuhan + ', ' + formatDecimal(item.luas) + ' m2)' : '';
                return '<li>' + escapeHtml(item.name + detail) + ' x ' + Number(item.qty || 0) + '</li>';
            }).join('') + '</ul>';
            return '<tr data-order-id="' + escapeHtml(order.id) + '">' +
                '<td>' + escapeHtml(order.id) + '</td>' +
                '<td>' + escapeHtml(order.customer.name) + '<br><small>' + escapeHtml(order.customer.email) + '</small></td>' +
                '<td>' + escapeHtml(order.customer.whatsapp) + '</td>' +
                '<td>' + products + '</td>' +
                '<td>' + currency(order.total) + '</td>' +
                '<td>' + escapeHtml(paymentMethodLabel(order.paymentMethod)) + '</td>' +
                '<td>' + proof + '</td>' +
                '<td>' + new Date(order.createdAt).toLocaleString('id-ID') + '</td>' +
                '<td><span class="status-badge ' + statusClass(order.status) + '">' + escapeHtml(order.status) + '</span></td>' +
                '<td class="order-actions">' +
                    '<select class="order-status-select" data-order-action="status">' +
                        statusOption(order.status, 'Menunggu Pembayaran') +
                        statusOption(order.status, 'Menunggu Konfirmasi') +
                        statusOption(order.status, 'Diproses') +
                        statusOption(order.status, 'Siap Diambil') +
                        statusOption(order.status, 'Dalam Pengiriman') +
                        statusOption(order.status, 'Selesai') +
                        statusOption(order.status, 'Dibatalkan') +
                    '</select>' +
                    '<button type="button" class="btn btn-danger" data-order-action="delete"><i class="fas fa-trash"></i> Hapus</button>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    function statusOption(current, value) {
        return '<option value="' + value + '"' + (current === value ? ' selected' : '') + '>' + value + '</option>';
    }

    function updateOrderStatus(id, status) {
        var orders = getOrders();
        var order = orders.find(function(item) { return item.id === id; });
        if (!order) return;
        if (status === 'Dibatalkan' && !window.confirm('Yakin ingin membatalkan pesanan ini?')) {
            renderAdminOrders();
            return;
        }
        order.status = status;
        order.updatedAt = new Date().toISOString();
        saveOrders(orders);
        notify('Status pesanan diubah admin.');
    }

    function deleteOrder(id) {
        if (!window.confirm('Yakin ingin menghapus pesanan ini?')) return;
        var orders = getOrders().filter(function(order) { return order.id !== id; });
        saveOrders(orders);
        notify('Pesanan berhasil dihapus.');
    }

    function renderUserOrders() {
        var list = document.getElementById('userOrdersList');
        if (!list) return;
        var userKey = getUserKey();
        var orders = getOrders().filter(function(order) {
            return String(order.userKey || 'guest') === String(userKey);
        }).slice().reverse();

        if (!orders.length) {
            list.innerHTML = '<div class="cart-empty">Belum ada pesanan pada akun atau browser ini.</div>';
            return;
        }

        list.innerHTML = orders.map(function(order) {
            var items = (order.items || []).map(function(item) {
                var detail = item.ukuranKebutuhan ? ' - Area ' + item.ukuranKebutuhan : '';
                return '<li>' + escapeHtml(item.name + detail) + ' <strong>x' + Number(item.qty || 0) + '</strong></li>';
            }).join('');
            var status = order.status || 'Menunggu Pembayaran';
            var payAction = status === 'Menunggu Pembayaran'
                ? '<button type="button" class="btn" data-order-pay="' + escapeHtml(order.id) + '">Lanjut Bayar</button>'
                : '';
            return '<article class="user-order-card">' +
                '<div class="user-order-head">' +
                    '<div><span>ID Pesanan</span><strong>' + escapeHtml(order.id) + '</strong></div>' +
                    '<span class="status-badge ' + statusClass(status) + '">' + escapeHtml(status) + '</span>' +
                '</div>' +
                '<div class="order-progress" aria-hidden="true"><span style="width:' + orderProgress(status) + '%"></span></div>' +
                '<p class="order-condition">' + escapeHtml(orderCondition(status)) + '</p>' +
                '<div class="user-order-meta">' +
                    '<div><span>Total</span><strong>' + currency(order.total) + '</strong></div>' +
                    '<div><span>Metode</span><strong>' + escapeHtml(paymentMethodLabel(order.paymentMethod)) + '</strong></div>' +
                    '<div><span>Tanggal</span><strong>' + new Date(order.createdAt).toLocaleDateString('id-ID') + '</strong></div>' +
                '</div>' +
                '<ul class="user-order-items">' + items + '</ul>' +
                payAction +
            '</article>';
        }).join('');
    }

    function bindFrontend() {
        document.querySelectorAll('.add-to-cart-button, #productModalCartButton').forEach(function(button) {
            button.addEventListener('click', function() {
                if (!requireLoginForOrder('Silakan login atau registrasi terlebih dahulu sebelum menambahkan produk ke keranjang.')) return;
                openPurchaseModal(button);
            });
        });

        var cartOpen = document.getElementById('cartOpenBtn');
        if (cartOpen) cartOpen.addEventListener('click', function() {
            if (!requireLoginForOrder('Silakan login atau registrasi terlebih dahulu untuk membuka keranjang.')) return;
            if (document.getElementById('cartOverlay')) {
                openOverlay('cartOverlay');
            } else {
                window.location.href = pageUrl('checkout.php');
            }
        });

        var ordersOpen = document.getElementById('userOrdersOpenBtn');
        if (ordersOpen) ordersOpen.addEventListener('click', function() {
            if (!requireLoginForOrder('Silakan login atau registrasi terlebih dahulu untuk melihat pesanan.')) return;
            window.location.href = pageUrl('orders.php');
        });

        var userOrdersList = document.getElementById('userOrdersList');
        if (userOrdersList) {
            userOrdersList.addEventListener('click', function(event) {
                var payButton = event.target.closest('[data-order-pay]');
                if (!payButton) return;
                if (!requireLoginForOrder('Silakan login atau registrasi terlebih dahulu untuk melanjutkan pembayaran.')) return;
                var order = getOrders().find(function(item) { return item.id === payButton.dataset.orderPay; });
                if (!order) return;
                currentPaymentOrderId = order.id;
                showPayment(order);
                closeOverlay('ordersOverlay');
                openOverlay('paymentOverlay');
            });
        }

        document.querySelectorAll('[data-commerce-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                closeOverlay(button.dataset.commerceClose + 'Overlay');
            });
        });

        document.querySelectorAll('.commerce-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(event) {
                if (event.target === overlay) closeOverlay(overlay.id);
            });
        });

        var cartItems = document.getElementById('cartItems');
        if (cartItems) {
            cartItems.addEventListener('click', function(event) {
                var actionButton = event.target.closest('[data-cart-action]');
                if (!actionButton) return;
                var item = event.target.closest('.cart-item');
                updateCartItem(item.dataset.cartId, actionButton.dataset.cartAction);
            });
        }

        var clearCart = document.getElementById('clearCartBtn');
        if (clearCart) clearCart.addEventListener('click', function() {
            if (getCart().length && window.confirm('Yakin ingin mengosongkan keranjang?')) {
                saveCart([]);
                notify('Keranjang dikosongkan.');
            }
        });

        var cancelCart = document.getElementById('cancelCartBtn');
        if (cancelCart) cancelCart.addEventListener('click', function() {
            if (window.confirm('Yakin ingin membatalkan pesanan sebelum checkout?')) {
                saveCart([]);
                closeOverlay('cartOverlay');
                notify('Pesanan dibatalkan.');
            }
        });

        var checkoutOpen = document.getElementById('checkoutOpenBtn');
        if (checkoutOpen) checkoutOpen.addEventListener('click', function() {
            if (!requireLoginForOrder()) return;
            if (!getCart().length) {
                notify('Keranjang masih kosong.', 'error');
                return;
            }
            window.location.href = pageUrl('checkout.php');
        });

        var checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            fillCheckout();
            checkoutForm.addEventListener('submit', handleCheckoutSubmit);
        }

        var proofInput = document.getElementById('paymentProofInput');
        if (proofInput) proofInput.addEventListener('change', function() {
            var file = proofInput.files && proofInput.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(event) {
                proofDataUrl = event.target.result;
                var preview = document.getElementById('paymentProofPreview');
                if (preview) {
                    preview.src = proofDataUrl;
                    preview.classList.add('active');
                }
            };
            reader.readAsDataURL(file);
        });

        var submitProofButton = document.getElementById('submitProofBtn');
        if (submitProofButton) submitProofButton.addEventListener('click', submitProof);
    }

    function bindAdmin() {
        var body = document.getElementById('adminOrdersBody');
        if (!body) return;

        body.addEventListener('change', function(event) {
            if (event.target.matches('[data-order-action="status"]')) {
                var row = event.target.closest('tr');
                updateOrderStatus(row.dataset.orderId, event.target.value);
            }
        });

        body.addEventListener('click', function(event) {
            var proof = event.target.closest('[data-proof-view]');
            if (proof) {
                window.open(proof.src, '_blank');
                return;
            }
            var deleteButton = event.target.closest('[data-order-action="delete"]');
            if (deleteButton) {
                var row = deleteButton.closest('tr');
                deleteOrder(row.dataset.orderId);
            }
        });
    }

    function init() {
        bindFrontend();
        bindAdmin();
        renderCart();
        renderAdminOrders();
        renderUserOrders();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
