<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: deposer.php');
    exit();
}

// Récupération et validation des données
$id_user       = (int)$_SESSION['user_id'];
$id_modele     = (int)($_POST['id_modele'] ?? 0);
$titre         = trim($_POST['titre'] ?? '');
$prix          = (float)($_POST['prix'] ?? 0);
$annee         = (int)($_POST['annee'] ?? 0);
$kilometrage   = (int)($_POST['kilometrage'] ?? 0);
$carburant     = $_POST['carburant'] ?? '';
$boite_vitesse = $_POST['boite_vitesse'] ?? '';
$puissance     = !empty($_POST['puissance_fiscale']) ? (int)$_POST['puissance_fiscale'] : null;
$description   = trim($_POST['description'] ?? '');

// Validations
$carburants_ok    = ['Diesel', 'Essence', 'Hybride', 'Electrique'];
$boites_ok        = ['Manuelle', 'Automatique'];
$annee_courante   = (int)date('Y');

if ($id_modele <= 0 || empty($titre) || $prix <= 0 ||
    $annee < 1970 || $annee > $annee_courante + 1 ||
    $kilometrage < 0 ||
    !in_array($carburant, $carburants_ok) ||
    !in_array($boite_vitesse, $boites_ok)) {
    header('Location: deposer.php?error=donnees_invalides');
    exit();
}

try {
    $pdo->beginTransaction();

    // Insérer le véhicule (avec puissance_fiscale)
    $stmtV = $pdo->prepare("
        INSERT INTO vehicules (id_modele, annee, kilometrage, carburant, boite_vitesse, puissance_fiscale)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtV->execute([$id_modele, $annee, $kilometrage, $carburant, $boite_vitesse, $puissance]);
    $id_vehicule = $pdo->lastInsertId();

    // Insérer l'annonce (statut = en_attente, en attente de validation admin)
    $stmtA = $pdo->prepare("
        INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, statut)
        VALUES (?, ?, ?, ?, ?, 'en_attente')
    ");
    $stmtA->execute([$id_user, $id_vehicule, htmlspecialchars($titre), htmlspecialchars($description), $prix]);

    // Gestion des photos uploadées
    if (!empty($_FILES['photos']['name'][0])) {
        $id_annonce = $pdo->lastInsertId();
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $mime = mime_content_type($tmp);
                if (in_array($mime, $allowed_types)) {
                    $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
                    $filename = uniqid('photo_', true) . '.' . strtolower($ext);
                    if (move_uploaded_file($tmp, $upload_dir . $filename)) {
                        $pdo->prepare("INSERT INTO photos (id_annonce, chemin_url) VALUES (?, ?)")
                            ->execute([$id_annonce, 'uploads/' . $filename]);
                    }
                }
            }
        }
    }

    $pdo->commit();
    header('Location: index.php?success=1');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: deposer.php?error=serveur');
    exit();
}
