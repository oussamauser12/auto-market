<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Non connecté → page de connexion
    header('Location: login.php');
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 1) {
    // Admin → dashboard admin
    header('Location: espace-admin/dashboard.php');
} else {
    // Client → espace client
    header('Location: espace-client/index.php');
}
exit();
?>
