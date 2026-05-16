<?php
session_start();

// Si déjà connecté, rediriger directement
if (isset($_SESSION["utilisateur"])) {
    header("Location: dashboard_" . $_SESSION["role"] . ".php");
    exit();
}

require "db.php";

$errlogin    = "";
$errpassword = "";
$errgeneral  = "";
$success     = "";

// ─────────────────────────────────────────────
// INSCRIPTION
// ─────────────────────────────────────────────
if (isset($_POST["inscrire"])) {
    $nom_complet = trim($_POST["nom_complet"]);
    $login       = trim($_POST["login"]);
    $password    = $_POST["password"];
    $role        = $_POST["role"]; // client ou employe

    // Validation simple
    if (empty($login))       $errlogin    = "Le login est obligatoire !";
    if (empty($password))    $errpassword = "Le mot de passe est obligatoire !";
    if (strlen($password) < 4) $errpassword = "Mot de passe trop court (min 4 caractères) !";

    if (empty($errlogin) && empty($errpassword)) {
        // Vérifier si le login existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE login = :login");
        $stmt->execute([":login" => $login]);

        if ($stmt->rowCount() > 0) {
            $errlogin = "Ce login est déjà pris !";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (nom_complet, login, password, role)
                VALUES (:nom_complet, :login, :password, :role)
            ");
            $stmt->execute([
                ":nom_complet" => $nom_complet,
                ":login"       => $login,
                ":password"    => $hash,
                ":role"        => $role
            ]);
            $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        }
    }
}

// ─────────────────────────────────────────────
// CONNEXION
// ─────────────────────────────────────────────
if (isset($_POST["connecter"])) {
    $login    = trim($_POST["login"]);
    $password = $_POST["password"];

    if (empty($login))    $errlogin    = "Le login est obligatoire !";
    if (empty($password)) $errpassword = "Le mot de passe est obligatoire !";

    if (empty($errlogin) && empty($errpassword)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE login = :login");
        $stmt->execute([":login" => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Connexion réussie → stocker en session
            $_SESSION["utilisateur"]  = $user["login"];
            $_SESSION["nom_complet"]  = $user["nom_complet"];
            $_SESSION["role"]         = $user["role"];
            $_SESSION["user_id"]      = $user["id"];

            // Redirection selon le rôle
            header("Location: dashboard_" . $user["role"] . ".php");
            exit();
        } else {
            $errgeneral = "Login ou mot de passe incorrect !";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Stox</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body {
            background-color: #1a1a1a;
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
        }
        .wrapper { width: 420px; }
        .tabs {
            display: flex; margin-bottom: 0;
        }
        .tab-btn {
            flex: 1; padding: 13px; background: #1e1e1e; border: 1px solid #333;
            color: #888; font-size: 14px; font-weight: bold; cursor: pointer;
            letter-spacing: 1px; border-bottom: none; transition: all 0.3s;
        }
        .tab-btn.active { background: #242424; color: #c9a84c; border-top: 2px solid #c9a84c; }
        .tab-btn:first-child { border-radius: 10px 0 0 0; }
        .tab-btn:last-child  { border-radius: 0 10px 0 0; }

        .box {
            background: #242424; padding: 35px 40px;
            border-radius: 0 0 14px 14px;
            border: 1px solid #333; border-top: none;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
            display: none;
        }
        .box.active { display: block; }

        .logo { text-align:center; font-size:38px; margin-bottom:6px; }
        h2 { text-align:center; color:#c9a84c; margin-bottom:5px; font-size:22px; letter-spacing:2px; }
        .sous-titre { text-align:center; color:#666; font-size:12px; margin-bottom:22px; }
        hr { border:none; border-top:1px solid #c9a84c44; margin-bottom:22px; }

        label { color:#aaa; font-size:12px; font-weight:600; letter-spacing:0.5px; }
        input[type="text"], input[type="password"], select {
            width:100%; padding:11px 14px; margin:6px 0 4px;
            background:#1a1a1a; border:1px solid #444; border-radius:8px;
            font-size:14px; color:#f0f0f0; transition:border 0.3s;
        }
        input:focus, select:focus {
            border-color:#c9a84c; outline:none;
            box-shadow:0 0 0 3px rgba(201,168,76,0.12);
        }
        select option { background:#1a1a1a; }
        .erreur  { color:#e74c3c; font-size:12px; margin-bottom:4px; }
        .success { color:#58d68d; font-size:13px; margin-bottom:10px; text-align:center; }
        .btn-submit {
            width:100%; padding:12px; margin-top:15px;
            background:linear-gradient(135deg,#c9a84c,#a07830);
            color:#1a1a1a; border:none; border-radius:8px;
            font-size:15px; font-weight:bold; cursor:pointer; letter-spacing:1px;
            transition:opacity 0.3s;
        }
        .btn-submit:hover { opacity:0.85; }
        .field-group { margin-bottom:8px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="logo">🏪</div>

    <!-- ONGLETS -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('connexion', this)">CONNEXION</button>
        <button class="tab-btn" onclick="switchTab('inscription', this)">INSCRIPTION</button>
    </div>

    <!-- ── ONGLET CONNEXION ── -->
    <div class="box active" id="tab-connexion">
        <h2>STOX</h2>
        <p class="sous-titre">Gestion de Magasin</p>
        <hr>

        <?php if ($errlogin):    ?><p class="erreur"><?= $errlogin ?></p><?php endif; ?>
        <?php if ($errpassword): ?><p class="erreur"><?= $errpassword ?></p><?php endif; ?>
        <?php if ($errgeneral):  ?><p class="erreur"><?= $errgeneral ?></p><?php endif; ?>
        <?php if ($success):     ?><p class="success">✅ <?= $success ?></p><?php endif; ?>

        <form method="POST">
            <div class="field-group">
                <label>LOGIN</label>
                <input type="text" name="login" placeholder="Entrez votre login">
            </div>
            <div class="field-group">
                <label>MOT DE PASSE</label>
                <input type="password" name="password" placeholder="Entrez votre mot de passe">
            </div>
            <button class="btn-submit" type="submit" name="connecter">SE CONNECTER</button>
        </form>
    </div>

    <!-- ── ONGLET INSCRIPTION ── -->
    <div class="box" id="tab-inscription">
        <h2>NOUVEAU COMPTE</h2>
        <p class="sous-titre">Créer votre accès STOX</p>
        <hr>

        <form method="POST">
            <div class="field-group">
                <label>NOM COMPLET</label>
                <input type="text" name="nom_complet" placeholder="Ex : Ahmed Benali">
            </div>
            <div class="field-group">
                <label>LOGIN</label>
                <input type="text" name="login" placeholder="Ex : ahmed123">
            </div>
            <div class="field-group">
                <label>MOT DE PASSE</label>
                <input type="password" name="password" placeholder="Min 4 caractères">
            </div>
            <div class="field-group">
                <label>RÔLE</label>
                <select name="role">
                    <option value="client">👤 Client</option>
                    <option value="employe">🛠️ Employé</option>
                </select>
            </div>
            <button class="btn-submit" type="submit" name="inscrire">CRÉER LE COMPTE</button>
        </form>
    </div>
</div>

<script>
function switchTab(name, btn) {
    // Masquer toutes les boîtes
    document.querySelectorAll('.box').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    // Afficher la bonne
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>