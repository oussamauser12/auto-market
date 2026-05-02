<?php
session_start();
require '../config/db.php';

// --- PROTECTION STRICTE ---
// Vérifie si l'utilisateur est connecté ET si son rôle est admin (1)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

// Logique pour Valider ou Supprimer une annonce
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'valider') {
        $pdo->prepare("UPDATE annonces SET statut = 'valide' WHERE id_annonce = ?")->execute([$id]);
    } elseif ($_GET['action'] === 'supprimer') {
        $pdo->prepare("DELETE FROM annonces WHERE id_annonce = ?")->execute([$id]);
    }
    header('Location: dashboard.php'); // Redirection pour éviter de renvoyer le formulaire
    exit();
}

// Récupérer les statistiques pour les cartes
$total_annonces = $pdo->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
$attente = $pdo->query("SELECT COUNT(*) FROM annonces WHERE statut = 'en_attente'")->fetchColumn();
$utilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();

// Récupérer les annonces
$annonces = $pdo->query("SELECT a.*, m.nom_modele, ma.nom_marque, u.nom as vendeur 
                         FROM annonces a 
                         JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
                         JOIN modeles m ON v.id_modele = m.id_modele 
                         JOIN marques ma ON m.id_marque = ma.id_marque
                         JOIN utilisateurs u ON a.id_user = u.id_user
                         ORDER BY a.statut ASC, a.date_publication DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent: #c2a37d; /* Gold/Copper */
            --bg-deep: #0a0a0b;
            --sidebar-bg: #111112;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-main);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .sidebar-header {
            padding: 40px 30px;
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: 2px;
            color: var(--accent);
        }

        .sidebar-menu {
            flex: 1;
            padding: 0 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .menu-item i { margin-right: 15px; width: 20px; text-align: center; }

        .menu-item.active, .menu-item:hover {
            background: var(--card-bg);
            color: var(--accent);
        }

        .sidebar-footer {
            padding: 30px;
            border-top: 1px solid var(--border);
        }

        .logout-btn {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header-top h1 { font-size: 1.8rem; font-weight: 800; margin: 0; }

        /* --- STAT CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .stat-card h3 { color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin: 0 0 10px 0; letter-spacing: 1px; }
        .stat-card .value { font-size: 2rem; font-weight: 800; }
        .stat-card .icon-bg {
            position: absolute; right: -10px; bottom: -10px;
            font-size: 5rem; opacity: 0.05; color: var(--accent);
        }

        /* --- TABLE STYLE --- */
        .table-container {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; padding: 20px; 
            background: rgba(255,255,255,0.02); 
            font-size: 0.75rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 1px;
        }
        td { padding: 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }

        .status-pill {
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-valide { background: rgba(52, 211, 153, 0.1); color: #34d399; }
        .status-en_attente { background: rgba(251, 191, 36, 0.1); color: #fbbf24; }

        .action-btns { display: flex; gap: 10px; }
        .btn {
            width: 35px; height: 35px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: 0.3s;
        }
        .btn-approve { background: var(--accent); color: black; }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .btn:hover { transform: translateY(-3px); }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">AUTO.<span>ADMIN</span></div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item active">
            <i class="fas fa-chart-pie"></i> Vue d'ensemble
        </a>
        <a href="gestion-users.php" class="menu-item">
            <i class="fas fa-users"></i> Utilisateurs
        </a>
        <a href="moderer.php" class="menu-item">
            <i class="fas fa-check-circle"></i> Modération
        </a>
        <a href="../index.php" class="menu-item">
            <i class="fas fa-eye"></i> Voir le site
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-power-off"></i> &nbsp; Déconnexion
        </a>
    </div>
</div>

<div class="main-content">
    <div class="header-top">
        <h1>Tableau de bord</h1>
        <div class="admin-profile">
            <span style="color: var(--text-muted);">Bienvenue,</span> 
            <strong>Admin</strong>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Annonces</h3>
            <div class="value"><?= $total_annonces ?></div>
            <i class="fas fa-car icon-bg"></i>
        </div>
        <div class="stat-card">
            <h3>En attente</h3>
            <div class="value" style="color: #fbbf24;"><?= $attente ?></div>
            <i class="fas fa-clock icon-bg"></i>
        </div>
        <div class="stat-card">
            <h3>Utilisateurs</h3>
            <div class="value"><?= $utilisateurs ?></div>
            <i class="fas fa-user-friends icon-bg"></i>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Véhicule</th>
                    <th>Vendeur</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($annonces as $a): ?>
                <tr>
                    <td><span style="color: var(--text-muted);">#<?= $a['id_annonce'] ?></span></td>
                    <td>
                        <div style="font-weight: 700;"><?= $a['nom_marque'] ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $a['nom_modele'] ?></div>
                    </td>
                    <td><?= htmlspecialchars($a['vendeur']) ?></td>
                    <td style="font-weight: 700; color: var(--accent);">
                        <?= number_format($a['prix'], 0, ',', ' ') ?> DH
                    </td>
                    <td>
                        <span class="status-pill status-<?= $a['statut'] ?>">
                            <?= str_replace('_', ' ', $a['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <?php if($a['statut'] === 'en_attente'): ?>
                                <a href="?action=valider&id=<?= $a['id_annonce'] ?>" class="btn btn-approve" title="Valider">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <a href="?action=supprimer&id=<?= $a['id_annonce'] ?>" 
                               class="btn btn-delete" 
                               title="Supprimer" 
                               onclick="return confirm('Supprimer définitivement ?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>