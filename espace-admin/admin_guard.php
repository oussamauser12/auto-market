<?php
session_start();

// Vérifie si l'utilisateur est connecté ET s'il est admin (rôle 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    // Redirige vers la page de login s'il n'est pas admin
    header('Location: ../login.php'); 
    exit();
}
?>