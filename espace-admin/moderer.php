<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../login.php'); exit();
}

// Logique de validation ou suppression
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'valider') {
        $pdo->prepare("UPDATE annonces SET statut = 'valide' WHERE id_annonce = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'supprimer') {
        $pdo->prepare("DELETE FROM annonces WHERE id_annonce = ?")->execute([$id]);
    }
    header('Location: moderer.php'); // Recharger la page proprement
}

// Récupérer les annonces en attente avec les détails du véhicule
$query = "SELECT a.*, u.nom as proprio, m.nom_modele 
          FROM annonces a 
          JOIN utilisateurs u ON a.id_user = u.id_user
          JOIN vehicules v ON a.id_vehicule = v.id_vehicule
          JOIN modeles m ON v.id_modele = m.id_modele
          WHERE a.statut = 'en_attente'";
$annonces = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <title>Modération - Auto-Market</title>
</head>
<body>
    <div class="sidebar">
        <h2>Auto-Market Admin</h2>
        <ul>
            <li><a href="dashboard.php">Tableau de bord</a></li>
            <li><a href="moderer.php">Modération</a></li>
            <li><a href="../logout.php">Déconnexion</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-admin">
            <h1>Annonces à valider</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Vendeur</th>
                    <th>Modèle</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($annonces as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['titre']) ?></td>
                    <td><?= htmlspecialchars($a['proprio']) ?></td>
                    <td><?= htmlspecialchars($a['nom_modele']) ?></td>
                    <td><?= number_format($a['prix'], 0, ',', ' ') ?> DH</td>
                    <td>
                        <a href="moderer.php?action=valider&id=<?= $a['id_annonce'] ?>" class="btn-action btn-approve">Valider</a>
                        <a href="moderer.php?action=supprimer&id=<?= $a['id_annonce'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer définitivement ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($annonces)): ?>
                <tr><td colspan="5" style="text-align:center;">Aucune annonce en attente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>