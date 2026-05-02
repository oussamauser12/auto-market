<?php
session_start();
require '../config/db.php';

// Sécurité : Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id']; // Cast sécurisé
    $action = $_GET['action'];

    if ($action === 'accepter') {
        $stmt = $pdo->prepare("UPDATE annonces SET statut = 'valide' WHERE id_annonce = ?");
    } elseif ($action === 'refuser') {
        $stmt = $pdo->prepare("UPDATE annonces SET statut = 'refuse' WHERE id_annonce = ?");
    } else {
        header('Location: index.php');
        exit();
    }

    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
