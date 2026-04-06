<?php
session_start();
require '../config/db.php';

// Sécurité : Rediriger si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['user_id'];

// Récupérer uniquement les annonces de cet utilisateur
$query = "SELECT a.*, v.*, m.nom_modele, ma.nom_marque 
          FROM annonces a 
          JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
          JOIN modeles m ON v.id_modele = m.id_modele 
          JOIN marques ma ON m.id_marque = ma.id_marque
          WHERE a.id_user = ? 
          ORDER BY a.date_publication DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$id_user]);
$mes_annonces = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Garage | Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #020617;
            color: white;
            margin: 0;
            padding: 40px 20px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .glass-card:hover { transform: translateX(10px); background: rgba(255,255,255,0.05); }

        .car-img {
            width: 120px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
        }

        .car-info { flex-grow: 1; }
        .car-info h3 { margin: 0; font-size: 1.2rem; }
        .car-info p { color: #94a3b8; margin: 5px 0 0; font-size: 0.9rem; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-valide { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .status-attente { background: rgba(234, 179, 8, 0.2); color: #eab308; }

        .btn-action {
            color: #94a3b8;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.3s;
            margin-left: 15px;
        }

        .btn-delete:hover { color: #ef4444; }
        .btn-edit:hover { color: var(--accent); }

        .empty-state {
            text-align: center;
            padding: 80px;
            border: 2px dashed var(--border);
            border-radius: 32px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div>
            <h1 style="margin:0;">Mon Garage</h1>
            <p style="color: #94a3b8;">Gérez vos annonces publiées</p>
        </div>
        <a href="deposer.php" style="background: var(--accent); color:white; padding: 12px 24px; border-radius: 12px; text-decoration:none; font-weight:700;">+ Nouvelle Annonce</a>
    </div>

    <?php if (empty($mes_annonces)): ?>
        <div class="empty-state">
            <i class="fas fa-car-side fa-3x" style="margin-bottom: 20px;"></i>
            <h3>Votre garage est vide</h3>
            <p>Commencez à vendre vos véhicules dès maintenant.</p>
        </div>
    <?php else: ?>
        <?php foreach ($mes_annonces as $annonce): ?>
            <div class="glass-card">
                <img src="https://source.unsplash.com/400x300/?car,<?= $annonce['nom_marque'] ?>" class="car-img" alt="Voiture">
                
                <div class="car-info">
                    <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                    <p><?= $annonce['nom_marque'] ?> <?= $annonce['nom_modele'] ?> • <?= number_format($annonce['prix'], 0, ',', ' ') ?> DH</p>
                </div>

                <span class="status-badge <?= ($annonce['statut'] == 'valide') ? 'status-valide' : 'status-attente' ?>">
                    <?= $annonce['statut'] ?>
                </span>

                <div style="display: flex;">
                    <a href="annonce.php?id=<?= $annonce['id_annonce'] ?>" class="btn-action btn-edit" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="supprimer_annonce.php?id=<?= $annonce['id_annonce'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer cette annonce ?')" title="Supprimer"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top: 40px; text-align: center;">
        <a href="index.php" style="color: #94a3b8; text-decoration: none;"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    </div>
</div>

</body>
</html>