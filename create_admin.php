<?php
require 'config/db.php'; // Votre fichier de connexion

$nom = "Oussama"; //
$prenom = "Boujadi"; //
$email = "admine1@auto.ma"; //
$password_clair = "1111";

// On hache le mot de passe pour la sécurité
$password_hash = password_hash($password_clair, PASSWORD_DEFAULT);
$role = 1; // 1 = Admin, 0 = Client

try {
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prenom, $email, $password_hash, $role]);
    
    echo "L'administrateur a été créé avec succès !";
} catch (PDOException $e) {
    echo "Erreur lors de la création : " . $e->getMessage();
}
?>