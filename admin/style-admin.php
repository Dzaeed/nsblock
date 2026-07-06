<?php
header('Content-Type: text/css');
?>

/* ========================================
   CSS VARIABLES
   ======================================== */
:root {
    --primary-red: #d32f2f;
    --white: #ffffff;
    --light-gray: #f4f6f9;
    --medium-gray: #e9ecef;
    --dark-gray: #343a40;
    --info-blue: #17a2b8;
    --success-green: #28a745;
    --warning-yellow: #ffc107;
    --danger-red: #dc3545;
    --border-color: #dee2e6;
    --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.15);
    --transition: all 0.3s ease;
}

/* ========================================
   BASE STYLES
   ======================================== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--light-gray);
    margin: 0;
    line-height: 1.6;
    color: var(--dark-gray);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--white);
    padding: 2rem;
    min-height: 100vh;
}

/* ========================================
   TYPOGRAPHY
   ======================================== */
h1 {
    color: var(--dark-gray);
    border-bottom: 2px solid var(--primary-red);
    padding-bottom: 1rem;
    margin: 0 0 2rem 0;
    font-size: 2rem;
    font-weight: 700;
    text-align: center;
}

h1 i {
    margin-right: 0.5rem;
    color: var(--primary-red);
}

h2 {
    color: var(--dark-gray);
    margin: 2rem 0 1.5rem 0;
    font-size: 1.6rem;
    font-weight: 600;
}

/* ========================================
   BUTTONS
   ======================================== */
.btn-group {
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    color: var(--white);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
    text-align: center;
    width: 100%;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.btn i {
    margin-right: 0.5rem;
}

/* Button Variants */
.btn-primary {
    background-color: var(--primary-red);
}

.btn-info {
    background-color: var(--info-blue);
}

.btn-secondary {
    background-color: var(--warning-yellow);
    color: var(--dark-gray);
}

.btn-danger {
    background-color: var(--danger-red);
}

.btn-success {
    background-color: var(--success-green);
}

/* Small buttons for table actions */
td .btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    margin: 0.2rem 0.2rem 0.2rem 0;
    width: auto;
    display: inline-block;
}

/* ========================================
   TABLES
   ======================================== */
.table-container {
    overflow-x: auto;
    margin-top: 1.5rem;
    border-radius: 8px;
    box-shadow: var(--shadow-light);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: var(--white);
    border-radius: 8px;
    overflow: hidden;
    min-width: 600px;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

th {
    background-color: var(--light-gray);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    color: var(--dark-gray);
    white-space: nowrap;
}

tbody tr:nth-child(even) {
    background-color: #fdfdfd;
}

tbody tr:hover {
    background-color: #f1f5f9;
}

/* ========================================
   IMAGES
   ======================================== */
td img {
    max-width: 80px;
    height: auto;
    border-radius: 8px;
    box-shadow: var(--shadow-light);
    display: block;
}

/* ========================================
   FORMS
   ======================================== */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark-gray);
}

.form-group label i {
    margin-right: 0.5rem;
    color: var(--primary-red);
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 1rem;
    transition: var(--transition);
    background-color: var(--white);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-red);
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

/* Current Image Display */
.current-image {
    margin: 1rem 0;
    text-align: center;
}

.product-image {
    max-width: 200px;
    height: auto;
    border-radius: 8px;
    box-shadow: var(--shadow-light);
    transition: var(--transition);
}

.product-image:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-medium);
}

/* File Input Styling */
.file-input {
    padding: 0.6rem;
    border: 2px dashed var(--border-color);
    background-color: var(--light-gray);
    cursor: pointer;
    transition: var(--transition);
}

.file-input:hover {
    border-color: var(--primary-red);
    background-color: rgba(211, 47, 47, 0.05);
}

.file-input:focus {
    border-color: var(--primary-red);
    background-color: var(--white);
}

.file-help {
    color: #6c757d;
    margin-top: 0.5rem;
    display: block;
    font-size: 0.85rem;
    line-height: 1.4;
}

.form-actions {
    margin-top: 2rem;
    border-top: 1px solid var(--border-color);
    padding-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-actions .btn {
    flex: 1;
    min-width: 120px;
    text-align: center;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

/* ========================================
   MESSAGES
   ======================================== */
.message {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.message.warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

/* ========================================
   RESPONSIVE DESIGN - MOBILE FIRST
   ======================================== */

/* Tablet (768px and up) */
@media (min-width: 768px) {
    .container {
        margin: 2rem auto;
        padding: 3rem;
        border-radius: 12px;
        box-shadow: var(--shadow-light);
    }
    
    h1 {
        font-size: 2.2rem;
        text-align: left;
    }
    
    .btn-group {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .btn {
        width: auto;
        flex: 1;
        min-width: 150px;
    }
    
    .form-actions {
        flex-direction: row;
        gap: 1rem;
    }
    
    .form-actions .btn {
        flex: 1;
    }
    
    .product-image {
        max-width: 250px;
    }
}

/* Desktop (1024px and up) */
@media (min-width: 1024px) {
    .container {
        margin: 3rem auto;
        padding: 3rem 4rem;
    }
    
    h1 {
        font-size: 2.5rem;
    }
    
    h2 {
        font-size: 1.8rem;
    }
    
    .btn-group {
        gap: 1.5rem;
    }
    
    .btn {
        padding: 1rem 2rem;
        font-size: 1rem;
    }
    
    th, td {
        padding: 1.2rem;
    }
    
    td img {
        max-width: 120px;
    }
    
    .product-image {
        max-width: 300px;
    }
}

/* Mobile (max-width: 767px) */
@media (max-width: 767px) {
    .container {
        margin: 0;
        padding: 1.5rem;
        border-radius: 0;
        box-shadow: none;
    }
    
    h1 {
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
    }
    
    h2 {
        font-size: 1.4rem;
        margin: 1.5rem 0 1rem 0;
    }
    
    .btn {
        padding: 0.8rem 1.2rem;
        font-size: 0.9rem;
    }
    
    .table-container {
        margin-top: 1rem;
        border-radius: 0;
        box-shadow: none;
    }
    
    table {
        min-width: 100%;
        border-radius: 0;
    }
    
    th, td {
        padding: 0.8rem 0.6rem;
        font-size: 0.9rem;
    }
    
    td img {
        max-width: 60px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 0.7rem;
        font-size: 0.9rem;
    }
    
    .product-image {
        max-width: 150px;
    }
}

/* ========================================
   ADMIN DASHBOARD LAYOUT
   ======================================== */
.dashboard-shell {
    min-height: 100vh;
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    width: 100%;
    background: var(--white);
    border-radius: 18px;
    padding: 1rem 1.5rem;
    box-shadow: var(--shadow-medium);
    margin-bottom: 1.5rem;
    position: sticky;
    top: 0;
    z-index: 20;
    flex-wrap: wrap;
}

.dashboard-navbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    justify-content: center;
    flex: 1;
    min-width: 240px;
}

.dashboard-navbar a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.8rem 1rem;
    border-radius: 14px;
    background: var(--light-gray);
    color: var(--dark-gray);
    text-decoration: none;
    transition: var(--transition);
    font-weight: 600;
}

.dashboard-navbar a:hover,
.dashboard-navbar a.active {
    background: var(--primary-red);
    color: var(--white);
}

.dashboard-home-link {
    background: rgba(211, 47, 47, 0.1);
    color: var(--primary-red);
}

.brand {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-red);
}

.brand i {
    font-size: 1.2rem;
}

.top-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

.top-actions .btn {
    min-width: 140px;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.dashboard-home-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 14px;
    background: rgba(211, 47, 47, 0.12);
    color: var(--primary-red);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.dashboard-home-link:hover {
    background: var(--primary-red);
    color: var(--white);
}

.logout-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
}

.sidebar {
    background: var(--white);
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: var(--shadow-light);
}

.sidebar-brand {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--dark-gray);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1rem;
}

.nav-menu {
    display: grid;
    gap: 0.75rem;
}

.nav-link {
    width: 100%;
    border: none;
    background: var(--light-gray);
    color: var(--dark-gray);
    padding: 0.95rem 1rem;
    border-radius: 14px;
    text-align: left;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
}

.nav-link.active,
.nav-link:hover {
    background-color: var(--primary-red);
    color: var(--white);
}

.main-panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section {
    display: none;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.25s ease;
}

.section.active {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.section-header h1,
.section-header h2 {
    margin: 0;
}

.section-header p {
    margin: 0.5rem 0 0 0;
    color: #606f85;
    max-width: 720px;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.metric-card {
    background: var(--white);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: var(--shadow-light);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.metric-card h3 {
    margin: 0;
    font-size: 0.95rem;
    color: var(--dark-gray);
}

.metric-card span {
    font-size: 2.3rem;
    font-weight: 700;
    color: var(--primary-red);
}

.panel {
    background: var(--white);
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: var(--shadow-light);
    margin-bottom: 1.5rem;
    display: none;
}

.panel.active {
    display: block;
}

.table-action .btn {
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-approved {
    background-color: var(--success-green);
    color: #fff;
}

.status-pending {
    background-color: var(--warning-yellow);
    color: var(--dark-gray);
}

.action-buttons .btn {
    margin: 0.2rem 0.2rem 0.2rem 0;
    padding: 0.45rem 0.9rem;
    font-size: 0.82rem;
}

.message-cell {
    max-width: 300px;
    word-break: break-word;
}

.order-products-list {
    margin: 0;
    padding-left: 1rem;
    min-width: 180px;
}

.order-products-list li {
    margin-bottom: 0.3rem;
}

.payment-proof-thumb {
    width: 78px;
    height: 78px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
}

.status-menunggu-pembayaran {
    background: #ffe8a1;
    color: #704d00;
}

.status-menunggu-konfirmasi {
    background: #fff3cd;
    color: #856404;
}

.status-diproses {
    background: #d1ecf1;
    color: #0c5460;
}

.status-siap-diambil {
    background: #e8f0ff;
    color: #1b4f9c;
}

.status-dalam-pengiriman {
    background: #e5f4ff;
    color: #075985;
}

.status-selesai {
    background: #d4edda;
    color: #155724;
}

.status-dibatalkan {
    background: #f8d7da;
    color: #721c24;
}

.order-status-select {
    min-width: 180px;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--white);
}

.order-actions {
    display: grid;
    gap: 0.45rem;
    min-width: 150px;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 768px) {
    .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 767px) {
    .topbar {
        flex-direction: column;
        align-items: stretch;
    }

    .section-header {
        flex-direction: column;
    }

    .section-header p {
        max-width: 100%;
    }

    .nav-link {
        font-size: 0.92rem;
    }

    .btn {
        width: 100%;
    }
}
