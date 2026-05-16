<?php
session_start();
if (!isset($_SESSION["utilisateur"]) || $_SESSION["role"] !== "client") {
    header("Location: login.php");
}
require "db.php";

$id_client = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT f.*, c.id as num_commande
    FROM factures f
    JOIN commandes c ON c.id = f.id_commande
    WHERE c.id_client = :id_client
    ORDER BY f.id DESC
");
$stmt->execute([":id_client" => $id_client]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Factures - STOX</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; }
        .navbar { background:#111; padding:16px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #c9a84c; }
        .navbar h1 { font-size:20px; color:#c9a84c; letter-spacing:2px; }
        .navbar a { color:#e74c3c; text-decoration:none; font-weight:bold; margin-left:20px; font-size:13px; }
        .navbar a.back { color:#c9a84c; }
        .container { padding:30px; }
        .section-title { color:#c9a84c; margin-bottom:15px; font-size:18px; letter-spacing:1px; }
        table { width:100%; border-collapse:collapse; background:#242424; border-radius:10px; overflow:hidden; border:1px solid #333; }
        thead { background:#111; border-bottom:2px solid #c9a84c; }
        thead th { padding:12px 18px; text-align:left; font-size:11px; color:#c9a84c; letter-spacing:1.5px; text-transform:uppercase; }
        tbody tr { border-bottom:1px solid #2e2e2e; }
        tbody tr:hover { background:#2c2c2c; }
        tbody td { padding:12px 18px; font-size:13px; color:#ccc; }
        .btn-voir { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; padding:5px 14px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:bold; }
        .empty { color:#555; font-style:italic; padding:20px; text-align:center; }
        .footer { text-align:center; padding:20px; color:#444; font-size:12px; margin-top:40px; border-top:1px solid #2e2e2e; }
        .footer span { color:#c9a84c; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🧾 STOX — MES FACTURES</h1>
    <div>
        <a href="dashboard_client.php" class="back">← CATALOGUE</a>
        <a href="logout.php">🔴 DÉCONNEXION</a>
    </div>
</div>
<div class="container">
    <h2 class="section-title">🧾 HISTORIQUE DES FACTURES</h2>
    <?php if (empty($factures)): ?>
        <p class="empty">Aucune facture pour l'instant.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Facture #</th><th>Commande #</th><th>Total HT</th><th>TVA</th><th>Total TTC</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $f): ?>
            <tr>
                <td>#<?= $f["id"] ?></td>
                <td>#<?= $f["num_commande"] ?></td>
                <td><?= number_format($f["total_ht"], 0, ',', ' ') ?> DH</td>
                <td><?= $f["tva"] ?>%</td>
                <td style="color:#c9a84c;font-weight:bold;"><?= number_format($f["total_ttc"], 0, ',', ' ') ?> DH</td>
                <td><a href="facture.php?id=<?= $f["id"] ?>" class="btn-voir">👁️ Voir</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<div class="footer">© 2025 <span>STOX</span> — Tous droits réservés</div>
</body>
</html>