<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est admin (role = 1)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Requête sécurisée (on utilise 'mdl' au lieu de 'mod' qui est un mot réservé)
$sqlMarques = "SELECT m.nom_marque as marque, COUNT(a.id_annonce) as nb 
               FROM annonces a
               JOIN vehicules v ON a.id_vehicule = v.id_vehicule
               JOIN modeles mdl ON v.id_modele = mdl.id_modele
               JOIN marques m ON mdl.id_marque = m.id_marque
               GROUP BY m.nom_marque";

$marquesQuery = $pdo->query($sqlMarques);
$marquesData = $marquesQuery->fetchAll(PDO::FETCH_ASSOC);
// 2. Répartition par Carburant (Utilisation de JOIN car le carburant est dans la table 'vehicules')
$sqlCarburant = "SELECT v.carburant, COUNT(a.id_annonce) as nb 
                 FROM annonces a 
                 JOIN vehicules v ON a.id_vehicule = v.id_vehicule 
                 GROUP BY v.carburant";
$carburantQuery = $pdo->query($sqlCarburant);
$carburantData = $carburantQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupération des statistiques globales
$totalAnnonces = $pdo->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
$attente = $pdo->query("SELECT COUNT(*) FROM annonces WHERE statut = 'en_attente'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 0")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Auto-Market</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Quelques ajustements pour l'affichage des graphes */
        .charts-container {
            display: flex; 
            gap: 20px; 
            margin-top: 30px;
            padding-bottom: 50px;
        }
        canvas {
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Auto-Market Admin</h2>
        <ul>
            <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
            <li><a href="moderer.php">Modération (<?= $attente ?>)</a></li>
            <li><a href="gestion-users.php">Utilisateurs</a></li>
            <li><a href="../index.php">Voir le site</a></li>
            <li><a href="../logout.php">Déconnexion</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-admin">
            <h1>Tableau de Bord</h1>
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?></span>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="card" style="flex: 1; background: #fff; padding: 20px; border-left: 5px solid #1a237e; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3>Total Annonces</h3>
                <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;"><?= $totalAnnonces ?></p>
            </div>
            <div class="card" style="flex: 1; background: #fff; padding: 20px; border-left: 5px solid #fbc02d; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3>En attente</h3>
                <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;"><?= $attente ?></p>
            </div>
            <div class="card" style="flex: 1; background: #fff; padding: 20px; border-left: 5px solid #2e7d32; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3>Clients Inscrits</h3>
                <p style="font-size: 2rem; font-weight: bold; margin: 10px 0;"><?= $totalUsers ?></p>
            </div>
        </div>

        <div class="charts-container">
            <div class="card" style="flex: 2; background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 20px;">Annonces par Marque</h3>
                <canvas id="marqueChart"></canvas>
            </div>

            <div class="card" style="flex: 1; background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 20px;">Types de Carburant</h3>
                <canvas id="carburantChart"></canvas>
            </div>
        </div>
    </div>

    <script>
    // Configuration des couleurs
    const bluePrimary = '#3b82f6';
    const chartColors = ['#ef4444', '#10b981', '#f59e0b', '#6366f1', '#ec4899'];

    // Graphique des Marques (Bar Chart)
    const ctxMarque = document.getElementById('marqueChart').getContext('2d');
    new Chart(ctxMarque, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($marquesData, 'marque')) ?>,
            datasets: [{
                label: 'Nombre de voitures',
                data: <?= json_encode(array_column($marquesData, 'nb')) ?>,
                backgroundColor: bluePrimary,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Graphique des Carburants (Doughnut Chart)
    const ctxCarb = document.getElementById('carburantChart').getContext('2d');
    new Chart(ctxCarb, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($carburantData, 'carburant')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($carburantData, 'nb')) ?>,
                backgroundColor: chartColors,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '65%'
        }
    });
    </script>
</body>
</html>