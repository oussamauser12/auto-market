<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// REQUÊTE CONSERVÉE
$query = "SELECT a.*, v.*, m.nom_modele, ma.nom_marque, u.ville as ville_vendeur, 
                  MIN(p.chemin_url) as chemin_url, f.date_ajout
          FROM favoris f
          JOIN annonces a ON f.id_annonce = a.id_annonce
          JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
          JOIN modeles m ON v.id_modele = m.id_modele 
          JOIN marques ma ON m.id_marque = ma.id_marque
          JOIN utilisateurs u ON a.id_user = u.id_user
          LEFT JOIN photos p ON a.id_annonce = p.id_annonce
          WHERE f.id_user = ?
          GROUP BY a.id_annonce, v.id_vehicule, m.id_modele, ma.id_marque, u.id_user, f.date_ajout
          ORDER BY f.date_ajout DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$favoris = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris | Auto-Market Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --cupra-dark: #050b14;
            --cupra-carbon: #0d121a;
            --cupra-copper: #c2a37d;
            --text-gray: #94a3b8;
            --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--cupra-dark); 
            color: #ffffff; 
            margin: 0; 
            padding-top: 120px; 
            overflow-x: hidden;
        }
        
        /* --- Navigation Style Cupra --- */
        nav { 
            position: fixed; 
            top: 20px; 
            left: 50%; 
            transform: translateX(-50%); 
            width: 90%; 
            max-width: 1200px; 
            background: rgba(13, 18, 26, 0.8); 
            backdrop-filter: blur(15px); 
            padding: 20px 40px; 
            border-radius: 0; /* Angles vifs Cupra */
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            z-index: 1000; 
            border: 1px solid rgba(194, 163, 125, 0.2); 
        }
        nav .logo { color: #fff; font-weight: 800; font-size: 1.5rem; text-decoration: none; letter-spacing: -1px; }
        nav .logo span { color: var(--cupra-copper); }
        nav .links a { color: #cbd5e1; text-decoration: none; margin-left: 25px; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        nav .links a:hover { color: var(--cupra-copper); }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .header-section { margin-bottom: 60px; border-left: 4px solid var(--cupra-copper); padding-left: 25px; }
        .header-section h1 { font-size: 3rem; font-weight: 800; text-transform: uppercase; letter-spacing: -2px; margin: 0; line-height: 1; }
        .header-section p { color: var(--text-gray); margin-top: 10px; font-size: 1.1rem; }
        
        /* --- Grid & Cards --- */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 50px; }
        
        .car-card { 
            background: var(--cupra-carbon); 
            border-radius: 0; 
            overflow: hidden; 
            border: 1px solid rgba(255,255,255,0.05); 
            position: relative; 
            transition: var(--transition); 
        }
        .car-card:hover { transform: translateY(-10px); border-color: var(--cupra-copper); box-shadow: 0 30px 60px rgba(0,0,0,0.5); }
        
        .image-wrapper { height: 240px; background: #161e2b; position: relative; overflow: hidden; }
        .image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 1.5s ease; }
        .car-card:hover .image-wrapper img { transform: scale(1.1); }
        
        .price-badge { 
            position: absolute; 
            bottom: 0; 
            right: 0; 
            background: var(--cupra-copper); 
            padding: 10px 20px; 
            font-weight: 800; 
            color: var(--cupra-dark); 
            font-size: 1.1rem;
        }
        
        /* Bouton suppression moderne */
        .btn-remove { 
            position: absolute; 
            top: 15px; 
            right: 15px; 
            background: rgba(239, 68, 68, 0.9); 
            width: 40px; 
            height: 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            text-decoration: none; 
            transition: 0.3s;
            z-index: 10;
        }
        .btn-remove:hover { background: #ef4444; transform: rotate(90deg); }

        .content { padding: 30px; }
        .brand-meta { font-size: 0.7rem; color: var(--cupra-copper); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        
        .specs { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            background: rgba(255,255,255,0.03); 
            padding: 15px; 
            margin: 20px 0; 
            font-size: 0.75rem; 
            color: var(--text-gray);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .specs span i { color: var(--cupra-copper); margin-right: 5px; }

        .btn-view { 
            display: block; 
            text-align: center; 
            background: transparent; 
            color: white; 
            padding: 15px; 
            text-decoration: none; 
            border: 1px solid var(--cupra-copper); 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            font-size: 0.8rem;
            transition: var(--transition);
        }
        .btn-view:hover { background: var(--cupra-copper); color: var(--cupra-dark); }
        
        /* Empty State Premium */
        .empty-state { text-align: center; padding: 120px 20px; border: 1px dashed rgba(194, 163, 125, 0.3); }
        .empty-state i { font-size: 5rem; margin-bottom: 25px; color: var(--cupra-copper); opacity: 0.3; }
        .empty-state h2 { font-size: 2rem; text-transform: uppercase; letter-spacing: -1px; }

        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
            h1 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">AUTO<span>.</span>MARKET</a>
    <div class="links">
        <a href="index.php">Explorer</a>
        <a href="favoris.php" style="color: var(--cupra-copper);">Favoris</a>
        <a href="profil.php">Garage</a>
        <a href="../logout.php" style="color: #ef4444;"><i class="fa-solid fa-power-off"></i></a>
    </div>
</nav>

<div class="container">
    <div class="header-section">
        <h1>Mes coups de cœur</h1>
        <p>Votre sélection exclusive d'automobiles d'exception.</p>
    </div>

    <?php if (empty($favoris)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-heart-crack"></i>
            <h2>Votre liste est vide</h2>
            <p>Le garage de vos rêves commence par une première sélection.</p>
            <a href="index.php" class="btn-view" style="display: inline-block; width: auto; padding: 15px 40px; margin-top: 30px;">Parcourir le catalogue</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($favoris as $auto): ?>
            <div class="car-card">
                <div class="image-wrapper">
                    <img src="../<?= $auto['chemin_url'] ?? 'assets/img/default-car.jpg' ?>" alt="Car Preview">
                    <div class="price-badge"><?= number_format($auto['prix'], 0, ',', ' ') ?> DH</div>
                    
                    <a href="ajouter_favoris.php?id=<?= $auto['id_annonce'] ?>" class="btn-remove" title="Supprimer">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>

                <div class="content">
                    <div class="brand-meta"><?= $auto['nom_marque'] ?> • <?= $auto['ville_vendeur'] ?></div>
                    <h3 style="margin: 0; font-size: 1.4rem; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($auto['titre']) ?></h3>
                    
                    <div class="specs">
                        <span><i class="fa-solid fa-calendar-days"></i> <?= $auto['annee'] ?></span>
                        <span><i class="fa-solid fa-gauge"></i> <?= number_format($auto['kilometrage'], 0, '.', ' ') ?></span>
                        <span><i class="fa-solid fa-bolt-lightning"></i> <?= $auto['carburant'] ?></span>
                    </div>

                    <a href="annonce.php?id=<?= $auto['id_annonce'] ?>" class="btn-view">Détails Véhicule</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>