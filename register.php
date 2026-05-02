<?php
session_start();
require 'config/db.php';

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: espace-client/index.php');
    exit();
}

$message = "";
$message_type = "";
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $ville     = trim($_POST['ville'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    $old = compact('nom', 'prenom', 'email', 'telephone', 'ville');

    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $message_type = "error";
    } elseif ($password !== $confirm) {
        $message = "Les mots de passe ne correspondent pas.";
        $message_type = "error";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO utilisateurs (nom, prenom, email, password, telephone, ville, role)
                 VALUES (?, ?, ?, ?, ?, ?, 0)"
            );
            $stmt->execute([$nom, $prenom, $email, $hashed, $telephone, $ville]);
            $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $message_type = "success";
            $old = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Cette adresse email est déjà utilisée.";
            } else {
                $message = "Une erreur est survenue. Veuillez réessayer.";
            }
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Auto-Market</title>
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
            background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)),
                        url('https://media.gqmagazine.fr/photos/5bf402f6923dee70dbcfae9e/16:9/w_2560%2Cc_limit/526140.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px 20px;
        }
        .register-card {
            width: 90%;
            max-width: 500px;
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
        .register-card::before {
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
            font-size: 1.8rem;
            margin-bottom: 6px;
        }
        .logo span { color: var(--cupra-gold); }
        .subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }
        .msg {
            padding: 12px 15px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 22px;
            border-radius: 2px;
        }
        .msg.error  { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.25); }
        .msg.success { background: rgba(74, 222, 128, 0.1); color: #86efac; border: 1px solid rgba(74, 222, 128, 0.25); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 0.70rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--cupra-gold);
            margin-bottom: 8px;
            font-weight: 700;
        }
        .form-group label .req { color: #ef4444; margin-left: 2px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 0.85rem;
            pointer-events: none;
            transition: var(--transition);
        }
        .input-wrapper input, .input-wrapper select {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: none;
            border-bottom: 1px solid var(--glass-border);
            color: white;
            padding: 12px 15px 12px 40px;
            font-family: inherit;
            font-size: 0.92rem;
            transition: var(--transition);
        }
        .input-wrapper select { cursor: pointer; }
        .input-wrapper select option { background: #111; color: white; }
        .input-wrapper input:focus, .input-wrapper select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-bottom-color: var(--cupra-gold);
            outline: none;
        }
        .input-wrapper input::placeholder { color: #475569; }
        .btn-submit {
            width: 100%;
            background: var(--cupra-gold);
            color: #000;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.88rem;
            padding: 15px;
            border: none;
            cursor: pointer;
            margin-top: 8px;
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
            margin-top: 24px;
            font-size: 0.85rem;
            color: #64748b;
        }
        .footer-text a { color: var(--cupra-gold); text-decoration: none; font-weight: 700; }
        .footer-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="register-card">
    <div class="logo">AUTO<span>.</span>MARKET</div>
    <p class="subtitle">Créer votre compte</p>

    <?php if (!empty($message)): ?>
        <div class="msg <?= $message_type ?>">
            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
            <?php if ($message_type === 'success'): ?>
                &nbsp;<a href="login.php" style="color:#86efac;font-weight:700;">Se connecter →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="form-row">
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nom" placeholder="Dupont" required
                           value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Prénom <span class="req">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="prenom" placeholder="Jean" required
                           value="<?= htmlspecialchars($old['prenom'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Adresse Email <span class="req">*</span></label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="nom@exemple.com" required
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Téléphone</label>
                <div class="input-wrapper">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="telephone" placeholder="06 00 00 00 00"
                           value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Ville</label>
                <div class="input-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                    <select name="ville">
                        <option value="">-- Choisir --</option>
                        <?php
                        $villes = ["Agadir","Béni Mellal","Casablanca","El Jadida","Fès","Kénitra","Khouribga","Ksar El Kebir","Larache","Marrakech","Meknès","Mohammédia","Nador","Oujda","Rabat","Safi","Settat","Tanger","Taza","Tétouan"];
                        foreach ($villes as $v) {
                            $sel = (($old['ville'] ?? '') === $v) ? 'selected' : '';
                            echo "<option value=\"$v\" $sel>$v</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Mot de passe <span class="req">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Min. 6 caractères" required minlength="6">
                </div>
            </div>
            <div class="form-group">
                <label>Confirmer <span class="req">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Répéter" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-user-plus"></i> Créer mon compte
        </button>
    </form>

    <div class="footer-text">
        Déjà inscrit ? <a href="login.php">Se connecter</a>
    </div>
</div>

<script>
    document.querySelectorAll('.input-wrapper input, .input-wrapper select').forEach(el => {
        const icon = el.previousElementSibling;
        el.addEventListener('focus',  () => icon.style.color = 'var(--cupra-gold)');
        el.addEventListener('blur',   () => icon.style.color = '#64748b');
    });

    document.getElementById('registerForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> CRÉATION EN COURS...';
        btn.style.opacity = '0.7';
        btn.disabled = true;
    });
</script>
</body>
</html>
