<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];
$error = "";
$success = "";

// Logique de suppression
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    if ($id_to_delete === $current_user_id) {
        $error = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $pdo->prepare("DELETE FROM utilisateurs WHERE id_user = ?")->execute([$id_to_delete]);
        header('Location: gestion-users.php?msg=deleted');
        exit();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success = "Utilisateur supprimé avec succès.";
}

// Récupérer tous les utilisateurs
$users      = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();
$total      = count($users);
$nb_admins  = count(array_filter($users, fn($u) => $u['role'] == 1));
$nb_clients = $total - $nb_admins;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs | Auto-Market Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent:      #c2a37d;
            --accent-dim:  rgba(194, 163, 125, 0.12);
            --bg-deep:     #0a0a0b;
            --sidebar-bg:  #111112;
            --card-bg:     rgba(255, 255, 255, 0.03);
            --border:      rgba(255, 255, 255, 0.08);
            --text-main:   #ffffff;
            --text-muted:  #94a3b8;
            --danger:      #ef4444;
            --danger-dim:  rgba(239, 68, 68, 0.1);
            --success:     #34d399;
            --success-dim: rgba(52, 211, 153, 0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-header { padding: 40px 30px 30px; }

        .sidebar-logo {
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 3px;
            color: var(--accent);
            text-transform: uppercase;
        }
        .sidebar-logo span { color: white; }

        .sidebar-menu { flex: 1; padding: 10px 20px; }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.25s ease;
        }
        .menu-item i { margin-right: 14px; width: 18px; text-align: center; font-size: 0.95rem; }
        .menu-item:hover { background: var(--card-bg); color: var(--accent); }
        .menu-item.active { background: var(--accent-dim); color: var(--accent); font-weight: 700; }

        .sidebar-footer {
            padding: 24px 30px;
            border-top: 1px solid var(--border);
        }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--danger);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .logout-btn:hover { opacity: 0.75; }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 44px 40px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 36px;
        }
        .page-header h1 { font-size: 1.9rem; font-weight: 800; }
        .page-header .subtitle { color: var(--text-muted); font-size: 0.85rem; margin-top: 4px; }

        .total-badge {
            background: var(--accent-dim);
            color: var(--accent);
            padding: 8px 18px;
            border-radius: 100px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 0.88rem;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error   { background: var(--danger-dim);  border: 1px solid rgba(239,68,68,0.25);  color: #fca5a5; }
        .alert-success { background: var(--success-dim); border: 1px solid rgba(52,211,153,0.25); color: #6ee7b7; }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-mini {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 26px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-mini .icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            background: var(--accent-dim);
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .stat-mini .label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .stat-mini .value { font-size: 1.6rem; font-weight: 800; margin-top: 2px; }

        .table-container {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
        }

        .table-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 28px;
            border-bottom: 1px solid var(--border);
        }
        .table-top h2 { font-size: 1rem; font-weight: 700; }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 16px;
        }
        .search-box i { color: var(--text-muted); font-size: 0.85rem; }
        .search-box input {
            background: none; border: none; outline: none;
            color: white; font-family: inherit;
            font-size: 0.88rem; width: 200px;
        }
        .search-box input::placeholder { color: #475569; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; padding: 16px 24px;
            font-size: 0.72rem; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 1.2px;
            font-weight: 600; background: rgba(255,255,255,0.015);
        }
        tbody td {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            font-size: 0.88rem; vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background 0.2s; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }

        .user-id { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; }

        .user-cell { display: flex; align-items: center; gap: 12px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--accent-dim); color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.85rem; flex-shrink: 0;
        }
        .user-name  { font-weight: 600; font-size: 0.9rem; }
        .user-email { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }

        .badge {
            padding: 5px 12px; border-radius: 100px;
            font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-admin  { background: var(--accent-dim); color: var(--accent); }
        .badge-client { background: rgba(100,116,139,0.15); color: #94a3b8; }

        .date-cell { color: var(--text-muted); font-size: 0.82rem; }

        .btn-ban {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--danger-dim); color: var(--danger);
            border: 1px solid rgba(239,68,68,0.2);
            padding: 8px 14px; border-radius: 9px;
            font-size: 0.78rem; font-weight: 700;
            text-decoration: none; transition: all 0.25s;
        }
        .btn-ban:hover { background: var(--danger); color: white; transform: translateY(-2px); }

        .vous-tag { font-size: 0.78rem; color: var(--accent); font-style: italic; font-weight: 600; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 2.5rem; margin-bottom: 14px; opacity: 0.4; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">AUTO<span>.</span>MARKET</div>
        <div style="font-size:0.72rem;color:#475569;margin-top:6px;letter-spacing:1px;">ADMIN PANEL</div>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item"><i class="fas fa-chart-line"></i> Tableau de bord</a>
        <a href="moderer.php" class="menu-item"><i class="fas fa-shield-halved"></i> Modération</a>
        <a href="gestion-users.php" class="menu-item active"><i class="fas fa-users"></i> Utilisateurs</a>
    </nav>
    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-arrow-right-from-bracket"></i> Déconnexion
        </a>
    </div>
</aside>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1>Gestion des Comptes</h1>
            <p class="subtitle">Gérez les utilisateurs inscrits sur la plateforme</p>
        </div>
        <span class="total-badge"><i class="fas fa-users" style="margin-right:7px;"></i>Total : <?= $total ?> inscrits</span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-mini">
            <div class="icon"><i class="fas fa-users"></i></div>
            <div><div class="label">Total membres</div><div class="value"><?= $total ?></div></div>
        </div>
        <div class="stat-mini">
            <div class="icon"><i class="fas fa-user-shield"></i></div>
            <div><div class="label">Administrateurs</div><div class="value"><?= $nb_admins ?></div></div>
        </div>
        <div class="stat-mini">
            <div class="icon"><i class="fas fa-user"></i></div>
            <div><div class="label">Clients</div><div class="value"><?= $nb_clients ?></div></div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-top">
            <h2><i class="fas fa-list" style="color:var(--accent);margin-right:10px;"></i>Liste des utilisateurs</h2>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un utilisateur...">
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Utilisateur</th><th>Ville</th><th>Rôle</th><th>Inscription</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                <?php if (empty($users)): ?>
                <tr><td colspan="6"><div class="empty-state"><i class="fas fa-users-slash"></i><p>Aucun utilisateur trouvé.</p></div></td></tr>
                <?php else: ?>
                <?php foreach ($users as $u):
                    $initiales = strtoupper(substr($u['nom'], 0, 1) . substr($u['prenom'], 0, 1));
                ?>
                <tr class="user-row" data-search="<?= strtolower(htmlspecialchars($u['nom'] . ' ' . $u['prenom'] . ' ' . $u['email'])) ?>">
                    <td><span class="user-id">#<?= $u['id_user'] ?></span></td>
                    <td>
                        <div class="user-cell">
                            <div class="avatar"><?= $initiales ?></div>
                            <div>
                                <div class="user-name"><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></div>
                                <div class="user-email"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="date-cell"><?= htmlspecialchars($u['ville'] ?? 'Non précisée') ?></td>
                    <td>
                        <?php if ($u['role'] == 1): ?>
                            <span class="badge badge-admin"><i class="fas fa-shield-halved" style="margin-right:5px;"></i>Administrateur</span>
                        <?php else: ?>
                            <span class="badge badge-client"><i class="fas fa-user" style="margin-right:5px;"></i>Client</span>
                        <?php endif; ?>
                    </td>
                    <td class="date-cell">
                        <i class="fas fa-calendar-alt" style="margin-right:6px;opacity:0.5;"></i>
                        <?= date('d/m/Y', strtotime($u['date_inscription'])) ?>
                    </td>
                    <td>
                        <?php if ($u['id_user'] == $current_user_id): ?>
                            <span class="vous-tag"><i class="fas fa-circle-check" style="margin-right:5px;"></i>(Vous)</span>
                        <?php else: ?>
                            <a href="gestion-users.php?delete_id=<?= $u['id_user'] ?>"
                               class="btn-ban"
                               onclick="return confirm('Supprimer cet utilisateur et toutes ses annonces ?')">
                                <i class="fas fa-ban"></i> Bannir
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.user-row').forEach(row => {
            const data = row.getAttribute('data-search');
            row.style.display = (!q || data.includes(q)) ? '' : 'none';
        });
    });
</script>

</body>
</html>