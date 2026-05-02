<?php
session_start();
require '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];
if ($id <= 0) { header('Location: index.php'); exit(); }

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
            --primary: #050b14; /* Noir Cupra Petrol */
            --accent: #c2a37d;  /* Cuivre Cupra */
            --glass: rgba(13, 18, 26, 0.8);
            --bg: #02060c;
            --radius: 0px; /* Look tranchant Cupra */
            --shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 40px 20px;
            color: #ffffff;
        }

        .container { max-width: 1200px; margin: 0 auto; opacity: 0; transform: translateY(20px); transition: 1s ease; }
        .container.loaded { opacity: 1; transform: translateY(0); }

        /* --- Header / Retour --- */
        .back-nav { margin-bottom: 30px; }
        .back-nav a { 
            text-decoration: none; 
            color: var(--accent); 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.8rem;
        }
        .back-nav a:hover { color: white; transform: translateX(-5px); }

        /* --- Layout Principal --- */
        .main-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        /* --- Section Image --- */
        .gallery-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border-radius: var(--radius);
            padding: 10px;
            border: 1px solid rgba(194, 163, 125, 0.2);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .main-img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: var(--radius);
            transition: transform 1.5s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .gallery-card:hover .main-img { transform: scale(1.05); }

        /* --- Info Card --- */
        .info-card {
            background: #0d121a;
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            position: sticky;
            top: 40px;
            border-left: 4px solid var(--accent);
        }

        .brand-badge {
            background: rgba(194, 163, 125, 0.1);
            color: var(--accent);
            padding: 8px 20px;
            border-radius: 0;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            border: 1px solid var(--accent);
        }

        h1 { 
            font-size: 3rem; 
            margin: 25px 0; 
            letter-spacing: -2px; 
            text-transform: uppercase;
            font-weight: 800;
            line-height: 1;
        }

        .price { 
            font-size: 3.5rem; 
            font-weight: 200; 
            color: var(--accent); 
            margin-bottom: 30px; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 20px;
        }

        /* --- Spécifications Grid --- */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 30px 0;
        }

        .spec-box {
            background: rgba(255,255,255,0.03);
            padding: 20px;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }
        .spec-box:hover { background: rgba(194, 163, 125, 0.08); }

        .spec-box i { color: var(--accent); font-size: 1.4rem; }
        .spec-label { display: block; font-size: 0.6rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .spec-val { font-weight: 700; font-size: 1rem; color: #fff; }

        /* --- Contact Seller Section --- */
        .seller-box {
            margin-top: 30px;
            padding: 25px;
            background: var(--accent);
            color: #000;
            border-radius: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-call {
            background: #000;
            color: #fff;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 0;
            font-weight: 800;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            letter-spacing: 1px;
        }

        .btn-call:hover { transform: scale(1.05); background: #fff; color: #000; }

        /* --- Description --- */
        .description-text {
            margin-top: 30px;
            color: #ccc;
            line-height: 1.8;
            font-size: 1.1rem;
            font-weight: 300;
        }

        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .info-card { position: relative; top: 0; }
            h1 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<div class="container" id="mainContainer">
    <div class="back-nav">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à la marketplace</a>
    </div>

    <div class="main-grid">
        <div class="gallery-card">
            <img src="https://source.unsplash.com/1200x800/?car,<?= $auto['nom_marque'] ?>" alt="Voiture" class="main-img">
            
            <div style="padding: 30px;">
                <h2 style="font-weight: 800; border-bottom: 2px solid var(--accent); padding-bottom: 15px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px;">Détails & Équipements</h2>
                <div class="description-text">
                    <?= nl2br(htmlspecialchars($auto['description'])) ?>
                </div>
            </div>
        </div>

        <div class="info-card">
            <span class="brand-badge"><?= $auto['nom_marque'] ?> • <?= $auto['nom_modele'] ?></span>
            <h1><?= htmlspecialchars($auto['titre']) ?></h1>
            <div class="price"><?= number_format($auto['prix'], 0, ',', ' ') ?> <small style="font-size: 1.2rem; font-weight: 800;">DH</small></div>

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
                    <span style="display: block; font-size: 0.7rem; opacity: 0.8; font-weight: 800; text-transform: uppercase;">VENDEUR</span>
                    <span style="font-weight: 700; font-size: 1.1rem;"><?= $auto['nom'] ?> • <?= $auto['ville'] ?></span>
                </div>
                <a href="tel:<?= $auto['telephone'] ?>" class="btn-call">
                    <i class="fas fa-phone"></i> Appeler
                </a>
            </div>
            
            <p style="text-align: center; font-size: 0.8rem; color: #555; margin-top: 30px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-shield-alt" style="color: var(--accent);"></i> Transaction sécurisée par Auto-Market Premium
            </p>
        </div>
    </div>
</div>

<script>
    // Petit script pour l'animation d'entrée Cupra
    window.onload = () => {
        document.getElementById('mainContainer').classList.add('loaded');
    };
</script>

</body>
</html>