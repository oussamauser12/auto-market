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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Garage | Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --cupra-gold: #c2a37d;
            --cupra-gold-dark: #a18562;
            --bg-dark: #050505;
            --card-bg: rgba(20, 20, 20, 0.7);
            --glass-border: rgba(194, 163, 125, 0.15);
            --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(194, 163, 125, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(194, 163, 125, 0.05) 0%, transparent 40%);
            color: #f1f5f9;
            margin: 0;
            padding: 60px 20px;
            min-height: 100vh;
        }

        .container { max-width: 950px; margin: 0 auto; }

        /* HEADER */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 30px;
        }

        .header-section h1 {
            font-size: 2.2rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
            margin: 0;
            background: linear-gradient(to right, #ffffff, var(--cupra-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-new {
            background: transparent;
            border: 1px solid var(--cupra-gold);
            color: var(--cupra-gold);
            padding: 14px 28px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.75rem;
            transition: var(--transition);
        }

        .btn-new:hover {
            background: var(--cupra-gold);
            color: #000;
            box-shadow: 0 10px 20px rgba(194, 163, 125, 0.2);
        }

        /* CARDS */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 6px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-card:hover {
            transform: scale(1.02);
            border-color: var(--cupra-gold);
            background: rgba(30, 30, 30, 0.9);
        }

        .car-img {
            width: 150px;
            height: 90px;
            border-radius: 4px;
            object-fit: cover;
            filter: grayscale(0.2) contrast(1.1);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .car-info { flex-grow: 1; }
        .car-info h3 { margin: 0; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; }
        .car-info p { color: var(--cupra-gold); margin: 8px 0 0; font-weight: 600; font-size: 0.95rem; }

        /* BADGES */
        .status-badge {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid currentColor;
        }

        .status-valide { background: rgba(34, 197, 94, 0.1); color: #4ade80; }
        .status-attente { background: rgba(194, 163, 125, 0.1); color: var(--cupra-gold); }

        /* ACTIONS */
        .btn-action {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            font-size: 1.1rem;
            transition: var(--transition);
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            margin-left: 10px;
        }

        .btn-edit:hover { color: var(--cupra-gold); background: rgba(194, 163, 125, 0.1); }
        .btn-delete:hover { color: #ef4444; background: rgba(239, 68, 68, 0.1); }

        .empty-state {
            text-align: center;
            padding: 100px;
            border: 1px dashed var(--glass-border);
            background: var(--card-bg);
            color: #64748b;
        }

        .back-link {
            display: inline-block;
            margin-top: 50px;
            color: #64748b;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
            transition: var(--transition);
        }

        .back-link:hover { color: var(--cupra-gold); }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div>
            <h1>Mon Garage</h1>
            <p style="color: #64748b; margin-top: 5px;">Gérez vos annonces publiées</p>
        </div>
        <a href="deposer.php" class="btn-new">+ Nouvelle Annonce</a>
    </div>

    <?php if (empty($mes_annonces)): ?>
        <div class="empty-state">
            <i class="fas fa-car-side fa-3x" style="margin-bottom: 20px; color: var(--cupra-gold);"></i>
            <h3>Votre garage est vide</h3>
            <p>Commencez à vendre vos véhicules dès maintenant.</p>
        </div>
    <?php else: ?>
        <?php foreach ($mes_annonces as $index => $annonce): ?>
            <div class="glass-card" style="animation-delay: <?= $index * 0.1 ?>s;">
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
                    <a href="supprimer_annonce.php?id=<?= $annonce['id_annonce'] ?>" class="btn-action btn-delete" onclick="return confirm('Voulez-vous vraiment retirer cette annonce ?')" title="Supprimer"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align: center;">
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    </div>
</div>

<script>
    // Animation subtile au survol des cartes
    document.querySelectorAll('.glass-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.borderColor = 'rgba(194, 163, 125, 0.5)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.borderColor = 'rgba(194, 163, 125, 0.15)';
        });
    });
</script>

</body>
</html>