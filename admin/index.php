<?php
session_start();
require '../config/db.php';

// 1. Récupération de TOUTES les annonces en attente avec LEFT JOIN pour éviter les lignes masquées
$query = "SELECT a.*, v.*, m.nom_modele, ma.nom_marque, u.nom, u.prenom 
          FROM annonces a 
          LEFT JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
          LEFT JOIN modeles m ON v.id_modele = m.id_modele 
          LEFT JOIN marques ma ON m.id_marque = ma.id_marque
          LEFT JOIN utilisateurs u ON a.id_user = u.id_user
          WHERE a.statut = 'en_attente' 
          ORDER BY a.date_publication ASC";

$annonces_attente = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg: #020617; --glass: rgba(255,255,255,0.03); --border: rgba(255,255,255,0.1); --admin-accent: #8b5cf6; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: white; padding: 40px; }
        .admin-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 100px 1fr auto;
            align-items: center;
            gap: 20px;
        }
        .btn { padding: 12px 20px; border-radius: 12px; border: none; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-check { background: #22c55e; color: white; }
        .btn-check:hover { background: #16a34a; transform: translateY(-2px); }
        .btn-reject { background: #ef4444; color: white; margin-left: 10px; }
        .btn-reject:hover { background: #dc2626; transform: translateY(-2px); }
        .badge { background: var(--admin-accent); padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
        <div>
            <h1 style="margin:0;">Dashboard <span style="color: var(--admin-accent)">Moderation</span></h1>
            <p style="color: #64748b; margin-top: 5px;">Gestion des annonces entrantes</p>
        </div>
        <div class="badge"><i class="fas fa-user-shield"></i> Admin Connecté</div>
    </div>

    <?php if(empty($annonces_attente)): ?>
        <div style="text-align: center; padding: 50px; border: 2px dashed var(--border); border-radius: 24px;">
            <i class="fas fa-check-circle fa-3x" style="color: #22c55e; margin-bottom: 15px;"></i>
            <p style="color: #94a3b8; font-size: 1.1rem;">Félicitations ! Aucune annonce n'attend votre validation.</p>
        </div>
    <?php else: ?>
        <?php foreach($annonces_attente as $a): ?>
            <div class="admin-card">
                <img src="https://source.unsplash.com/100x100/?car,<?= urlencode($a['nom_marque']) ?>" style="border-radius: 15px; width: 100px; height: 100px; object-fit: cover;">
                
                <div>
                    <h3 style="margin: 0; font-size: 1.3rem;"><?= htmlspecialchars($a['titre']) ?></h3>
                    <p style="margin: 8px 0; color: #94a3b8;">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($a['prenom'] ?? 'Anonyme') ?> <?= htmlspecialchars($a['nom'] ?? '') ?> 
                        <span style="margin: 0 10px;">•</span> 
                        <i class="fas fa-tag"></i> <b style="color: white;"><?= number_format($a['prix'], 0, ',', ' ') ?> DH</b>
                    </p>
                    <div style="font-size: 0.85rem; color: #64748b;">
                        <?= htmlspecialchars($a['nom_marque']) ?> <?= htmlspecialchars($a['nom_modele']) ?> (<?= $a['annee'] ?>)
                    </div>
                </div>

                <div>
                    <a href="valider.php?id=<?= $a['id_annonce'] ?>&action=accepter" class="btn btn-check">
                        <i class="fas fa-check"></i> Valider
                    </a>
                    <a href="valider.php?id=<?= $a['id_annonce'] ?>&action=refuser" class="btn btn-reject">
                        <i class="fas fa-times"></i> Refuser
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top: 40px; text-align: center;">
        <a href="../espace-client/index.php" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left"></i> Retour au site principal
        </a>
    </div>

</body>
</html>