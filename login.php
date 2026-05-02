<?php
session_start();
require 'config/db.php';

// Si déjà connecté, rediriger directement
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 1) {
        header('Location: espace-admin/dashboard.php');
    } else {
        header('Location: espace-client/index.php');
    }
    exit();
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            // Stocker les infos de session
            $_SESSION['user_id'] = (int)$user['id_user'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['role']    = (int)$user['role']; // 0 = client, 1 = admin

            // Redirection selon le rôle
            if ($_SESSION['role'] === 1) {
                header('Location: espace-admin/dashboard.php');
            } else {
                header('Location: espace-client/index.php');
            }
            exit();

        } else {
            $erreur = "Identifiants incorrects. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Auto-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --cupra-gold: #c2a37d;
            --glass-border: rgba(255, 255, 255, 0.1);
            --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://media.gqmagazine.fr/photos/5bf402f6923dee70dbcfae9e/16:9/w_2560%2Cc_limit/526140.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .login-card {
            width: 90%;
            max-width: 420px;
            background: rgba(15, 15, 15, 0.65);
            backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 4px;
            padding: 50px 40px;
            box-shadow: 0 50px 100px rgba(0,0,0,0.5);
            position: relative;
            animation: fadeUp 0.6s ease-out;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Ligne cuivrée en haut */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: var(--cupra-gold);
        }

        .logo {
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: white;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .logo span { color: var(--cupra-gold); }

        .subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 35px;
        }

        /* Message d'erreur */
        .erreur-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 12px 15px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 2px;
        }
        .erreur-msg i { margin-right: 6px; }

        /* Formulaire */
        .form-group { margin-bottom: 22px; }

        .form-group label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--cupra-gold);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 0.9rem;
            pointer-events: none;
            transition: var(--transition);
        }

        .input-wrapper input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: none;
            border-bottom: 1px solid var(--glass-border);
            color: white;
            padding: 13px 15px 13px 42px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .input-wrapper input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-bottom-color: var(--cupra-gold);
            outline: none;
        }

        .input-wrapper input::placeholder { color: #475569; }

        /* Bouton */
        .btn-submit {
            width: 100%;
            background: var(--cupra-gold);
            color: #000;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
            padding: 16px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            transition: var(--transition);
            font-family: inherit;
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            opacity: 0.88;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(194, 163, 125, 0.25);
        }

        .footer-text {
            text-align: center;
            margin-top: 28px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .footer-text a {
            color: var(--cupra-gold);
            text-decoration: none;
            font-weight: 700;
        }

        .footer-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">

    <div class="logo">AUTO<span>.</span>MARKET</div>
    <p class="subtitle">Expérimentez la performance.</p>

    <?php if (!empty($erreur)): ?>
        <div class="erreur-msg">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erreur) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" id="loginForm">

        <div class="form-group">
            <label for="email">Adresse Email</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email"
                       placeholder="nom@exemple.com"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-arrow-right-to-bracket"></i> Se connecter
        </button>

    </form>

    <div class="footer-text">
        Nouveau ici ? <a href="register.php">Créer un compte</a>
    </div>

</div>

<script>
    // Icône change de couleur au focus
    document.querySelectorAll('.input-wrapper input').forEach(input => {
        const icon = input.previousElementSibling;
        input.addEventListener('focus', () => icon.style.color = 'var(--cupra-gold)');
        input.addEventListener('blur',  () => icon.style.color = '#64748b');
    });

    // Feedback visuel lors de la soumission
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ACCÈS EN COURS...';
        btn.style.opacity = '0.7';
        btn.disabled = true;
    });
</script>

</body>
</html>
