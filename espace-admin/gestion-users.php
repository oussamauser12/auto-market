<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Logique de suppression d'un utilisateur
if (isset($_GET['delete_id'])) {
    $id_to_delete = $_GET['delete_id'];
    
    // Empêcher l'admin de se supprimer lui-même
    if ($id_to_delete == $_SESSION['user_id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte administrateur.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_user = ?");
        $stmt->execute([$id_to_delete]);
        header('Location: gestion-users.php?msg=deleted');
        exit();
    }
}

// Récupérer tous les utilisateurs
$users = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Utilisateurs - Auto-Market</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .badge-admin { background: #1a237e; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; }
        .badge-client { background: #e0e0e0; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Auto-Market Admin</h2>
        <ul>
            <li><a href="dashboard.php">Tableau de bord</a></li>
            <li><a href="moderer.php">Modération</a></li>
            <li><a href="gestion-users.php" class="active">Utilisateurs</a></li>
            <li><a href="../logout.php">Déconnexion</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-admin">
            <h1>Gestion des Comptes</h1>
            <span>Total : <?= count($users) ?> inscrits</span>
        </div>

        <?php if(isset($error)): ?>
            <p style="color: red; background: #ffdada; padding: 10px; border-radius: 5px;"><?= $error ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Ville</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td>#<?= $u['id_user'] ?></td>
                    <td><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['ville'] ?? 'Non précisée') ?></td>
                    <td>
                        <?php if($u['role'] == 1): ?>
                            <span class="badge-admin">Administrateur</span>
                        <?php else: ?>
                            <span class="badge-client">Client</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                    <td>
                        <?php if($u['id_user'] != $_SESSION['user_id']): ?>
                            <a href="gestion-users.php?delete_id=<?= $u['id_user'] ?>" 
                               class="btn-action btn-delete" 
                               onclick="return confirm('Attention : cela supprimera aussi toutes les annonces de cet utilisateur. Confirmer ?')">
                               Bannir
                            </a>
                        <?php else: ?>
                            <small><i>(Vous)</i></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>