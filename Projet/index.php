<?php
/**
 * ============================================================
 *  SALAH SPORT - E-Commerce Website
 *  Single-file PHP application (index.php)
 *  Features: Products, Cart (JS), Checkout (PHP), Confirmation
 * ============================================================
 */

// ── PHP: Handle checkout form submission ──────────────────────
$orderSuccess = false;
$orderError   = '';
$orderData    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $name    = trim($_POST['name']    ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $email   = trim($_POST['email']   ?? '');
    $items   = trim($_POST['cart_items'] ?? '');
    $total   = trim($_POST['cart_total'] ?? '0');

    if (!$name || !$address || !$phone) {
        $orderError = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!preg_match('/^[0-9+\s\-]{6,20}$/', $phone)) {
        $orderError = 'Numéro de téléphone invalide.';
    } else {
        $orderData = [
            'name'    => htmlspecialchars($name),
            'address' => htmlspecialchars($address),
            'phone'   => htmlspecialchars($phone),
            'email'   => htmlspecialchars($email),
            'items'   => htmlspecialchars($items),
            'total'   => htmlspecialchars($total),
            'ref'     => 'SS-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'date'    => date('d/m/Y H:i'),
        ];
        $orderSuccess = true;

        // ── Save order to a simple text-based "database" ────
        $logDir  = __DIR__ . '/orders';
        if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
        $logFile = $logDir . '/orders.txt';
        $line    = implode(' | ', [
            $orderData['ref'],
            $orderData['date'],
            $orderData['name'],
            $orderData['phone'],
            $orderData['email'],
            $orderData['address'],
            $orderData['total'] . ' MAD',
            $orderData['items'],
        ]) . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Salah Sport – Équipements Sportifs</title>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

<style>
/* ============================================================
   CSS RESET & VARIABLES
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --black:   #ff0000ff;
    --white:   #f5f5f0;
    --green:   #00e676;
    --green2:  #00c853;
    --gray:    #1a1a1a;
    --gray2:   #2a2a2a;
    --gray3:   #3d3d3d;
    --muted:   #888;
    --red:     #ff3b3b;
    --font-display: 'Bebas Neue', sans-serif;
    --font-body:    'DM Sans', sans-serif;
    --radius:  12px;
    --shadow:  0 8px 32px rgba(0,0,0,.45);
    --transition: .28s cubic-bezier(.4,0,.2,1);
}

html { scroll-behavior: smooth; }

body {
    background: var(--black);
    color: var(--white);
    font-family: var(--font-body);
    font-size: 16px;
    line-height: 1.6;
    overflow-x: hidden;
}

a { text-decoration: none; color: inherit; }
img { max-width: 100%; display: block; }
button { cursor: pointer; border: none; font-family: var(--font-body); }

/* ============================================================
   SCROLLBAR
   ============================================================ */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--gray); }
::-webkit-scrollbar-thumb { background: var(--green); border-radius: 4px; }

/* ============================================================
   NAVBAR
   ============================================================ */
#navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5vw;
    height: 68px;
    background: rgba(10,10,10,.85);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(255,255,255,.06);
    transition: var(--transition);
}

.logo {
    font-family: var(--font-display);
    font-size: 2rem;
    letter-spacing: 3px;
    color: var(--white);
}
.logo span { color: var(--green); }

.nav-links {
    display: flex;
    gap: 2rem;
    list-style: none;
}
.nav-links a {
    font-size: .9rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--muted);
    transition: color var(--transition);
    position: relative;
}
.nav-links a::after {
    content: '';
    position: absolute;
    bottom: -4px; left: 0;
    width: 0; height: 2px;
    background: var(--green);
    transition: width var(--transition);
}
.nav-links a:hover { color: var(--white); }
.nav-links a:hover::after { width: 100%; }

/* Cart icon */
#cart-icon {
    position: relative;
    background: var(--gray2);
    border: 1px solid var(--gray3);
    color: var(--white);
    padding: .55rem 1.1rem;
    border-radius: 50px;
    font-size: .85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .5rem;
    transition: background var(--transition), border-color var(--transition);
}
#cart-icon:hover { background: var(--green); color: var(--black); border-color: var(--green); }
#cart-count {
    background: var(--green);
    color: var(--black);
    font-size: .7rem;
    font-weight: 700;
    width: 20px; height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s;
}
#cart-icon:hover #cart-count { background: var(--black); color: var(--green); }

/* Hamburger */
.hamburger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: transparent;
    padding: 4px;
}
.hamburger span {
    display: block;
    width: 24px; height: 2px;
    background: var(--white);
    border-radius: 2px;
    transition: var(--transition);
}

/* Mobile menu */
#mobile-menu {
    display: none;
    position: fixed;
    top: 68px; left: 0; right: 0;
    background: rgba(10,10,10,.98);
    padding: 2rem 5vw;
    z-index: 999;
    flex-direction: column;
    gap: 1.2rem;
    border-bottom: 1px solid var(--gray3);
}
#mobile-menu a {
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--muted);
}
#mobile-menu a:hover { color: var(--green); }
#mobile-menu.open { display: flex; }

/* ============================================================
   HERO
   ============================================================ */
#home {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 80px 5vw 60px;
    position: relative;
    overflow: hidden;
}

/* Animated grid background */
#home::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(0,230,118,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,230,118,.04) 1px, transparent 1px);
    background-size: 60px 60px;
    animation: gridMove 20s linear infinite;
}

/* Glow blob */
#home::after {
    content: '';
    position: absolute;
    top: 20%; left: 50%;
    transform: translate(-50%, -50%);
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(0,230,118,.12) 0%, transparent 70%);
    animation: pulse 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes gridMove { from { transform: translateY(0); } to { transform: translateY(60px); } }
@keyframes pulse { 0%,100% { transform: translate(-50%,-50%) scale(1); opacity: .7; } 50% { transform: translate(-50%,-50%) scale(1.15); opacity: 1; } }

.hero-content { position: relative; z-index: 1; max-width: 820px; }

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(0,230,118,.1);
    border: 1px solid rgba(0,230,118,.3);
    color: var(--green);
    padding: .4rem 1rem;
    border-radius: 50px;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 1.5rem;
    animation: fadeUp .8s ease both;
}

.hero-title {
    font-family: var(--font-display);
    font-size: clamp(3.5rem, 10vw, 8rem);
    line-height: .95;
    letter-spacing: 4px;
    color: var(--white);
    animation: fadeUp .8s .15s ease both;
}
.hero-title span { color: var(--green); }

.hero-subtitle {
    font-size: 1.1rem;
    color: var(--muted);
    max-width: 520px;
    margin: 1.5rem auto 2.5rem;
    font-weight: 300;
    animation: fadeUp .8s .3s ease both;
}

.hero-cta {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeUp .8s .45s ease both;
}

.btn-primary {
    background: var(--green);
    color: var(--black);
    padding: .85rem 2.2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: .95rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    transition: transform var(--transition), box-shadow var(--transition), background var(--transition);
    box-shadow: 0 0 0 0 rgba(0,230,118,0);
}
.btn-primary:hover {
    background: var(--green2);
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,230,118,.4);
}

.btn-outline {
    background: transparent;
    color: var(--white);
    padding: .85rem 2.2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: .95rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: 1px solid var(--gray3);
    transition: border-color var(--transition), color var(--transition), transform var(--transition);
}
.btn-outline:hover { border-color: var(--green); color: var(--green); transform: translateY(-3px); }

/* Stats bar */
.hero-stats {
    display: flex;
    gap: 3rem;
    justify-content: center;
    margin-top: 4rem;
    animation: fadeUp .8s .6s ease both;
}
.stat { text-align: center; }
.stat-num {
    font-family: var(--font-display);
    font-size: 2.5rem;
    color: var(--green);
    line-height: 1;
}
.stat-label { font-size: .75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-top: .2rem; }

/* ============================================================
   SECTION COMMONS
   ============================================================ */
section { padding: 100px 5vw; }

.section-header { text-align: center; margin-bottom: 60px; }
.section-tag {
    display: inline-block;
    background: rgba(0,230,118,.1);
    color: var(--green);
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    padding: .3rem .9rem;
    border-radius: 50px;
    border: 1px solid rgba(0,230,118,.25);
    margin-bottom: 1rem;
}
.section-title {
    font-family: var(--font-display);
    font-size: clamp(2rem, 5vw, 3.5rem);
    letter-spacing: 2px;
    line-height: 1;
}
.section-title span { color: var(--green); }
.section-sub { color: var(--muted); font-size: .95rem; margin-top: .8rem; }

/* ============================================================
   PRODUCTS GRID
   ============================================================ */
#products { background: var(--gray); }

.filter-bar {
    display: flex;
    gap: .6rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 50px;
}
.filter-btn {
    background: var(--gray2);
    color: var(--muted);
    padding: .45rem 1.2rem;
    border-radius: 50px;
    font-size: .8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 1px solid var(--gray3);
    transition: var(--transition);
}
.filter-btn.active, .filter-btn:hover {
    background: var(--green);
    color: var(--black);
    border-color: var(--green);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

/* Product Card */
.product-card {
    background: var(--black);
    border: 1px solid var(--gray3);
    border-radius: var(--radius);
    overflow: hidden;
    transition: transform var(--transition), border-color var(--transition), box-shadow var(--transition);
    animation: fadeUp .6s ease both;
}
.product-card:hover {
    transform: translateY(-8px);
    border-color: var(--green);
    box-shadow: 0 16px 40px rgba(0,230,118,.15);
}

.product-img-wrap {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: var(--gray2);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Product emoji placeholder (works without real images) */
.product-emoji {
    font-size: 6rem;
    transition: transform var(--transition);
    user-select: none;
}
.product-card:hover .product-emoji { transform: scale(1.15) rotate(-5deg); }

.product-badge {
    position: absolute;
    top: 12px; left: 12px;
    background: var(--green);
    color: var(--black);
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: .25rem .65rem;
    border-radius: 50px;
}

.product-info { padding: 1.2rem; }
.product-category {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--green);
    font-weight: 600;
    margin-bottom: .3rem;
}
.product-name {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: .5rem;
    color: var(--white);
}
.product-desc { font-size: .8rem; color: var(--muted); margin-bottom: 1rem; line-height: 1.5; }

.product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.product-price {
    font-family: var(--font-display);
    font-size: 1.6rem;
    color: var(--green);
    letter-spacing: 1px;
}
.product-price small { font-family: var(--font-body); font-size: .8rem; color: var(--muted); margin-left: 2px; }

.add-to-cart {
    background: var(--green);
    color: var(--black);
    padding: .55rem 1.1rem;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: transform var(--transition), box-shadow var(--transition), background var(--transition);
    display: flex;
    align-items: center;
    gap: .4rem;
}
.add-to-cart:hover {
    background: var(--green2);
    transform: scale(1.05);
    box-shadow: 0 4px 16px rgba(0,230,118,.4);
}
.add-to-cart.added { background: var(--white); color: var(--black); }

/* ============================================================
   CART DRAWER
   ============================================================ */
#cart-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
    z-index: 1100;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    backdrop-filter: blur(4px);
}
#cart-overlay.open { opacity: 1; visibility: visible; }

#cart-drawer {
    position: fixed;
    top: 0; right: 0;
    width: min(420px, 100vw);
    height: 100vh;
    background: var(--gray);
    z-index: 1200;
    transform: translateX(100%);
    transition: transform var(--transition);
    display: flex;
    flex-direction: column;
    border-left: 1px solid var(--gray3);
}
#cart-drawer.open { transform: translateX(0); }

.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.4rem 1.6rem;
    border-bottom: 1px solid var(--gray3);
    flex-shrink: 0;
}
.cart-header h2 { font-family: var(--font-display); font-size: 1.6rem; letter-spacing: 2px; }
#close-cart {
    background: var(--gray2);
    color: var(--white);
    width: 36px; height: 36px;
    border-radius: 50%;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition);
}
#close-cart:hover { background: var(--red); }

#cart-items { flex: 1; overflow-y: auto; padding: 1.2rem 1.6rem; }

.empty-cart {
    text-align: center;
    padding: 3rem 0;
    color: var(--muted);
}
.empty-cart .big-icon { font-size: 4rem; margin-bottom: 1rem; }
.empty-cart p { font-size: .9rem; }

.cart-item {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray2);
    animation: slideIn .3s ease;
}
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

.cart-item-emoji { font-size: 2.4rem; flex-shrink: 0; }
.cart-item-info { flex: 1; }
.cart-item-name { font-size: .9rem; font-weight: 700; }
.cart-item-price { font-size: .8rem; color: var(--green); font-weight: 600; margin-top: .15rem; }

.qty-controls {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: .5rem;
}
.qty-btn {
    background: var(--gray2);
    color: var(--white);
    width: 26px; height: 26px;
    border-radius: 6px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background var(--transition);
    border: 1px solid var(--gray3);
}
.qty-btn:hover { background: var(--green); color: var(--black); }
.qty-val { font-size: .85rem; font-weight: 700; min-width: 20px; text-align: center; }

.remove-item {
    background: transparent;
    color: var(--muted);
    font-size: 1.1rem;
    transition: color var(--transition);
    padding: 4px;
}
.remove-item:hover { color: var(--red); }

.cart-footer {
    padding: 1.4rem 1.6rem;
    border-top: 1px solid var(--gray3);
    flex-shrink: 0;
}
.cart-summary { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.cart-summary .label { font-size: .85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; }
.cart-total-price { font-family: var(--font-display); font-size: 2rem; color: var(--green); }

#checkout-btn {
    width: 100%;
    background: var(--green);
    color: var(--black);
    padding: 1rem;
    border-radius: var(--radius);
    font-weight: 700;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: background var(--transition), transform var(--transition);
}
#checkout-btn:hover { background: var(--green2); transform: translateY(-2px); }
#checkout-btn:disabled { background: var(--gray3); color: var(--muted); transform: none; cursor: not-allowed; }

/* ============================================================
   CHECKOUT SECTION
   ============================================================ */
#checkout { background: var(--black); }

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    max-width: 900px;
    margin: 0 auto;
}

.checkout-form-wrap {
    background: var(--gray);
    border: 1px solid var(--gray3);
    border-radius: var(--radius);
    padding: 2.5rem;
}
.checkout-form-wrap h3 {
    font-family: var(--font-display);
    font-size: 1.8rem;
    letter-spacing: 2px;
    margin-bottom: 1.8rem;
}

.form-group { margin-bottom: 1.2rem; }
.form-group label {
    display: block;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    margin-bottom: .5rem;
}
.form-group input,
.form-group textarea {
    width: 100%;
    background: var(--gray2);
    border: 1px solid var(--gray3);
    color: var(--white);
    padding: .8rem 1rem;
    border-radius: 8px;
    font-size: .9rem;
    font-family: var(--font-body);
    transition: border-color var(--transition), box-shadow var(--transition);
    outline: none;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(0,230,118,.12);
}
.form-group textarea { resize: vertical; min-height: 80px; }

.form-error {
    background: rgba(255,59,59,.12);
    border: 1px solid rgba(255,59,59,.3);
    color: #ff8080;
    padding: .8rem 1rem;
    border-radius: 8px;
    font-size: .85rem;
    margin-bottom: 1rem;
}

.submit-btn {
    width: 100%;
    background: var(--green);
    color: var(--black);
    padding: 1rem;
    border-radius: var(--radius);
    font-weight: 700;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
    margin-top: .5rem;
}
.submit-btn:hover {
    background: var(--green2);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,230,118,.35);
}

/* Cart summary panel */
.order-summary {
    background: var(--gray);
    border: 1px solid var(--gray3);
    border-radius: var(--radius);
    padding: 2rem;
    align-self: start;
    position: sticky;
    top: 90px;
}
.order-summary h3 {
    font-family: var(--font-display);
    font-size: 1.4rem;
    letter-spacing: 2px;
    margin-bottom: 1.4rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray3);
}
#summary-items { margin-bottom: 1.2rem; }
.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: .85rem;
    padding: .4rem 0;
    border-bottom: 1px solid var(--gray2);
}
.summary-row:last-child { border: none; }
.summary-row .item-name { color: var(--muted); }
.summary-row .item-sub { font-size: .75rem; color: var(--gray3); margin-top: 2px; }
.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--gray3);
    margin-top: .5rem;
}
.summary-total span { font-size: .8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); }
.summary-total strong { font-family: var(--font-display); font-size: 2rem; color: var(--green); }
.empty-summary { color: var(--muted); font-size: .85rem; text-align: center; padding: 1.5rem 0; }

/* ============================================================
   ORDER CONFIRMATION
   ============================================================ */
.confirmation-wrap {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    background: var(--gray);
    border: 1px solid rgba(0,230,118,.3);
    border-radius: 20px;
    padding: 3rem 2.5rem;
    box-shadow: 0 0 60px rgba(0,230,118,.1);
}
.confirm-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    animation: bounceIn .6s ease;
}
@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    60% { transform: scale(1.2); }
    100% { transform: scale(1); opacity: 1; }
}
.confirm-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    letter-spacing: 3px;
    color: var(--green);
    margin-bottom: .5rem;
}
.confirm-sub { color: var(--muted); font-size: .95rem; margin-bottom: 2rem; }

.confirm-details {
    background: var(--black);
    border-radius: var(--radius);
    padding: 1.5rem;
    text-align: left;
    margin-bottom: 2rem;
}
.confirm-row {
    display: flex;
    justify-content: space-between;
    padding: .5rem 0;
    border-bottom: 1px solid var(--gray2);
    font-size: .85rem;
}
.confirm-row:last-child { border: none; }
.confirm-row .cl { color: var(--muted); }
.confirm-row .cv { font-weight: 600; text-align: right; }
.confirm-ref { color: var(--green); font-family: var(--font-display); letter-spacing: 2px; }

/* ============================================================
   CONTACT
   ============================================================ */
#contact { background: var(--gray); }

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    max-width: 900px;
    margin: 0 auto;
    align-items: center;
}

.contact-info { }
.contact-info h2 {
    font-family: var(--font-display);
    font-size: 2.5rem;
    letter-spacing: 2px;
    margin-bottom: 1rem;
}
.contact-info p { color: var(--muted); margin-bottom: 2rem; font-size: .9rem; }
.contact-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1.2rem;
}
.contact-item-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: rgba(0,230,118,.1);
    border: 1px solid rgba(0,230,118,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.contact-item-text strong { display: block; font-size: .85rem; margin-bottom: .1rem; }
.contact-item-text span { font-size: .8rem; color: var(--muted); }

/* ============================================================
   FOOTER
   ============================================================ */
footer {
    background: var(--black);
    border-top: 1px solid var(--gray3);
    padding: 2.5rem 5vw;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
footer .logo { font-size: 1.4rem; }
footer p { font-size: .78rem; color: var(--muted); }
.footer-links { display: flex; gap: 1.5rem; }
.footer-links a { font-size: .78rem; color: var(--muted); transition: color var(--transition); }
.footer-links a:hover { color: var(--green); }

/* ============================================================
   ANIMATIONS & UTILITIES
   ============================================================ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

.fade-in { opacity: 0; transform: translateY(30px); transition: opacity .7s ease, transform .7s ease; }
.fade-in.visible { opacity: 1; transform: translateY(0); }

/* Notification toast */
#toast {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--green);
    color: var(--black);
    padding: .8rem 1.8rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: .85rem;
    z-index: 9999;
    transition: transform .4s cubic-bezier(.4,0,.2,1);
    white-space: nowrap;
    box-shadow: 0 8px 30px rgba(0,230,118,.4);
}
#toast.show { transform: translateX(-50%) translateY(0); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 900px) {
    .checkout-grid { grid-template-columns: 1fr; }
    .contact-grid  { grid-template-columns: 1fr; gap: 40px; }
    .order-summary { position: static; }
}

@media (max-width: 700px) {
    .nav-links { display: none; }
    .hamburger { display: flex; }

    .hero-stats { gap: 1.5rem; }
    .stat-num { font-size: 1.8rem; }

    section { padding: 70px 5vw; }
    .products-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
    .product-img-wrap { height: 160px; }
    .product-emoji { font-size: 4rem; }
}

@media (max-width: 460px) {
    .products-grid { grid-template-columns: 1fr; }
    .hero-title { font-size: 3rem; }
    footer { flex-direction: column; text-align: center; }
}
</style>
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav id="navbar">
    <div class="logo">SALAH<span>.</span>SPORT</div>

    <ul class="nav-links">
        <li><a href="#home">Accueil</a></li>
        <li><a href="#products">Produits</a></li>
        <li><a href="#checkout">Commander</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>

    <button id="cart-icon" onclick="openCart()" aria-label="Panier">
        🛒 Panier
        <span id="cart-count">0</span>
    </button>

    <button class="hamburger" id="hamburger" aria-label="Menu" onclick="toggleMobile()">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- Mobile menu -->
<div id="mobile-menu">
    <a href="#home"     onclick="closeMobile()">Accueil</a>
    <a href="#products" onclick="closeMobile()">Produits</a>
    <a href="#checkout" onclick="closeMobile()">Commander</a>
    <a href="#contact"  onclick="closeMobile()">Contact</a>
</div>

<!-- ============================================================
     HERO
     ============================================================ -->
<section id="home">
    <div class="hero-content">
        <div class="hero-badge">⚡ La Performance à Votre Portée</div>

        <h1 class="hero-title">
            SALAH<br /><span>SPORT</span>
        </h1>

        <p class="hero-subtitle">
            Équipements sportifs professionnels pour les champions d'aujourd'hui et de demain. Qualité premium, prix imbattables.
        </p>

        <div class="hero-cta">
            <a href="#products" class="btn-primary">Voir les Produits →</a>
            <a href="#contact"  class="btn-outline">Contactez-nous</a>
        </div>

        <div class="hero-stats">
            <div class="stat">
                <div class="stat-num">500+</div>
                <div class="stat-label">Produits</div>
            </div>
            <div class="stat">
                <div class="stat-num">10K+</div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat">
                <div class="stat-num">5★</div>
                <div class="stat-label">Note</div>
            </div>
            <div class="stat">
                <div class="stat-num">24h</div>
                <div class="stat-label">Livraison</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     PRODUCTS
     ============================================================ -->
<section id="products">
    <div class="section-header fade-in">
        <div class="section-tag">🏆 Notre Catalogue</div>
        <h2 class="section-title">PRODUITS <span>VEDETTES</span></h2>
        <p class="section-sub">Sélectionnés par nos experts pour votre performance</p>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar fade-in">
        <button class="filter-btn active" data-filter="all">Tous</button>
        <button class="filter-btn" data-filter="football">Football</button>
        <button class="filter-btn" data-filter="fitness">Fitness</button>
        <button class="filter-btn" data-filter="chaussures">Chaussures</button>
        <button class="filter-btn" data-filter="boxe">Boxe</button>
        <button class="filter-btn" data-filter="running">Running</button>
    </div>

    <!-- Products will be injected here by JS -->
    <div class="products-grid" id="products-grid"></div>
</section>

<!-- ============================================================
     CHECKOUT
     ============================================================ -->
<section id="checkout">
    <div class="section-header fade-in">
        <div class="section-tag">📦 Commande</div>
        <h2 class="section-title">PASSER <span>COMMANDE</span></h2>
        <p class="section-sub">Livraison rapide partout au Maroc</p>
    </div>

    <?php if ($orderSuccess): ?>
    <!-- ── ORDER SUCCESS ── -->
    <div class="confirmation-wrap fade-in">
        <div class="confirm-icon">🎉</div>
        <h2 class="confirm-title">MERCI !</h2>
        <p class="confirm-sub">Votre commande a été reçue avec succès. Nous vous contacterons bientôt.</p>
        <div class="confirm-details">
            <div class="confirm-row">
                <span class="cl">Référence</span>
                <span class="cv confirm-ref"><?= $orderData['ref'] ?></span>
            </div>
            <div class="confirm-row">
                <span class="cl">Date</span>
                <span class="cv"><?= $orderData['date'] ?></span>
            </div>
            <div class="confirm-row">
                <span class="cl">Nom</span>
                <span class="cv"><?= $orderData['name'] ?></span>
            </div>
            <div class="confirm-row">
                <span class="cl">Téléphone</span>
                <span class="cv"><?= $orderData['phone'] ?></span>
            </div>
            <div class="confirm-row">
                <span class="cl">Adresse</span>
                <span class="cv"><?= $orderData['address'] ?></span>
            </div>
            <div class="confirm-row">
                <span class="cl">Total</span>
                <span class="cv" style="color:var(--green);font-family:var(--font-display);font-size:1.2rem"><?= $orderData['total'] ?> MAD</span>
            </div>
        </div>
        <a href="#products" class="btn-primary" style="display:inline-block;margin-top:.5rem">Continuer les achats →</a>
    </div>

    <?php else: ?>
    <!-- ── CHECKOUT FORM ── -->
    <div class="checkout-grid">
        <div class="checkout-form-wrap fade-in">
            <h3>VOS INFOS</h3>

            <?php if ($orderError): ?>
            <div class="form-error">⚠️ <?= $orderError ?></div>
            <?php endif; ?>

            <form method="POST" action="#checkout" onsubmit="return prepareCheckout()">
                <input type="hidden" name="checkout" value="1" />
                <input type="hidden" name="cart_items" id="form-cart-items" />
                <input type="hidden" name="cart_total" id="form-cart-total" />

                <div class="form-group">
                    <label>Nom complet *</label>
                    <input type="text" name="name" placeholder="Ex: Mohammed Salah"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="votre@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                </div>

                <div class="form-group">
                    <label>Téléphone *</label>
                    <input type="tel" name="phone" placeholder="+212 6XX XXX XXX"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required />
                </div>

                <div class="form-group">
                    <label>Adresse de livraison *</label>
                    <textarea name="address" placeholder="Rue, Ville, Code postal..."
                              required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="submit-btn">✓ Confirmer la commande</button>
            </form>
        </div>

        <!-- Order summary -->
        <div class="order-summary fade-in">
            <h3>RÉSUMÉ</h3>
            <div id="summary-items">
                <p class="empty-summary">Votre panier est vide.<br/>Ajoutez des produits d'abord.</p>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <strong id="summary-total-price">0 MAD</strong>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- ============================================================
     CONTACT
     ============================================================ -->
<section id="contact">
    <div class="contact-grid">
        <div class="contact-info fade-in">
            <div class="section-tag">📍 Où nous trouver</div>
            <h2>PARLONS<br /><span style="color:var(--green)">SPORT</span></h2>
            <p>Notre équipe est disponible 7j/7 pour vous conseiller et vous accompagner dans vos achats.</p>

            <div class="contact-item">
                <div class="contact-item-icon">📍</div>
                <div class="contact-item-text">
                    <strong>Adresse</strong>
                    <span>123 Rue du Sport, Casablanca, Maroc</span>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">📞</div>
                <div class="contact-item-text">
                    <strong>Téléphone</strong>
                    <span>+212 6 00 00 00 00</span>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">✉️</div>
                <div class="contact-item-text">
                    <strong>Email</strong>
                    <span>contact@salahsport.ma</span>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-item-icon">🕐</div>
                <div class="contact-item-text">
                    <strong>Horaires</strong>
                    <span>Lun–Sam : 9h00 – 20h00</span>
                </div>
            </div>
        </div>

        <div class="checkout-form-wrap fade-in">
            <h3>ÉCRIVEZ-NOUS</h3>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" placeholder="Votre nom" />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" placeholder="votre@email.com" />
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea placeholder="Votre message..." style="min-height:120px"></textarea>
            </div>
            <button class="submit-btn" onclick="this.textContent='✓ Message envoyé!';this.style.background='#00c853';setTimeout(()=>{this.textContent='Envoyer le message';this.style.background=''},3000)">
                Envoyer le message
            </button>
        </div>
    </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer>
    <div class="logo">SALAH<span>.</span>SPORT</div>
    <p>© <?= date('Y') ?> Salah Sport – Tous droits réservés</p>
    <div class="footer-links">
        <a href="#home">Accueil</a>
        <a href="#products">Produits</a>
        <a href="#contact">Contact</a>
    </div>
</footer>

<!-- ============================================================
     CART DRAWER
     ============================================================ -->
<div id="cart-overlay" onclick="closeCart()"></div>

<div id="cart-drawer">
    <div class="cart-header">
        <h2>🛒 PANIER</h2>
        <button id="close-cart" onclick="closeCart()">✕</button>
    </div>

    <div id="cart-items">
        <!-- Items injected by JS -->
    </div>

    <div class="cart-footer">
        <div class="cart-summary">
            <span class="label">Total</span>
            <span class="cart-total-price" id="cart-total-display">0 MAD</span>
        </div>
        <button id="checkout-btn" disabled onclick="goCheckout()">
            Commander →
        </button>
    </div>
</div>

<!-- Toast notification -->
<div id="toast"></div>

<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
/* ─────────────────────────────────────────────────────────────
   PRODUCT DATA
   ───────────────────────────────────────────────────────────── */
const PRODUCTS = [
    {
        id: 1, name: 'Ballon de Football Pro',
        category: 'football', emoji: '⚽',
        price: 299, badge: 'Nouveau',
        desc: 'Ballon officiel taille 5, cuir synthétique premium, idéal pour les matchs.'
    },
    {
        id: 2, name: 'Chaussures de Sport Nike',
        category: 'chaussures', emoji: '👟',
        price: 899, badge: 'Populaire',
        desc: 'Légères et respirantes, semelle en caoutchouc antidérapante.'
    },
    {
        id: 3, name: 'Haltères Réglables 20 kg',
        category: 'fitness', emoji: '🏋️',
        price: 550, badge: 'Top Vente',
        desc: 'Set d\'haltères réglables, idéal pour la musculation à domicile.'
    },
    {
        id: 4, name: 'Gants de Boxe Pro',
        category: 'boxe', emoji: '🥊',
        price: 349, badge: '',
        desc: 'Gants de boxe cuir véritable, rembourrage triple densité, 12 oz.'
    },
    {
        id: 5, name: 'Montre GPS Running',
        category: 'running', emoji: '⌚',
        price: 1299, badge: 'Premium',
        desc: 'GPS intégré, fréquence cardiaque, autonomie 20h, résistante à l\'eau.'
    },
    {
        id: 6, name: 'Tapis de Course Électrique',
        category: 'fitness', emoji: '🏃',
        price: 3500, badge: 'Promo',
        desc: 'Vitesse 1–20 km/h, inclinaison réglable, écran LCD, pliable.'
    },
    {
        id: 7, name: 'Maillot Football Domicile',
        category: 'football', emoji: '👕',
        price: 249, badge: '',
        desc: 'Maillot technique respirant, coupe slim, toutes tailles disponibles.'
    },
    {
        id: 8, name: 'Sac de Sport 40L',
        category: 'fitness', emoji: '🎒',
        price: 399, badge: '',
        desc: 'Grand compartiment principal, poche chaussures séparée, bandoulière rembourrée.'
    },
    {
        id: 9, name: 'Chaussures Running Trail',
        category: 'chaussures', emoji: '🏔️',
        price: 749, badge: 'Nouveau',
        desc: 'Semelle Vibram, accroche maximale, imperméable, drop 8mm.'
    },
];

/* ─────────────────────────────────────────────────────────────
   CART STATE
   ───────────────────────────────────────────────────────────── */
let cart = JSON.parse(localStorage.getItem('ss_cart') || '[]');

function saveCart() {
    localStorage.setItem('ss_cart', JSON.stringify(cart));
}

function getTotal() {
    return cart.reduce((sum, item) => sum + item.price * item.qty, 0);
}

/* ─────────────────────────────────────────────────────────────
   RENDER PRODUCTS
   ───────────────────────────────────────────────────────────── */
function renderProducts(filter = 'all') {
    const grid = document.getElementById('products-grid');
    const filtered = filter === 'all'
        ? PRODUCTS
        : PRODUCTS.filter(p => p.category === filter);

    grid.innerHTML = filtered.map((p, i) => `
        <div class="product-card" style="animation-delay:${i * .08}s"
             data-category="${p.category}">
            <div class="product-img-wrap">
                <div class="product-emoji">${p.emoji}</div>
                ${p.badge ? `<span class="product-badge">${p.badge}</span>` : ''}
            </div>
            <div class="product-info">
                <div class="product-category">${p.category}</div>
                <div class="product-name">${p.name}</div>
                <div class="product-desc">${p.desc}</div>
                <div class="product-footer">
                    <div class="product-price">${p.price}<small>MAD</small></div>
                    <button class="add-to-cart" onclick="addToCart(${p.id})" id="btn-${p.id}">
                        + Ajouter
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

/* ─────────────────────────────────────────────────────────────
   CART FUNCTIONS
   ───────────────────────────────────────────────────────────── */
function addToCart(productId) {
    const product = PRODUCTS.find(p => p.id === productId);
    if (!product) return;

    const existing = cart.find(i => i.id === productId);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ ...product, qty: 1 });
    }
    saveCart();
    updateCartUI();
    showToast(`✓ ${product.name} ajouté !`);

    // Button feedback
    const btn = document.getElementById('btn-' + productId);
    if (btn) {
        btn.textContent = '✓ Ajouté';
        btn.classList.add('added');
        setTimeout(() => {
            btn.textContent = '+ Ajouter';
            btn.classList.remove('added');
        }, 1500);
    }

    // Animate cart count
    const countEl = document.getElementById('cart-count');
    countEl.style.transform = 'scale(1.5)';
    setTimeout(() => { countEl.style.transform = 'scale(1)'; }, 300);
}

function removeFromCart(productId) {
    cart = cart.filter(i => i.id !== productId);
    saveCart();
    updateCartUI();
}

function changeQty(productId, delta) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) {
        removeFromCart(productId);
        return;
    }
    saveCart();
    updateCartUI();
}

function updateCartUI() {
    // Count badge
    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    document.getElementById('cart-count').textContent = totalQty;

    // Drawer items
    const itemsEl = document.getElementById('cart-items');
    const total   = getTotal();

    if (cart.length === 0) {
        itemsEl.innerHTML = `
            <div class="empty-cart">
                <div class="big-icon">🛒</div>
                <p>Votre panier est vide.<br/>Ajoutez des produits !</p>
            </div>`;
    } else {
        itemsEl.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-emoji">${item.emoji}</div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${(item.price * item.qty).toLocaleString()} MAD</div>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" onclick="changeQty(${item.id}, +1)">+</button>
                    </div>
                </div>
                <button class="remove-item" onclick="removeFromCart(${item.id})" title="Supprimer">🗑</button>
            </div>
        `).join('');
    }

    // Total display
    document.getElementById('cart-total-display').textContent = total.toLocaleString() + ' MAD';

    // Checkout button
    document.getElementById('checkout-btn').disabled = cart.length === 0;

    // Sync checkout summary
    syncSummary();
}

function syncSummary() {
    const summaryEl = document.getElementById('summary-items');
    const summaryTotal = document.getElementById('summary-total-price');
    if (!summaryEl) return;

    const total = getTotal();
    summaryTotal.textContent = total.toLocaleString() + ' MAD';

    if (cart.length === 0) {
        summaryEl.innerHTML = '<p class="empty-summary">Votre panier est vide.<br/>Ajoutez des produits d\'abord.</p>';
    } else {
        summaryEl.innerHTML = cart.map(item => `
            <div class="summary-row">
                <span class="item-name">${item.emoji} ${item.name}
                    <div class="item-sub">Qté: ${item.qty}</div>
                </span>
                <span>${(item.price * item.qty).toLocaleString()} MAD</span>
            </div>
        `).join('');
    }
}

/* ─────────────────────────────────────────────────────────────
   CART DRAWER OPEN / CLOSE
   ───────────────────────────────────────────────────────────── */
function openCart() {
    document.getElementById('cart-drawer').classList.add('open');
    document.getElementById('cart-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeCart() {
    document.getElementById('cart-drawer').classList.remove('open');
    document.getElementById('cart-overlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ─────────────────────────────────────────────────────────────
   CHECKOUT HELPERS
   ───────────────────────────────────────────────────────────── */
function goCheckout() {
    closeCart();
    document.getElementById('checkout').scrollIntoView({ behavior: 'smooth' });
}

function prepareCheckout() {
    if (cart.length === 0) {
        showToast('⚠️ Panier vide !');
        return false;
    }
    const items = cart.map(i => `${i.name} x${i.qty}`).join(', ');
    document.getElementById('form-cart-items').value = items;
    document.getElementById('form-cart-total').value = getTotal();
    return true;
}

/* ─────────────────────────────────────────────────────────────
   TOAST
   ───────────────────────────────────────────────────────────── */
let toastTimer;
function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}

/* ─────────────────────────────────────────────────────────────
   PRODUCT FILTER
   ───────────────────────────────────────────────────────────── */
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderProducts(btn.dataset.filter);
    });
});

/* ─────────────────────────────────────────────────────────────
   NAVBAR SCROLL
   ───────────────────────────────────────────────────────────── */
window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    nav.style.background = window.scrollY > 60
        ? 'rgba(10,10,10,.97)'
        : 'rgba(10,10,10,.85)';
});

/* ─────────────────────────────────────────────────────────────
   MOBILE MENU
   ───────────────────────────────────────────────────────────── */
function toggleMobile() {
    document.getElementById('mobile-menu').classList.toggle('open');
}
function closeMobile() {
    document.getElementById('mobile-menu').classList.remove('open');
}

/* ─────────────────────────────────────────────────────────────
   INTERSECTION OBSERVER – fade-in on scroll
   ───────────────────────────────────────────────────────────── */
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });

document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

/* ─────────────────────────────────────────────────────────────
   INIT
   ───────────────────────────────────────────────────────────── */
renderProducts();
updateCartUI();

// Clear cart after successful order
<?php if ($orderSuccess): ?>
localStorage.removeItem('ss_cart');
cart = [];
<?php endif; ?>
</script>
</body>
</html>
