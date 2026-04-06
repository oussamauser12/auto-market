<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// REQUÊTE CORRIGÉE
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
    <title>Mes Favoris | Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f172a; --accent: #3b82f6; --bg: #f8fafc;
            --card-bg: #ffffff; --text-main: #1e293b; --text-muted: #64748b;
            --radius: 16px; --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: var(--text-main); margin: 0; padding-top: 100px; }
        
        nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); padding: 15px 30px; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; border: 1px solid rgba(255, 255, 255, 0.1); }
        nav .logo { color: white; font-weight: 800; font-size: 1.5rem; text-decoration: none; }
        nav .links a { color: #cbd5e1; text-decoration: none; margin-left: 25px; font-weight: 600; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .header-section { margin-bottom: 40px; }
        .header-section h1 { font-size: 2.5rem; color: var(--primary); margin: 0; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 50px; }
        .car-card { background: var(--card-bg); border-radius: var(--radius); overflow: hidden; border: 1px solid #f1f5f9; position: relative; transition: var(--transition); }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        
        .image-wrapper { height: 220px; background: #e2e8f0; position: relative; }
        .image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        
        .price-badge { position: absolute; bottom: 15px; left: 15px; background: white; padding: 8px 15px; border-radius: 12px; font-weight: 800; color: var(--primary); }
        
        /* Bouton pour retirer des favoris */
        .btn-remove { position: absolute; top: 15px; right: 15px; background: #ef4444; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

        .content { padding: 20px; }
        .specs { display: flex; justify-content: space-between; background: #f8fafc; padding: 10px; border-radius: 12px; margin: 15px 0; font-size: 0.85rem; color: var(--text-muted); }
        .btn-view { display: block; text-align: center; background: var(--primary); color: white; padding: 12px; text-decoration: none; border-radius: 10px; font-weight: 700; }
        
        .empty-state { text-align: center; padding: 100px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; color: #e2e8f0; }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">AUTO.MARKET</a>
    <div class="links">
        <a href="index.php">Explorer</a>
        <a href="favoris.php" style="color: var(--accent);">Mes Favoris</a>
        <a href="profil.php">Mon Garage</a>
        <a href="../logout.php" style="color: #ef4444;"><i class="fa-solid fa-power-off"></i></a>
    </div>
</nav>

<div class="container">
    <div class="header-section">
        <h1>Mes coups de cœur ❤️</h1>
        <p>Retrouvez ici toutes les annonces que vous avez sauvegardées.</p>
    </div>

    <?php if (empty($favoris)): ?>
        <div class="empty-state">
            <i class="fa-regular fa-heart"></i>
            <h2>Aucun favori pour le moment</h2>
            <p>Parcourez les annonces et cliquez sur le cœur pour les retrouver ici.</p>
            <a href="index.php" class="btn-view" style="display: inline-block; width: auto; padding: 12px 30px; margin-top: 20px;">Explorer les annonces</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($favoris as $auto): ?>
            <div class="car-card">
                <div class="image-wrapper">
                    <img src="../<?= $auto['chemin_url'] ?? 'assets/img/default-car.jpg' ?>" alt="Car">
                    <div class="price-badge"><?= number_format($auto['prix'], 0, ',', ' ') ?> DH</div>
                    
                    <a href="ajouter_favoris.php?id=<?= $auto['id_annonce'] ?>" class="btn-remove" title="Retirer des favoris">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </div>

                <div class="content">
                    <div style="font-size: 0.75rem; color: var(--accent); font-weight: 700; text-transform: uppercase;"><?= $auto['nom_marque'] ?> • <?= $auto['ville_vendeur'] ?></div>
                    <h3 style="margin: 5px 0; font-size: 1.2rem;"><?= htmlspecialchars($auto['titre']) ?></h3>
                    
                    <div class="specs">
                        <span><i class="fa-solid fa-calendar"></i> <?= $auto['annee'] ?></span>
                        <span><i class="fa-solid fa-gauge-high"></i> <?= $auto['kilometrage'] ?> km</span>
                        <span><i class="fa-solid fa-bolt"></i> <?= $auto['carburant'] ?></span>
                    </div>

                    <a href="annonce.php?id=<?= $auto['id_annonce'] ?>" class="btn-view">Voir l'annonce</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>