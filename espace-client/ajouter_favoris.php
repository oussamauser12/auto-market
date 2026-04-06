<?php
session_start();
require '../config/db.php';

// 1. Vérification de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['user_id'];
$id_annonce = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_annonce > 0) {
    // 2. Logique Toggle (Ajout/Suppression)
    $stmt = $pdo->prepare("SELECT * FROM favoris WHERE id_user = ? AND id_annonce = ?");
    $stmt->execute([$id_user, $id_annonce]);
    
    if ($stmt->rowCount() > 0) {
        $delete = $pdo->prepare("DELETE FROM favoris WHERE id_user = ? AND id_annonce = ?");
        $delete->execute([$id_user, $id_annonce]);
    } else {
        $insert = $pdo->prepare("INSERT INTO favoris (id_user, id_annonce) VALUES (?, ?)");
        $insert->execute([$id_user, $id_annonce]);
    }
}

// 3. REDIRECTION INTELLIGENTE
// Si on connaît la page d'où vient l'utilisateur, on le renvoie là-bas
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    // Sinon, par défaut, on retourne à l'index
    header('Location: index.php');
}
exit();
?>