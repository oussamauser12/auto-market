<?php
session_start(); // On démarre la session pour pouvoir la détruire

// On vide toutes les variables de session
$_SESSION = array();

// On détruit le cookie de session si il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// On détruit la session sur le serveur
session_destroy();

// Redirection vers la page de connexion
header("Location: login.php");
exit();
?>