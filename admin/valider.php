<?php
session_start();
require '../config/db.php';

if(isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if($action == 'accepter') {
        $stmt = $pdo->prepare("UPDATE annonces SET statut = 'valide' WHERE id_annonce = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE annonces SET statut = 'refuse' WHERE id_annonce = ?");
    }
    
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();