<?php
session_start();
require '../config/db.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Cette requête prend le plus petit ID pour chaque nom de modèle unique
$query_modeles = "SELECT MIN(m.id_modele) as id_modele, m.nom_modele, ma.nom_marque 
                  FROM modeles m 
                  JOIN marques ma ON m.id_marque = ma.id_marque 
                  GROUP BY m.nom_modele, ma.nom_marque
                  ORDER BY ma.nom_marque ASC, m.nom_modele ASC";

$modeles = $pdo->query($query_modeles)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendre un Véhicule | Auto-Market Premium</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-muted: #94a3b8;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617; 
            color: white;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .glass-card {
            width: 100%;
            max-width: 800px;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 50px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.7s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-form { text-align: center; margin-bottom: 40px; }
        .header-form h1 { font-size: 2.5rem; font-weight: 800; margin: 0; letter-spacing: -1px; }
        .header-form p { color: var(--text-muted); margin-top: 10px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input, select, textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 15px 20px;
            border-radius: 16px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: var(--transition);
            box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-submit {
            background: var(--accent);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 18px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 30px;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .btn-submit:hover {
            background: #2563eb;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-link:hover { color: white; transform: translateX(-5px); }

        option { background: #0f172a; color: white; }
    </style>
</head>
<body>

<div class="glass-card">
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Retour au marché
    </a>

    <div class="header-form">
        <h1>Vendre un Véhicule</h1>
        <p>Entrez les détails de votre voiture pour atteindre des milliers d'acheteurs.</p>
    </div>

    <form action="traitement_depot.php" method="POST">
        <div class="form-grid">
            <div class="form-group full">
                <label>Titre de l'annonce</label>
                <input type="text" name="titre" placeholder="Ex: BMW Série 3 - État exceptionnel" required>
            </div>

            <div class="form-group">
                <label>Marque & Modèle</label>
                <select name="id_modele" required>
                    <option value="" disabled selected>Choisir un modèle</option>
                    <?php foreach($modeles as $m): ?>
                        <option value="<?= $m['id_modele'] ?>">
                            <?= htmlspecialchars($m['nom_marque']) ?> <?= htmlspecialchars($m['nom_modele']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Prix de vente (DH)</label>
                <input type="number" 
                      name="prix" 
                      placeholder="Ex: 150000" 
                      min="1" 
                     step="1" 
                   required>
            </div>

            <div class="form-group">
                <label>Année</label>
                <input type="number" name="annee" min="1900" max="<?= date('Y') ?>" placeholder="2024" required>
            </div>

            <div class="form-group">
                <label>Kilométrage (KM)</label>
                <input type="number" name="kilometrage" min="0" placeholder="0" required>
            </div>

            <div class="form-group">
                <label>Carburant</label>
                <select name="carburant">
                    <option value="Diesel">Diesel</option>
                    <option value="Essence">Essence</option>
                    <option value="Hybride">Hybride</option>
                    <option value="Electrique">Electrique</option>
                </select>
            </div>

            <div class="form-group">
                <label>Boîte de vitesse</label>
                <select name="boite_vitesse">
                    <option value="Manuelle">Manuelle</option>
                    <option value="Automatique">Automatique</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Description détaillée</label>
                <textarea name="description" rows="5" placeholder="Décrivez l'état général, les options, l'entretien..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-rocket"></i> Publier l'annonce Premium
        </button>
    </form>
</div>

</body>
</html>
