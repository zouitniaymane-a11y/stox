<?php
session_start();
if (!isset($_SESSION["utilisateur"]) || $_SESSION["role"] !== "client") {//verfication que c'est un client
    header("Location: login.php"); 
}
require "db.php";

// Récupérer les produits depuis la BDD
$produits = $conn->query("SELECT * FROM produits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue - STOX</title>
    <!-- Même style que votre dashboard original -->
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; }
        .navbar { background:#111; padding:16px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #c9a84c; }
        .navbar h1 { font-size:20px; color:#c9a84c; letter-spacing:2px; }
        .badge-role { background:#1a3a1a; color:#58d68d; border:1px solid #27ae60; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; }
        .navbar a { color:#e74c3c; text-decoration:none; font-weight:bold; margin-left:20px; font-size:13px; }
        .container { padding:30px; }
        .info-box { background:#242424; border:1px solid #333; border-left:4px solid #58d68d; border-radius:10px; padding:18px 24px; margin-bottom:28px; color:#aaa; font-size:14px; }
        .info-box b { color:#58d68d; }
        table { width:100%; border-collapse:collapse; background:#242424; border-radius:10px; overflow:hidden; border:1px solid #333; }
        thead { background:#111; border-bottom:2px solid #c9a84c; }
        thead th { padding:14px 18px; text-align:left; font-size:12px; color:#c9a84c; letter-spacing:1.5px; text-transform:uppercase; }
        tbody tr { border-bottom:1px solid #2e2e2e; transition:background 0.2s; }
        tbody tr:hover { background:#2c2c2c; }
        tbody td { padding:13px 18px; font-size:14px; color:#ccc; }
        .badge { padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold; }
        .badge-info { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; }
        .badge-mobilier { background:#1a2e1a; color:#58d68d; border:1px solid #27ae60; }
        .stock-bas { color:#e74c3c; font-weight:bold; }
        .stock-ok  { color:#58d68d; font-weight:bold; }
        .footer { text-align:center; padding:20px; color:#444; font-size:12px; margin-top:40px; border-top:1px solid #2e2e2e; }
        .footer span { color:#c9a84c; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏪 STOX — CATALOGUE</h1>
    <div style="display:flex;align-items:center;gap:15px;">
        <span class="badge-role">👤 CLIENT</span>
        <span style="color:#888;font-size:13px;">Bonjour, <b style="color:#c9a84c"><?= $_SESSION["nom_complet"] ?></b></span>
        <a href="logout.php">🔴 DÉCONNEXION</a>
    </div>
</div>

<div class="container">
    <div class="info-box">
        <b>👁️ Mode lecture seule</b> — Vous pouvez consulter le catalogue des produits disponibles.
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Produit</th><th>Catégorie</th><th>Prix (DH)</th><th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
            <tr>
                <td><?= $p["id"] ?></td>
                <td><?= htmlspecialchars($p["nom"]) ?></td>
                <td>
                    <span class="badge <?= $p['categorie'] == 'Informatique' ? 'badge-info' : 'badge-mobilier' ?>">
                        <?= $p["categorie"] ?>
                    </span>
                </td>
                <td><?= number_format($p["prix"], 0, ',', ' ') ?> DH</td>
                <td class="<?= $p['stock'] <= 5 ? 'stock-bas' : 'stock-ok' ?>">
                    <?= $p["stock"] ?> <?= $p['stock'] <= 5 ? '⚠️' : '✅' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="footer">© 2025 <span>STOX</span> — Tous droits réservés</div>
</body>
</html>
