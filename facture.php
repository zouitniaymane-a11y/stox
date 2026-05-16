<?php
session_start();
if (!isset($_SESSION["utilisateur"])) {
    header("Location: login.php");
}
require "db.php";

$id_facture = (int)$_GET["id"];

// Récupérer la facture
$f = $conn->prepare("SELECT * FROM factures WHERE id = :id");
$f->execute([":id" => $id_facture]);
$facture = $f->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    echo "Facture introuvable."; exit();
}

// Récupérer les lignes de la commande
$l = $conn->prepare("
    SELECT lc.*, p.nom, p.categorie
    FROM lignes_commande lc
    JOIN produits p ON p.id = lc.id_produit
    WHERE lc.id_commande = :id_commande
");
$l->execute([":id_commande" => $facture["id_commande"]]);
$lignes = $l->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?= $id_facture ?> - STOX</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; display:flex; justify-content:center; padding:40px 20px; }
        .facture { background:#242424; border:1px solid #333; border-radius:14px; padding:40px; width:100%; max-width:680px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; padding-bottom:20px; border-bottom:2px solid #c9a84c; }
        .header h1 { font-size:28px; color:#c9a84c; letter-spacing:3px; }
        .header p { color:#888; font-size:13px; margin-top:5px; }
        .facture-num { text-align:right; }
        .facture-num h2 { font-size:20px; color:#f0f0f0; }
        .facture-num p { color:#888; font-size:13px; margin-top:4px; }
        table { width:100%; border-collapse:collapse; margin:25px 0; }
        thead { background:#111; border-bottom:2px solid #c9a84c; }
        thead th { padding:12px 16px; text-align:left; font-size:11px; color:#c9a84c; letter-spacing:1.5px; text-transform:uppercase; }
        tbody tr { border-bottom:1px solid #2e2e2e; }
        tbody td { padding:12px 16px; font-size:14px; color:#ccc; }
        .totaux { margin-top:20px; border-top:1px solid #333; padding-top:20px; }
        .ligne-total { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; color:#aaa; }
        .ligne-total.ttc { font-size:18px; font-weight:bold; color:#c9a84c; border-top:1px solid #c9a84c; margin-top:8px; padding-top:12px; }
        .actions { display:flex; gap:12px; margin-top:30px; padding-top:20px; border-top:1px solid #2e2e2e; }
        .btn { padding:10px 22px; border-radius:8px; font-size:13px; font-weight:bold; cursor:pointer; text-decoration:none; text-align:center; border:none; letter-spacing:1px; }
        .btn-or { background:linear-gradient(135deg,#c9a84c,#a07830); color:#1a1a1a; }
        .btn-outline { background:transparent; border:1px solid #444; color:#aaa; }
        .btn-outline:hover { border-color:#c9a84c; color:#c9a84c; }
        .success-banner { background:#1a2e1a; border:1px solid #27ae60; color:#58d68d; padding:14px 20px; border-radius:8px; margin-bottom:25px; font-size:14px; text-align:center; }
    </style>
</head>
<body>
<div class="facture">
    <div class="success-banner">✅ Commande validée avec succès ! Merci pour votre achat.</div>

    <div class="header">
        <div>
            <h1>🏪 STOX</h1>
            <p>Gestion de Magasin</p>
        </div>
        <div class="facture-num">
            <h2>FACTURE #<?= $id_facture ?></h2>
            <p>Commande #<?= $facture["id_commande"] ?></p>
            <p><?= date("d/m/Y H:i") ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>Produit</th><th>Catégorie</th><th>Qté</th><th>Prix unit.</th><th>Sous-total</th></tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
            <tr>
                <td><?= htmlspecialchars($ligne["nom"]) ?></td>
                <td><?= $ligne["categorie"] ?></td>
                <td><?= $ligne["quantite"] ?></td>
                <td><?= number_format($ligne["prix_applique"], 0, ',', ' ') ?> DH</td>
                <td><?= number_format($ligne["prix_applique"] * $ligne["quantite"], 0, ',', ' ') ?> DH</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totaux">
        <div class="ligne-total"><span>Total HT</span><span><?= number_format($facture["total_ht"], 0, ',', ' ') ?> DH</span></div>
        <div class="ligne-total"><span>TVA (<?= $facture["tva"] ?>%)</span><span><?= number_format($facture["total_ht"] * $facture["tva"] / 100, 0, ',', ' ') ?> DH</span></div>
        <div class="ligne-total ttc"><span>TOTAL TTC</span><span><?= number_format($facture["total_ttc"], 0, ',', ' ') ?> DH</span></div>
    </div>

    <div class="actions">
        <button class="btn btn-or" onclick="window.print()">🖨️ IMPRIMER</button>
        <a href="dashboard_client.php" class="btn btn-outline">← RETOUR AU CATALOGUE</a>
        <a href="mes_factures.php" class="btn btn-outline">🧾 MES FACTURES</a>
    </div>
</div>
</body>
</html>