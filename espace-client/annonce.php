<?php
session_start();
require '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Récupération des détails avec jointures pour un affichage complet
$stmt = $pdo->prepare("
    SELECT a.*, v.*, m.nom_modele, ma.nom_marque, u.nom, u.prenom, u.telephone, u.ville 
    FROM annonces a 
    JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
    JOIN modeles m ON v.id_modele = m.id_modele 
    JOIN marques ma ON m.id_marque = ma.id_marque
    JOIN utilisateurs u ON a.id_user = u.id_user
    WHERE a.id_annonce = ?
");
$stmt->execute([$id]);
$auto = $stmt->fetch();

if (!$auto) {
    echo "Annonce introuvable.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($auto['titre']) ?> | Auto-Market</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --glass: rgba(255, 255, 255, 0.7);
            --bg: #f1f5f9;
            --radius: 24px;
            --shadow: 0 20px 50px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 40px 20px;
            color: var(--primary);
        }

        .container { max-width: 1100px; margin: 0 auto; animation: fadeIn 0.8s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Header / Retour --- */
        .back-nav { margin-bottom: 30px; }
        .back-nav a { text-decoration: none; color: var(--accent); font-weight: 700; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-nav a:hover { transform: translateX(-5px); }

        /* --- Layout Principal --- */
        .main-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        /* --- Section Image Glassmorphism --- */
        .gallery-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            padding: 15px;
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: var(--shadow);
        }

        .main-img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: 18px;
        }

        /* --- Info Card --- */
        .info-card {
            background: white;
            padding: 35px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            position: sticky;
            top: 40px;
        }

        .brand-badge {
            background: #eff6ff;
            color: var(--accent);
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 { font-size: 2.2rem; margin: 15px 0; letter-spacing: -1px; }

        .price { font-size: 2.5rem; font-weight: 800; color: var(--accent); margin-bottom: 25px; }

        /* --- Spécifications Grid --- */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 25px 0;
        }

        .spec-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #f1f5f9;
        }

        .spec-box i { color: var(--accent); font-size: 1.2rem; }
        .spec-label { display: block; font-size: 0.75rem; color: #64748b; font-weight: 600; }
        .spec-val { font-weight: 700; font-size: 0.95rem; }

        /* --- Contact Seller Section --- */
        .seller-box {
            margin-top: 30px;
            padding: 20px;
            background: var(--primary);
            color: white;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-call {
            background: var(--accent);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-call:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4); }

        /* --- Description --- */
        .description-text {
            margin-top: 30px;
            color: #475569;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .info-card { position: relative; top: 0; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-nav">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à la marketplace</a>
    </div>

    <div class="main-grid">
        <div class="gallery-card">
            <img src="https://source.unsplash.com/1200x800/?car,<?= $auto['nom_marque'] ?>" alt="Voiture" class="main-img">
            
            <div style="padding: 20px;">
                <h2 style="font-weight: 800; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 15px;">Détails & Équipements</h2>
                <div class="description-text">
                    <?= nl2br(htmlspecialchars($auto['description'])) ?>
                </div>
            </div>
        </div>

        <div class="info-card">
            <span class="brand-badge"><?= $auto['nom_marque'] ?> • <?= $auto['nom_modele'] ?></span>
            <h1><?= htmlspecialchars($auto['titre']) ?></h1>
            <div class="price"><?= number_format($auto['prix'], 0, ',', ' ') ?> <small style="font-size: 1rem;">DH</small></div>

            <div class="specs-grid">
                <div class="spec-box">
                    <i class="fas fa-calendar"></i>
                    <div><span class="spec-label">ANNÉE</span><span class="spec-val"><?= $auto['annee'] ?></span></div>
                </div>
                <div class="spec-box">
                    <i class="fas fa-tachometer-alt"></i>
                    <div><span class="spec-label">KILOMÉTRAGE</span><span class="spec-val"><?= number_format($auto['kilometrage'], 0, ',', ' ') ?> km</span></div>
                </div>
                <div class="spec-box">
                    <i class="fas fa-gas-pump"></i>
                    <div><span class="spec-label">CARBURANT</span><span class="spec-val"><?= $auto['carburant'] ?></span></div>
                </div>
                <div class="spec-box">
                    <i class="fas fa-cog"></i>
                    <div><span class="spec-label">TRANSMISSION</span><span class="spec-val"><?= $auto['boite_vitesse'] ?></span></div>
                </div>
            </div>

            <div class="seller-box">
                <div>
                    <span style="display: block; font-size: 0.7rem; opacity: 0.7;">VENDEUR</span>
                    <span style="font-weight: 700;"><?= $auto['nom'] ?> • <?= $auto['ville'] ?></span>
                </div>
                <a href="tel:<?= $auto['telephone'] ?>" class="btn-call">
                    <i class="fas fa-phone"></i> Appeler
                </a>
            </div>
            
            <p style="text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 20px;">
                <i class="fas fa-shield-alt"></i> Transaction sécurisée par Auto-Market
            </p>
        </div>
    </div>
</div>

</body>
</html>