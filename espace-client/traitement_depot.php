<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Récupération et nettoyage des données
    $id_user = $_SESSION['user_id'];
    $id_modele = $_POST['id_modele'];
    $titre = htmlspecialchars($_POST['titre']);
    $prix = $_POST['prix'];
    if ($prix <= 0) {
    // Rediriger avec un message d'erreur
    header("Location: deposer.php?error=prix_invalide");
    exit();
}
    $annee = $_POST['annee'];
    $kilometrage = $_POST['kilometrage'];
    $carburant = $_POST['carburant'];
    $boite_vitesse = $_POST['boite_vitesse'];
    $description = htmlspecialchars($_POST['description']);
    $date_publication = date('Y-m-d H:i:s');

    try {
        // Commencer une transaction pour garantir que tout est enregistré ou rien du tout
        $pdo->beginTransaction();

        // 2. Création du véhicule dans la table 'vehicules'
        $stmtVehicule = $pdo->prepare("
            INSERT INTO vehicules (id_modele, annee, kilometrage, carburant, boite_vitesse) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtVehicule->execute([$id_modele, $annee, $kilometrage, $carburant, $boite_vitesse]);
        
        // Récupérer l'ID du véhicule qu'on vient de créer
        $id_vehicule = $pdo->lastInsertId();

        // 3. Création de l'annonce dans la table 'annonces'
        $stmtAnnonce = $pdo->prepare("
            INSERT INTO annonces (id_user, id_vehicule, titre, description, prix, date_publication, statut) 
            VALUES (?, ?, ?, ?, ?, ?, 'en_attente')
        ");
        // Note : J'ai mis 'valide' pour que vous puissiez voir l'annonce immédiatement sur l'index.
        $stmtAnnonce->execute([$id_user, $id_vehicule, $titre, $description, $prix, $date_publication]);

        $pdo->commit();

        // 4. Redirection vers l'accueil avec un message de succès
        header('Location: index.php?success=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de la publication : " . $e->getMessage());
    }
} else {
    header('Location: deposer.php');
    exit();
}