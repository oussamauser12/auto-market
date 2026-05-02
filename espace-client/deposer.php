<?php
session_start();
require '../config/db.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Récupération des modèles
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
    <title>Vendre | Auto-Market Premium</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --cupra-copper: #c2a37d;
            --cupra-copper-bright: #e5c9a7;
            --bg-deep: #050505;
            --glass: rgba(15, 15, 15, 0.8);
            --border: rgba(194, 163, 125, 0.2);
            --text-gray: #888;
            --transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-deep);
            color: #fff;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(194, 163, 125, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(194, 163, 125, 0.05) 0%, transparent 40%);
        }

        .container {
            width: 100%;
            max-width: 900px;
            padding: 40px 20px;
        }

        .cupra-card {
            background: var(--glass);
            backdrop-filter: blur(30px);
            border: 1px solid var(--border);
            border-radius: 4px; /* Bords droits style Cupra */
            padding: 60px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 50px 100px -20px rgba(0,0,0,0.7);
        }

        /* Effet de ligne lumineuse en haut */
        .cupra-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--cupra-copper), transparent);
        }

        .back-link {
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--cupra-copper);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            transition: var(--transition);
        }

        .back-link:hover {
            color: #fff;
            transform: translateX(-5px);
        }

        .header-form h1 {
            font-size: 3rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -2px;
            margin: 0;
            line-height: 0.9;
        }

        .header-form p {
            color: var(--text-gray);
            font-size: 1rem;
            margin: 20px 0 50px 0;
            max-width: 500px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-group.full { grid-column: 1 / -1; }

        label {
            display: block;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--cupra-copper);
            margin-bottom: 12px;
        }

        input, select, textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 0;
            color: #fff;
            font-family: inherit;
            font-size: 1.1rem;
            outline: none;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            border-bottom-color: var(--cupra-copper);
            background: rgba(194, 163, 125, 0.03);
        }

        /* Custom Select */
        select { cursor: pointer; }
        option { background: #111; color: #fff; }

        .btn-submit {
            margin-top: 60px;
            background: var(--cupra-copper);
            color: #000;
            border: none;
            padding: 22px;
            width: 100%;
            font-family: inherit;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .btn-submit:hover {
            background: #fff;
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(194, 163, 125, 0.2);
        }

        .btn-submit i {
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .btn-submit:hover i {
            transform: translateX(10px);
        }

        /* Animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: var(--transition);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .cupra-card { padding: 30px; }
            .header-form h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="cupra-card">
        <a href="index.php" class="back-link reveal">
            <i class="fas fa-chevron-left"></i> Retour
        </a>

        <div class="header-form reveal">
            <h1>Libérez<br><span style="color: var(--cupra-copper)">votre Puissance.</span></h1>
            <p>Vendez votre véhicule sur la plateforme la plus exclusive du marché automobile.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#fca5a5;padding:14px 18px;border-radius:3px;margin-bottom:24px;font-size:0.88rem;">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                $errs = ['donnees_invalides' => 'Données invalides. Vérifiez les champs.', 'prix_invalide' => 'Le prix doit être supérieur à 0.', 'serveur' => 'Erreur serveur. Réessayez.'];
                echo $errs[$_GET['error']] ?? 'Une erreur est survenue.';
                ?>
            </div>
        <?php endif; ?>

        <form action="traitement_depot.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group full reveal">
                    <label>Titre de l'annonce</label>
                    <input type="text" name="titre" placeholder="Ex: Renault Clio 4 - Full Options - Très bon état" required>
                </div>

                <div class="form-group reveal">
                    <label>Marque / Modèle</label>
                    <select name="id_modele" required>
                        <option value="" disabled selected>Sélectionner</option>
                        <?php foreach($modeles as $m): ?>
                            <option value="<?= $m['id_modele'] ?>">
                                <?= htmlspecialchars($m['nom_marque']) ?> <?= htmlspecialchars($m['nom_modele']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group reveal">
                    <label>Prix (DH)</label>
                    <input type="number" name="prix" placeholder="Ex: 95000" min="1" required>
                </div>

                <div class="form-group reveal">
                    <label>Année</label>
                    <input type="number" name="annee" min="1970" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>" required>
                </div>

                <div class="form-group reveal">
                    <label>Kilométrage (KM)</label>
                    <input type="number" name="kilometrage" min="0" placeholder="0" required>
                </div>

                <div class="form-group reveal">
                    <label>Carburant</label>
                    <select name="carburant" required>
                        <option value="Diesel">Diesel</option>
                        <option value="Essence">Essence</option>
                        <option value="Hybride">Hybride</option>
                        <option value="Electrique">Électrique</option>
                    </select>
                </div>

                <div class="form-group reveal">
                    <label>Boîte de vitesse</label>
                    <select name="boite_vitesse" required>
                        <option value="Manuelle">Manuelle</option>
                        <option value="Automatique">Automatique</option>
                    </select>
                </div>

                <div class="form-group reveal">
                    <label>Puissance fiscale (CV)</label>
                    <input type="number" name="puissance_fiscale" min="1" max="99" placeholder="Ex: 7">
                </div>

                <div class="form-group full reveal">
                    <label>Photos du véhicule</label>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple
                           style="background:rgba(255,255,255,0.05);border:1px dashed rgba(194,163,125,0.4);color:#94a3b8;padding:12px;width:100%;cursor:pointer;">
                </div>

                <div class="form-group full reveal">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="État général, options, entretien, historique..."></textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit reveal">
                Déposer l'annonce <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // Animation au chargement (Scroll Reveal simple)
    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        
        reveals.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add('active');
            }, index * 100);
        });

        // Effet de mouvement sur les inputs
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.style.transform = 'translateX(10px)';
            });
            input.addEventListener('blur', () => {
                input.parentElement.style.transform = 'translateX(0)';
            });
        });
    });
</script>

</body>
</html>