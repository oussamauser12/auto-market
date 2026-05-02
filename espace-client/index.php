<?php
session_start();
require '../config/db.php';
 
$user_id = $_SESSION['user_id'] ?? 0;
 
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$ville_filter = isset($_GET['ville']) ? trim($_GET['ville']) : '';
$budget_filter = isset($_GET['budget']) ? (int)$_GET['budget'] : 0;
 
$query = "SELECT a.*, v.*, m.nom_modele, ma.nom_marque, u.ville as ville_vendeur, 
                  MIN(p.chemin_url) as chemin_url,
                  (SELECT COUNT(*) FROM favoris f WHERE f.id_annonce = a.id_annonce AND f.id_user = $user_id) as is_fav
          FROM annonces a 
          JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
          JOIN modeles m ON v.id_modele = m.id_modele 
          JOIN marques ma ON m.id_marque = ma.id_marque
          JOIN utilisateurs u ON a.id_user = u.id_user
          LEFT JOIN photos p ON a.id_annonce = p.id_annonce
          WHERE a.statut = 'valide'";
 
$params = [];
 
if (!empty($search)) { 
    $query .= " AND (m.nom_modele LIKE :search OR ma.nom_marque LIKE :search OR a.titre LIKE :search)"; 
    $params['search'] = "%$search%";
}
if (!empty($ville_filter)) {
    $query .= " AND u.ville = :ville";
    $params['ville'] = $ville_filter;
}
if ($budget_filter > 0) {
    $query .= " AND a.prix <= :budget";
    $params['budget'] = $budget_filter;
}
 
$query .= " GROUP BY a.id_annonce ORDER BY a.date_publication DESC";
 
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$annonces = $stmt->fetchAll();
 
$villes_maroc = ["Casablanca", "Rabat", "Marrakech", "Fès", "Tanger", "Agadir", "Meknès", "Oujda", "Kénitra", "Tétouan", "Safi", "Mohammédia", "Khouribga", "Béni Mellal", "El Jadida", "Taza", "Nador", "Settat", "Larache", "Ksar El Kebir"];
sort($villes_maroc);
?>
 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ══════════════════════════════════════
           ROOT & RESET
        ══════════════════════════════════════ */
        :root {
            --bg:          #05050d;
            --gold:        #c9a96e;
            --gold-dim:    rgba(201,169,110,0.15);
            --gold-glow:   rgba(201,169,110,0.35);
            --glass:       rgba(255,255,255,0.025);
            --glass-hover: rgba(255,255,255,0.055);
            --border:      rgba(255,255,255,0.07);
            --border-gold: rgba(201,169,110,0.45);
            --muted:       #5a6a82;
            --mid:         #94a3b8;
            --ease:        cubic-bezier(0.25,1,0.3,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: #fff;
            overflow-x: hidden;
        }
 
        /* ══════════════════════════════════════
           ANIMATED BACKGROUND LAYERS
        ══════════════════════════════════════ */
 
        /* 1. Fixed canvas holding all BG effects */
        #bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
 
        /* 2. Subtle grid */
        #bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.028) 1px, transparent 1px);
            background-size: 70px 70px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
        }
 
        /* 3. Large aurora blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0;
            animation: blobFloat ease-in-out infinite alternate;
        }
        .blob-1 {
            width: 800px; height: 800px;
            top: -200px; left: -200px;
            background: radial-gradient(circle, rgba(201,169,110,0.22), transparent 65%);
            animation-duration: 20s;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 700px; height: 700px;
            bottom: -100px; right: -150px;
            background: radial-gradient(circle, rgba(99,102,241,0.2), transparent 65%);
            animation-duration: 25s;
            animation-delay: -8s;
        }
        .blob-3 {
            width: 500px; height: 500px;
            top: 40%; left: 40%;
            background: radial-gradient(circle, rgba(201,169,110,0.1), transparent 65%);
            animation-duration: 18s;
            animation-delay: -14s;
        }
        @keyframes blobFloat {
            0%   { opacity: 1; transform: translate(0,0) scale(1); }
            50%  { opacity: 0.85; }
            100% { opacity: 1; transform: translate(60px, 50px) scale(1.15); }
        }
 
        /* 4. Horizontal scan line */
        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold-glow), transparent);
            top: 0;
            animation: scanMove 12s linear infinite;
            opacity: 0.6;
        }
        @keyframes scanMove {
            from { top: -2px; }
            to   { top: 100%; }
        }
 
        /* 5. Floating particles (JS-generated) */
        .pt {
            position: absolute;
            width: 2px; height: 2px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0;
            animation: ptRise linear infinite;
        }
        @keyframes ptRise {
            0%   { transform: translateY(0)   scale(0); opacity: 0; }
            15%  { opacity: 0.7; }
            85%  { opacity: 0.4; }
            100% { transform: translateY(-110vh) scale(1.5); opacity: 0; }
        }
 
        /* ══════════════════════════════════════
           NAV
        ══════════════════════════════════════ */
        nav {
            position: fixed; top: 0; width: 100%; height: 70px;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 5%; z-index: 1000;
            background: rgba(5,5,13,0.75);
            backdrop-filter: blur(20px) saturate(150%);
            border-bottom: 1px solid var(--border);
            transition: height 0.4s var(--ease), background 0.4s;
        }
        nav.scrolled { height: 58px; background: rgba(5,5,13,0.95); }
 
        nav .logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem; letter-spacing: 3px;
            color: #fff; text-decoration: none;
        }
        nav .logo span { color: var(--gold); }
 
        nav .links { display: flex; align-items: center; gap: 28px; }
        nav .links a {
            color: var(--mid); text-decoration: none;
            font-size: 0.82rem; text-transform: uppercase;
            letter-spacing: 1.5px; font-weight: 500;
            transition: color 0.3s;
        }
        nav .links a:hover { color: #fff; }
 
        /* ══════════════════════════════════════
           HERO
        ══════════════════════════════════════ */
        .hero {
            height: 90vh; width: 100%; position: relative;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            background:
                linear-gradient(to bottom, rgba(5,5,13,0.45) 0%, var(--bg) 100%),
                url('https://media.gqmagazine.fr/photos/5bf402f6923dee70dbcfae9e/16:9/w_2560%2Cc_limit/526140.jpg')
                center/cover no-repeat;
            text-align: center;
            overflow: hidden;
        }
 
        /* hero gold line accent */
        .hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%; transform: translateX(-50%);
            width: 1px; height: 80px;
            background: linear-gradient(to bottom, var(--gold), transparent);
        }
 
        .hero h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(4rem, 10vw, 8rem);
            letter-spacing: 4px;
            line-height: 1;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(40px);
            animation: heroIn 1s var(--ease) 0.3s forwards;
        }
        .hero p {
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 6px;
            font-weight: 500;
            margin-top: 18px;
            font-size: 0.85rem;
            opacity: 0;
            transform: translateY(20px);
            animation: heroIn 1s var(--ease) 0.6s forwards;
        }
        @keyframes heroIn {
            to { opacity: 1; transform: translateY(0); }
        }
 
        /* ══════════════════════════════════════
           SEARCH BAR
        ══════════════════════════════════════ */
        .search-container {
            max-width: 1000px; margin: -55px auto 70px;
            background: rgba(10,10,20,0.85);
            backdrop-filter: blur(30px);
            padding: 22px 28px;
            border-radius: 4px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 0.55fr;
            gap: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px var(--gold-dim);
            position: relative; z-index: 2;
            opacity: 0; transform: translateY(25px);
            animation: heroIn 0.9s var(--ease) 0.9s forwards;
        }
 
        .search-container input,
        .search-container select {
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--border);
            padding: 12px 4px;
            color: #fff;
            outline: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        .search-container input::placeholder { color: var(--muted); }
        .search-container input:focus,
        .search-container select:focus { border-color: var(--gold); }
 
        .search-container button {
            background: linear-gradient(135deg, var(--gold), #e8c07a);
            border: none; cursor: pointer;
            color: #000; font-weight: 700;
            border-radius: 3px;
            font-size: 1rem;
            transition: transform 0.25s var(--ease), box-shadow 0.25s;
        }
        .search-container button:hover {
            transform: scale(1.06);
            box-shadow: 0 8px 25px var(--gold-glow);
        }
 
        /* ══════════════════════════════════════
           CONTAINER & GRID
        ══════════════════════════════════════ */
        .container {
            padding: 0 5% 60px;
            max-width: 1400px;
            margin: 0 auto;
            position: relative; z-index: 1;
        }
 
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 36px;
        }
 
        /* ══════════════════════════════════════
           CAR CARD
        ══════════════════════════════════════ */
        .car-card {
            background: var(--glass);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid var(--border);
            position: relative;
            transition: transform 0.6s var(--ease),
                        border-color 0.4s,
                        box-shadow 0.6s var(--ease),
                        background 0.4s;
            will-change: transform;
            /* entry animation applied via JS */
            opacity: 0;
            transform: translateY(50px) scale(0.97);
        }
        .car-card.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .car-card:hover {
            border-color: var(--border-gold);
            background: var(--glass-hover);
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5), 0 0 30px var(--gold-dim);
        }
 
        /* top accent line on hover */
        .car-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            opacity: 0;
            transition: opacity 0.4s;
        }
        .car-card:hover::before { opacity: 1; }
 
        /* image */
        .image-wrapper {
            height: 240px;
            overflow: hidden;
            position: relative;
            background: #0a0a15;
        }
        .image-wrapper img {
            width: 100%; height: 100%;
            object-fit: cover;
            filter: grayscale(60%) brightness(0.85);
            transition: filter 0.7s var(--ease), transform 0.7s var(--ease);
        }
        .car-card:hover .image-wrapper img {
            filter: grayscale(0%) brightness(1);
            transform: scale(1.07);
        }
 
        /* gradient overlay on image */
        .image-wrapper::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                transparent 40%,
                rgba(5,5,13,0.8) 100%
            );
            pointer-events: none;
        }
 
        .price-badge {
            position: absolute;
            bottom: 18px; left: 18px;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.5rem;
            letter-spacing: 1px;
            background: rgba(5,5,13,0.75);
            backdrop-filter: blur(10px);
            padding: 4px 14px;
            border-left: 3px solid var(--gold);
            color: #fff;
            z-index: 1;
        }
 
        /* fav heart */
        .image-wrapper > a[href*="favoris"] {
            position: absolute;
            top: 14px; right: 14px;
            width: 36px; height: 36px;
            background: rgba(5,5,13,0.7);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: var(--gold) !important;
            text-decoration: none;
            border: 1px solid var(--border);
            z-index: 2;
            transition: transform 0.3s var(--ease), background 0.3s;
        }
        .image-wrapper > a[href*="favoris"]:hover {
            transform: scale(1.2);
            background: rgba(201,169,110,0.25);
        }
 
        /* content block */
        .content { padding: 26px 28px 28px; }
 
        .content div[style*="font-size: 0.75rem"] {
            font-size: 0.75rem !important;
            color: var(--gold) !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            margin-bottom: 6px !important;
        }
 
        .content h3 {
            font-size: 1.15rem !important;
            color: #fff !important;
            font-weight: 600 !important;
            margin: 4px 0 18px !important;
            line-height: 1.35 !important;
        }
 
        .specs {
            display: flex;
            gap: 22px;
            color: var(--mid);
            font-size: 0.82rem;
            border-top: 1px solid var(--border);
            padding-top: 18px;
            font-weight: 400;
        }
        .specs span { display: flex; align-items: center; gap: 6px; }
        .specs span i { color: var(--gold); opacity: 0.85; font-size: 0.85rem; }
 
        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 22px;
            color: var(--mid) !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            transition: color 0.3s, gap 0.3s;
        }
        .btn-view::after {
            content: '→';
            transition: transform 0.3s var(--ease);
        }
        .btn-view:hover {
            color: var(--gold) !important;
            gap: 12px;
        }
        .btn-view:hover::after { transform: translateX(4px); }
 
        /* ══════════════════════════════════════
           SECTION LABEL
        ══════════════════════════════════════ */
        .section-label {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
        }
        .section-label span {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2rem;
            letter-spacing: 2px;
            color: #fff;
        }
        .section-label em {
            color: var(--gold);
            font-style: normal;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--border-gold), transparent);
        }
 
        /* ══════════════════════════════════════
           EMPTY STATE
        ══════════════════════════════════════ */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 40px;
            border: 1px dashed var(--border);
            border-radius: 8px;
            color: var(--muted);
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 16px; color: var(--border-gold); }
        .empty-state p { font-size: 1rem; margin-top: 8px; }
 
        /* ══════════════════════════════════════
           FOOTER
        ══════════════════════════════════════ */
        footer {
            padding: 50px 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-top: 1px solid var(--border);
            position: relative; z-index: 1;
        }
 
        /* ══════════════════════════════════════
           MISC
        ══════════════════════════════════════ */
        option { background: #0d0d1a; color: #fff; }
 
        /* scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: #2a2a3a; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold); }
 
        /* selection */
        ::selection { background: var(--gold-glow); color: #fff; }
    </style>
</head>
<body>
 
<!-- ░░░ ANIMATED BACKGROUND ░░░ -->
<div id="bg">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="scan-line"></div>
    <!-- particles injected by JS -->
</div>
 
<!-- ░░░ NAV ░░░ -->
<nav>
    <a href="index.php" class="logo">AUTO.<span>MARKET</span></a>
    <div class="links">
        <a href="index.php">Explorer</a>
        <a href="favoris.php">Mes Favoris</a>
        <a href="profil.php">Mon Garage</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="../logout.php" style="color: #f43f5e;"><i class="fa-solid fa-power-off"></i></a>
        <?php else: ?>
            <a href="../login.php" style="background: var(--gold); padding: 8px 22px; border-radius: 3px; color: #000; font-weight: 700; letter-spacing: 1px;">Connexion</a>
        <?php endif; ?>
    </div>
</nav>
 
<!-- ░░░ HERO ░░░ -->
<section class="hero">
    <h1>Conduisez l'Excellence.</h1>
    <p>La marketplace automobile du khrbga.</p>
</section>
 
<!-- ░░░ CONTENT ░░░ -->
<div class="container">
 
    <!-- Search -->
    <form action="index.php" method="GET" class="search-container">
        <input type="text" name="search" placeholder="Marque ou modèle..." value="<?= htmlspecialchars($search) ?>">
        
        <select name="ville">
            <option value="">Toutes villes</option>
            <?php foreach($villes_maroc as $v): ?>
                <option value="<?= $v ?>" <?= ($ville_filter == $v) ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="budget">
            <option value="">Budget max</option>
            <option value="50000"  <?= ($budget_filter == 50000)  ? 'selected' : '' ?>>50 000 DH</option>
            <option value="100000" <?= ($budget_filter == 100000) ? 'selected' : '' ?>>100 000 DH</option>
            <option value="150000" <?= ($budget_filter == 150000) ? 'selected' : '' ?>>150 000 DH</option>
            <option value="200000" <?= ($budget_filter == 200000) ? 'selected' : '' ?>>200 000 DH</option>
            <option value="300000" <?= ($budget_filter == 300000) ? 'selected' : '' ?>>300 000 DH</option>
            <option value="500000" <?= ($budget_filter == 500000) ? 'selected' : '' ?>>500 000 DH</option>
        </select>
        
        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
 
    <!-- Section label -->
    <div class="section-label">
        <span>Annonces <em>Disponibles</em></span>
    </div>
 
    <!-- Grid -->
    <div class="grid">
        <?php if (count($annonces) > 0): ?>
            <?php foreach ($annonces as $auto): ?>
            <div class="car-card">
                <div class="image-wrapper">
                    <?php
                        // Use real photo if available, else fallback to a Unsplash car query
                        $img_src = !empty($auto['chemin_url'])
                            ? '../' . htmlspecialchars($auto['chemin_url'])
                            : 'https://source.unsplash.com/800x500/?car,' . urlencode($auto['nom_marque'] . ' ' . $auto['nom_modele']);
                    ?>
                    <img 
                        src="<?= $img_src ?>" 
                        alt="<?= htmlspecialchars($auto['nom_marque'] . ' ' . $auto['nom_modele']) ?>"
                        loading="lazy"
                        onerror="this.src='https://source.unsplash.com/800x500/?luxury,car'"
                    >
                    <div class="price-badge"><?= number_format($auto['prix'], 0, ',', ' ') ?> DH</div>
                    
                    <a href="ajouter_favoris.php?id=<?= $auto['id_annonce'] ?>" 
                       style="position: absolute; top: 15px; right: 15px; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ef4444; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="<?= ($auto['is_fav'] > 0) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                    </a>
                </div>
 
                <div class="content">
                    <div style="font-size: 0.75rem; color: var(--accent-gold); font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($auto['nom_marque']) ?> • <?= htmlspecialchars($auto['ville_vendeur']) ?></div>
                    <h3 style="margin: 5px 0; font-size: 1.2rem; color: #fff;"><?= htmlspecialchars($auto['titre']) ?></h3>
                    
                    <div class="specs">
                        <span><i class="fa-solid fa-calendar"></i> <?= $auto['annee'] ?></span>
                        <span><i class="fa-solid fa-gauge-high"></i> <?= number_format($auto['kilometrage'],0,',',' ') ?> km</span>
                        <span><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($auto['carburant']) ?></span>
                    </div>
 
                    <div style="display: flex; gap: 10px;">
                        <a href="annonce.php?id=<?= $auto['id_annonce'] ?>" class="btn-view" style="flex: 1;">Détails</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-car-burst"></i>
                <p>Aucun véhicule ne correspond à vos critères.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
 
<!-- ░░░ FOOTER ░░░ -->
<footer>
    <p>© 2026 Auto-Market | ENSA Khouribga Edition</p>
</footer>
 
<!-- ░░░ JS ░░░ -->
<script>
/* ── Scroll nav ── */
const nav = document.querySelector('nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 80);
}, { passive: true });
 
/* ── Particles ── */
(function spawnParticles() {
    const bg = document.getElementById('bg');
    const count = 40;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'pt';
        const size = Math.random() * 3 + 1;
        p.style.cssText = `
            left: ${Math.random() * 100}%;
            bottom: ${Math.random() * -30}%;
            width: ${size}px;
            height: ${size}px;
            animation-duration: ${10 + Math.random() * 20}s;
            animation-delay: ${Math.random() * 18}s;
        `;
        bg.appendChild(p);
    }
})();
 
/* ── Card entrance (IntersectionObserver) ── */
(function animateCards() {
    const cards = document.querySelectorAll('.car-card');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const card = entry.target;
                const idx  = [...cards].indexOf(card);
                setTimeout(() => {
                    card.style.transition = 'opacity 0.7s cubic-bezier(0.25,1,0.3,1), transform 0.7s cubic-bezier(0.25,1,0.3,1), border-color 0.4s, box-shadow 0.6s cubic-bezier(0.25,1,0.3,1), background 0.4s';
                    card.classList.add('visible');
                }, (idx % 3) * 110); // stagger per row
                io.unobserve(card);
            }
        });
    }, { threshold: 0.1 });
    cards.forEach(c => io.observe(c));
})();
 
/* ── Tilt effect on cards ── */
document.querySelectorAll('.car-card').forEach(card => {
    card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width  - 0.5;
        const y = (e.clientY - rect.top)  / rect.height - 0.5;
        card.style.transform = `translateY(-10px) rotateY(${x * 6}deg) rotateX(${-y * 6}deg) scale(1.01)`;
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});
 
/* ── Cursor glow ── */
(function cursorGlow() {
    const glow = document.createElement('div');
    glow.style.cssText = `
        position: fixed;
        width: 300px; height: 300px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        background: radial-gradient(circle, rgba(201,169,110,0.07) 0%, transparent 70%);
        transform: translate(-50%, -50%);
        transition: transform 0.15s linear;
        will-change: left, top;
    `;
    document.body.appendChild(glow);
    window.addEventListener('mousemove', e => {
        glow.style.left = e.clientX + 'px';
        glow.style.top  = e.clientY + 'px';
    }, { passive: true });
})();
</script>
 
</body>
</html>