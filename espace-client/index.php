<?php
session_start();
require '../config/db.php';

$user_id = $_SESSION['user_id'] ?? 0;

// 1. Récupération de la recherche depuis l'URL (méthode GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. Construction de la requête de base
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

// 3. Ajout dynamique du filtre de recherche si le champ n'est pas vide
if (!empty($search)) {
    $query .= " AND (m.nom_modele LIKE :search OR ma.nom_marque LIKE :search)";
}

$query .= " GROUP BY a.id_annonce, v.id_vehicule, m.id_modele, ma.id_marque, u.id_user
            ORDER BY a.date_publication DESC";

$stmt = $pdo->prepare($query);

// 4. Exécution avec le paramètre de recherche sécurisé
if (!empty($search)) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}

$annonces = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Market | Premium Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f172a; --accent: #3b82f6; --bg: #f8fafc;
            --card-bg: #ffffff; --text-main: #1e293b; --text-muted: #64748b;
            --radius: 16px; --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: var(--text-main); margin: 0; }
        nav { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); padding: 15px 30px; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; border: 1px solid rgba(255, 255, 255, 0.1); }
        nav .logo { color: white; font-weight: 800; font-size: 1.5rem; text-decoration: none; }
        nav .links a { color: #cbd5e1; text-decoration: none; margin-left: 25px; font-weight: 600; transition: var(--transition); }
        .hero { height: 40vh; background: var(--primary); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding-top: 60px; }
        
        /* Modification du conteneur en FORM pour la recherche */
        .search-container { max-width: 1000px; margin: -40px auto 60px; background: var(--card-bg); padding: 10px; border-radius: 30px; display: grid; grid-template-columns: 2fr 1fr 1fr 0.5fr; gap: 10px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); border: none; }
        .search-container input, .search-container select { border: 1px solid #e2e8f0; padding: 12px 20px; border-radius: 20px; outline: none; font-family: inherit; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
        .car-card { background: var(--card-bg); border-radius: var(--radius); overflow: hidden; transition: var(--transition); border: 1px solid #f1f5f9; position: relative; }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .image-wrapper { height: 220px; background: #e2e8f0; position: relative; overflow: hidden; }
        .image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .car-card:hover .image-wrapper img { transform: scale(1.05); }
        .price-badge { position: absolute; bottom: 15px; left: 15px; background: white; padding: 8px 15px; border-radius: 12px; font-weight: 800; color: var(--primary); }
        .content { padding: 20px; }
        .specs { display: flex; justify-content: space-between; background: #f8fafc; padding: 10px; border-radius: 12px; margin: 15px 0; font-size: 0.85rem; }
        .btn-view { display: block; text-align: center; background: var(--primary); color: white; padding: 12px; text-decoration: none; border-radius: 10px; font-weight: 700; }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">AUTO.MARKET</a>
    <div class="links">
        <a href="index.php">Explorer</a>
        <a href="favoris.php">Mes Favoris</a>
        <a href="profil.php">Mon Garage</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="../logout.php" style="color: #ef4444;"><i class="fa-solid fa-power-off"></i></a>
        <?php else: ?>
            <a href="../login.php" style="background: var(--accent); padding: 8px 20px; border-radius: 12px; color: white;">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero">
    <h1>Conduisez l'Excellence.</h1>
    <p>La marketplace automobile n°1 pour l'ENSA Khouribga.</p>
</section>

<div class="container">
    <form action="index.php" method="GET" class="search-container">
        <input type="text" name="search" placeholder="Modèle, marque..." value="<?= htmlspecialchars($search) ?>">
        <select name="ville">
            <option value="">Toutes villes</option>
        </select>
        <select name="budget">
            <option value="">Budget max</option>
        </select>
        <button type="submit" style="background: var(--accent); color: white; border: none; border-radius: 15px; cursor: pointer;">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>

    <div class="grid">
        <?php if (count($annonces) > 0): ?>
            <?php foreach ($annonces as $auto): ?>
            <div class="car-card">
                <div class="image-wrapper">
                    <img src="../<?= $auto['chemin_url'] ?? 'assets/img/default-car.jpg' ?>" alt="Car">
                    <div class="price-badge"><?= number_format($auto['prix'], 0, ',', ' ') ?> DH</div>
                    
                    <a href="ajouter_favoris.php?id=<?= $auto['id_annonce'] ?>" 
                       style="position: absolute; top: 15px; right: 15px; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ef4444; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="<?= ($auto['is_fav'] > 0) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
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

                    <div style="display: flex; gap: 10px;">
                        <a href="annonce.php?id=<?= $auto['id_annonce'] ?>" class="btn-view" style="flex: 1;">Détails</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
                <i class="fa-solid fa-car-side" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                <p>Aucune voiture ne correspond à votre recherche "<strong><?= htmlspecialchars($search) ?></strong>".</p>
                <a href="index.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Voir toutes les annonces</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer style="padding: 40px 0; text-align: center; color: var(--text-muted);">
    <p>© 2026 Auto-Market | ENSA Khouribga Edition</p>
</footer>
</body>
</html>