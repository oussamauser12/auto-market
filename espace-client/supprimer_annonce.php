<?php
session_start();
require '../config/db.php';

// 1. Vérification de sécurité : l'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 2. Vérification de l'ID de l'annonce
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_annonce = $_GET['id'];
    $id_user = $_SESSION['user_id'];

    try {
        // On vérifie d'abord que l'annonce appartient bien à l'utilisateur connecté
        // (Sécurité pour éviter qu'un utilisateur supprime l'annonce d'un autre via l'URL)
        $check = $pdo->prepare("SELECT id_vehicule FROM annonces WHERE id_annonce = ? AND id_user = ?");
        $check->execute([$id_annonce, $id_user]);
        $annonce = $check->fetch();

        if ($annonce) {
            $id_vehicule = $annonce['id_vehicule'];

            $pdo->beginTransaction();

            // Supprimer l'annonce
            $delAnnonce = $pdo->prepare("DELETE FROM annonces WHERE id_annonce = ?");
            $delAnnonce->execute([$id_annonce]);

            // Supprimer le véhicule associé
            $delVehicule = $pdo->prepare("DELETE FROM vehicules WHERE id_vehicule = ?");
            $delVehicule->execute([$id_vehicule]);

            $pdo->commit();
            
            // Redirection avec succès
            header('Location: profil.php?deleted=1');
            exit();
        } else {
            // L'annonce n'existe pas ou n'appartient pas à l'utilisateur
            header('Location: profil.php?error=not_allowed');
            exit();
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    header('Location: profil.php');
    exit();
}