<?php
session_start();
require 'config/db.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Vérification en base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérification du mot de passe (Comparaison directe selon ton code actuel)
    if ($user && $password === $user['password']) {
        
        // --- INITIALISATION DE LA SESSION ---
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = (int)$user['role']; // 1 pour Admin, 0 pour Client

        // --- LOGIQUE DE REDIRECTION SELON LE RÔLE ---
        if ($_SESSION['role'] === 1) {
            // L'utilisateur est ADMIN -> Direction le Dashboard
            header('Location: espace-admin/dashboard.php');
            exit();
        } else {
            // L'utilisateur est CLIENT -> Direction son espace personnel
            header('Location: espace-client/index.php');
            exit();
        }

    } else {
        $erreur = "Identifiants incorrects. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Auto-Market</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-muted: #94a3b8;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617; 
            color: white;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            text-align: center;
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -1px;
            color: white;
            margin-bottom: 10px;
        }

        .logo span { color: var(--accent); }

        p.subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: var(--text-muted);
        }

        input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            padding: 14px 15px 14px 45px;
            border-radius: 14px;
            color: white;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
            box-sizing: border-box;
        }

        input:focus {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-login {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            transition: var(--transition);
        }

        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .erreur-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">AUTO<span>.</span>MARKET</div>
    <p class="subtitle">Ravi de vous revoir ! Connectez-vous.</p>

    <?php if(!empty($erreur)): ?>
        <div class="erreur-msg">
            <i class="fas fa-exclamation-circle"></i> <?= $erreur ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Adresse Email</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="nom@exemple.com" required>
            </div>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-login">Se connecter</button>
    </form>

    <div class="footer-text">
        Pas encore de compte ? <a href="register.php">Créer un compte</a>
    </div>
</div>

</body>
</html>