<?php
require 'config/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $ville = $_POST['ville'];
    $telephone = $_POST['telephone'];
    // Hachage du mot de passe pour la sécurité
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, password, telephone, ville, role) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$nom, $prenom, $email, $password, $telephone, $ville]);
        $message = "<p style='color:green;'>Inscription réussie ! <a href='login.php'>Connectez-vous ici</a></p>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Code d'erreur pour email en double
            $message = "<p style='color:red;'>Cet email est déjà utilisé.</p>";
        } else {
            $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Auto-Market</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1a237e; text-align: center;">Créer un compte</h2>
        <?= $message ?>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom" required style="width:100%; margin-bottom:10px; padding:8px;">
            <input type="text" name="prenom" placeholder="Prénom" required style="width:100%; margin-bottom:10px; padding:8px;">
            <input type="email" name="email" placeholder="Email" required style="width:100%; margin-bottom:10px; padding:8px;">
            <input type="text" name="telephone" placeholder="Téléphone" style="width:100%; margin-bottom:10px; padding:8px;">
            <input type="text" name="ville" placeholder="Ville (ex: Khouribga)" style="width:100%; margin-bottom:10px; padding:8px;">
            <input type="password" name="password" placeholder="Mot de passe" required style="width:100%; margin-bottom:15px; padding:8px;">
            <button type="submit" style="width:100%; background:#1a237e; color:white; border:none; padding:10px; border-radius:4px; cursor:pointer;">S'inscrire</button>
        </form>
        <p style="text-align:center; margin-top:15px;">Déjà inscrit ? <a href="login.php">Connexion</a></p>
    </div>
</body>
</html>