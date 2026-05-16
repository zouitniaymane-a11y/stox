<?php
session_start();
if (!isset($_SESSION["utilisateur"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php"); 
}
require "db.php";

$message = "";

// Supprimer un utilisateur
if (isset($_GET["supprimer_user"])) {
    $id = (int)$_GET["supprimer_user"];
    // Sécurité : on ne peut pas supprimer son propre compte
    if ($id !== (int)$_SESSION["user_id"]) {
        $conn->prepare("DELETE FROM users WHERE id = :id")->execute([":id"=>$id]);
        $message = "🗑️ Utilisateur supprimé !";
    } else {
        $message = "⚠️ Vous ne pouvez pas supprimer votre propre compte !";
    }
}

// Changer le rôle d'un utilisateur
if (isset($_POST["changer_role"])) {
    $id      = (int)$_POST["user_id"];
    $nouveau = $_POST["nouveau_role"];
    $conn->prepare("UPDATE users SET role = :role WHERE id = :id")->execute([":role"=>$nouveau,":id"=>$id]);
    $message = "✅ Rôle mis à jour !";
}

// Récupérer utilisateurs séparés par rôle
$employes = $conn->query("SELECT * FROM users WHERE role = 'employe' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$clients  = $conn->query("SELECT * FROM users WHERE role = 'client'  ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$admins   = $conn->query("SELECT * FROM users WHERE role = 'admin'   ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Stats globales
$totalProduits = $conn->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$totalStock    = $conn->query("SELECT SUM(stock) FROM produits")->fetchColumn();
$totalValeur   = $conn->query("SELECT SUM(prix * stock) FROM produits")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - STOX</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; }
        .navbar { background:#111; padding:16px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #c9a84c; }
        .navbar h1 { font-size:20px; color:#c9a84c; letter-spacing:2px; }
        .badge-admin { background:#2e1a3a; color:#bb8fce; border:1px solid #8e44ad; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; }
        .navbar a { color:#e74c3c; text-decoration:none; font-weight:bold; margin-left:15px; font-size:13px; }
        .navbar a.emp-link { color:#c9a84c; }
        .container { padding:30px; }
        .message { background:#1a2e1a; border:1px solid #27ae60; color:#58d68d; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:14px; }

        /* Stats */
        .stats { display:flex; gap:20px; margin-bottom:35px; }
        .card-stat { background:#242424; border-radius:10px; padding:22px 28px; flex:1; border:1px solid #333; border-left:4px solid #c9a84c; }
        .card-stat h3 { font-size:11px; color:#888; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:10px; }
        .card-stat p  { font-size:28px; font-weight:bold; color:#c9a84c; }

        /* Section */
        .section { margin-bottom:40px; }
        .section h2 { color:#c9a84c; margin-bottom:15px; font-size:18px; letter-spacing:1px; padding-bottom:10px; border-bottom:1px solid #2e2e2e; }

        table { width:100%; border-collapse:collapse; background:#242424; border-radius:10px; overflow:hidden; border:1px solid #333; margin-bottom:10px; }
        thead { background:#111; border-bottom:2px solid #c9a84c; }
        thead th { padding:12px 18px; text-align:left; font-size:11px; color:#c9a84c; letter-spacing:1.5px; text-transform:uppercase; }
        tbody tr { border-bottom:1px solid #2e2e2e; transition:background 0.2s; }
        tbody tr:hover { background:#2c2c2c; }
        tbody td { padding:12px 18px; font-size:13px; color:#ccc; }
        .badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
        .badge-employe { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; }
        .badge-client  { background:#1a2e1a; color:#58d68d; border:1px solid #27ae60; }
        .badge-admin2  { background:#2e1a3a; color:#bb8fce; border:1px solid #8e44ad; }
        .btn-sup { background:#3a1a1a; color:#e74c3c; border:1px solid #c0392b; padding:4px 12px; border-radius:6px; font-size:11px; text-decoration:none; font-weight:bold; }
        .btn-sup:hover { background:#e74c3c; color:#fff; }
        select.role-sel { background:#1a1a1a; color:#f0f0f0; border:1px solid #444; border-radius:6px; padding:4px 8px; font-size:12px; }
        .btn-save { background:#c9a84c; color:#1a1a1a; border:none; border-radius:6px; padding:4px 12px; font-size:12px; font-weight:bold; cursor:pointer; }
        .footer { text-align:center; padding:20px; color:#444; font-size:12px; margin-top:40px; border-top:1px solid #2e2e2e; }
        .footer span { color:#c9a84c; }
        .empty { color:#555; font-style:italic; padding:15px 18px; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏪 STOX — ADMINISTRATION</h1>
    <div style="display:flex;align-items:center;gap:15px;">
        <span class="badge-admin">👑 ADMIN</span>
        <span style="color:#888;font-size:13px;">Bonjour, <b style="color:#c9a84c"><?= $_SESSION["nom_complet"] ?></b></span>
        <a href="dashboard_employe.php" class="emp-link">📦 PRODUITS</a>
        <a href="logout.php">🔴 DÉCONNEXION</a>
    </div>
</div>

<div class="container">
    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

    <!-- STATS -->
    <div class="stats">
        <div class="card-stat"><h3>Total Produits</h3><p><?= $totalProduits ?></p></div>
        <div class="card-stat"><h3>Total Stock</h3><p><?= $totalStock ?></p></div>
        <div class="card-stat"><h3>Valeur Totale</h3><p><?= number_format($totalValeur, 0, ',', ' ') ?> DH</p></div>
        <div class="card-stat"><h3>Utilisateurs</h3><p><?= count($employes)+count($clients)+count($admins) ?></p></div>
    </div>

    <!-- TABLEAU AIDEZ-MOI : fonction réutilisable -->
    <?php
    // Fonction helper pour afficher un tableau d'utilisateurs
    function afficherTableauUsers($users, $badge_class, $conn_ref, $current_id) { ?>
        <?php if (empty($users)): ?>
            <p class="empty">Aucun utilisateur dans cette catégorie.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Nom Complet</th><th>Login</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u["id"] ?></td>
                    <td><?= htmlspecialchars($u["nom_complet"]) ?></td>
                    <td><?= htmlspecialchars($u["login"]) ?></td>
                    <td><span class="badge <?= $badge_class ?>"><?= strtoupper($u["role"]) ?></span></td>
                    <td><?= date("d/m/Y", strtotime($u["created_at"])) ?></td>
                    <td style="display:flex;gap:8px;align-items:center;">
                        <!-- Changer le rôle -->
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="user_id" value="<?= $u["id"] ?>">
                            <select name="nouveau_role" class="role-sel">
                                <option value="client"  <?= $u["role"]=="client"  ? "selected":""?>>Client</option>
                                <option value="employe" <?= $u["role"]=="employe" ? "selected":""?>>Employé</option>
                                <option value="admin"   <?= $u["role"]=="admin"   ? "selected":""?>>Admin</option>
                            </select>
                            <button type="submit" name="changer_role" class="btn-save">✔</button>
                        </form>
                        <!-- Supprimer -->
                        <?php if ($u["id"] != $current_id): ?>
                        <a href="?supprimer_user=<?= $u["id"] ?>" class="btn-sup"
                           onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif;
    }
    ?>

    <!-- SECTION ADMINS -->
    <div class="section">
        <h2>👑 ADMINISTRATEURS (<?= count($admins) ?>)</h2>
        <?php afficherTableauUsers($admins, "badge-admin2", $conn, $_SESSION["user_id"]); ?>
    </div>

    <!-- SECTION EMPLOYÉS -->
    <div class="section">
        <h2>🛠️ EMPLOYÉS (<?= count($employes) ?>)</h2>
        <?php afficherTableauUsers($employes, "badge-employe", $conn, $_SESSION["user_id"]); ?>
    </div>

    <!-- SECTION CLIENTS -->
    <div class="section">
        <h2>👤 CLIENTS (<?= count($clients) ?>)</h2>
        <?php afficherTableauUsers($clients, "badge-client", $conn, $_SESSION["user_id"]); ?>
    </div>
</div>

<div class="footer">© 2025 <span>STOX</span> — Tous droits réservés</div>
</body>
</html>