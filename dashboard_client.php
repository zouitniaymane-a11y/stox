<?php
session_start();
if (!isset($_SESSION["utilisateur"]) || $_SESSION["role"] !== "client") {
    header("Location: login.php");
}
require "db.php";

$message = "";
$id_client = $_SESSION["user_id"];

// AJOUTER AU PANIER (session)
if (isset($_POST["ajouter_panier"])) {
    $id_produit = (int)$_POST["id_produit"];
    $qte = (int)$_POST["quantite"];
    if (!isset($_SESSION["panier"])) $_SESSION["panier"] = [];
    if (isset($_SESSION["panier"][$id_produit])) {
        $_SESSION["panier"][$id_produit] += $qte;
    } else {
        $_SESSION["panier"][$id_produit] = $qte;
    }
    $message = "✅ Produit ajouté au panier !";
}

// VIDER LE PANIER
if (isset($_GET["vider"])) {
    $_SESSION["panier"] = [];
    $message = "🗑️ Panier vidé !";
}

// SUPPRIMER UN ARTICLE DU PANIER
if (isset($_GET["retirer"])) {
    $id = (int)$_GET["retirer"];
    unset($_SESSION["panier"][$id]);
    $message = "❌ Produit retiré du panier !";
}

// VALIDER LA COMMANDE
if (isset($_POST["valider_commande"])) {
    if (!empty($_SESSION["panier"])) {
        try {
            // Créer la commande
            $stmt = $conn->prepare("INSERT INTO commandes (id_client, id_employe, statut) VALUES (:id_client, 0, 'VALIDEE')");
            $stmt->execute([":id_client" => $id_client]);
            $id_commande = $conn->lastInsertId();

            $total_ht = 0;

            foreach ($_SESSION["panier"] as $id_produit => $quantite) {
                $p = $conn->prepare("SELECT * FROM produits WHERE id = :id");
                $p->execute([":id" => $id_produit]);
                $produit = $p->fetch(PDO::FETCH_ASSOC);

                if ($produit && $produit["stock"] >= $quantite) {
                    // Insérer ligne commande
                    $l = $conn->prepare("INSERT INTO lignes_commande (id_commande, id_produit, quantite, prix_applique) VALUES (:id_commande, :id_produit, :quantite, :prix)");
                    $l->execute([
                        ":id_commande" => $id_commande,
                        ":id_produit"  => $id_produit,
                        ":quantite"    => $quantite,
                        ":prix"        => $produit["prix"]
                    ]);

                    // Diminuer le stock
                    $s = $conn->prepare("UPDATE produits SET stock = stock - :qte WHERE id = :id");
                    $s->execute([":qte" => $quantite, ":id" => $id_produit]);

                    $total_ht += $produit["prix"] * $quantite;
                }
            }

            // Créer la facture (TVA 20%)
            $tva = 20;
            $montant_tva = $total_ht * $tva / 100;
            $total_ttc = $total_ht + $montant_tva;

            $f = $conn->prepare("INSERT INTO factures (id_commande, tva, total_ht, total_ttc) VALUES (:id_commande, :tva, :total_ht, :total_ttc)");
            $f->execute([
                ":id_commande" => $id_commande,
                ":tva"         => $tva,
                ":total_ht"    => $total_ht,
                ":total_ttc"   => $total_ttc
            ]);
            $id_facture = $conn->lastInsertId();

            $_SESSION["panier"] = [];
            header("Location: facture.php?id=" . $id_facture);
            exit();

        } catch (Exception $e) {
            $message = "❌ Erreur lors de la commande.";
        }
    } else {
        $message = "⚠️ Votre panier est vide !";
    }
}

$produits = $conn->query("SELECT * FROM produits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Construire le panier avec détails
$panier_details = [];
$total_panier = 0;
if (!empty($_SESSION["panier"])) {
    foreach ($_SESSION["panier"] as $id_produit => $quantite) {
        $p = $conn->prepare("SELECT * FROM produits WHERE id = :id");
        $p->execute([":id" => $id_produit]);
        $produit = $p->fetch(PDO::FETCH_ASSOC);
        if ($produit) {
            $sous_total = $produit["prix"] * $quantite;
            $total_panier += $sous_total;
            $panier_details[] = [
                "produit"    => $produit,
                "quantite"   => $quantite,
                "sous_total" => $sous_total
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue - STOX</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; }
        .navbar { background:#111; padding:16px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #c9a84c; }
        .navbar h1 { font-size:20px; color:#c9a84c; letter-spacing:2px; }
        .badge-role { background:#1a3a1a; color:#58d68d; border:1px solid #27ae60; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; }
        .navbar a { color:#e74c3c; text-decoration:none; font-weight:bold; margin-left:20px; font-size:13px; }
        .container { padding:30px; display:grid; grid-template-columns:1fr 380px; gap:30px; }
        .message { background:#1a2e1a; border:1px solid #27ae60; color:#58d68d; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:14px; grid-column:1/-1; }
        .message.err { background:#2e1a1a; border-color:#c0392b; color:#e74c3c; }

        /* Catalogue */
        table { width:100%; border-collapse:collapse; background:#242424; border-radius:10px; overflow:hidden; border:1px solid #333; }
        thead { background:#111; border-bottom:2px solid #c9a84c; }
        thead th { padding:12px 16px; text-align:left; font-size:11px; color:#c9a84c; letter-spacing:1.5px; text-transform:uppercase; }
        tbody tr { border-bottom:1px solid #2e2e2e; }
        tbody tr:hover { background:#2c2c2c; }
        tbody td { padding:11px 16px; font-size:13px; color:#ccc; }
        .badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
        .badge-info { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; }
        .badge-mobilier { background:#1a2e1a; color:#58d68d; border:1px solid #27ae60; }
        .stock-bas { color:#e74c3c; font-weight:bold; }
        .stock-ok  { color:#58d68d; font-weight:bold; }
        .qte-input { width:55px; padding:4px 8px; background:#1a1a1a; border:1px solid #444; border-radius:6px; color:#f0f0f0; font-size:13px; }
        .btn-add { background:#c9a84c; color:#1a1a1a; border:none; border-radius:6px; padding:5px 12px; font-size:12px; font-weight:bold; cursor:pointer; }
        .btn-add:hover { opacity:0.85; }

        /* Panier */
        .panier-box { background:#242424; border:1px solid #333; border-radius:10px; padding:20px; height:fit-content; position:sticky; top:20px; }
        .panier-box h2 { color:#c9a84c; font-size:16px; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #2e2e2e; }
        .panier-item { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #2a2a2a; font-size:13px; }
        .panier-item span { color:#aaa; }
        .panier-item .prix { color:#c9a84c; font-weight:bold; }
        .btn-retirer { background:#3a1a1a; color:#e74c3c; border:1px solid #c0392b; padding:3px 8px; border-radius:5px; font-size:11px; text-decoration:none; }
        .panier-total { margin-top:15px; padding-top:12px; border-top:2px solid #c9a84c; }
        .panier-total .ligne { display:flex; justify-content:space-between; font-size:13px; color:#aaa; margin-bottom:6px; }
        .panier-total .ttc { font-size:16px; font-weight:bold; color:#c9a84c; }
        .btn-valider { width:100%; padding:12px; background:linear-gradient(135deg,#c9a84c,#a07830); color:#1a1a1a; border:none; border-radius:8px; font-size:14px; font-weight:bold; cursor:pointer; margin-top:12px; letter-spacing:1px; }
        .btn-valider:hover { opacity:0.85; }
        .btn-vider { display:block; text-align:center; color:#888; font-size:12px; margin-top:10px; text-decoration:none; }
        .btn-vider:hover { color:#e74c3c; }
        .panier-vide { color:#555; font-style:italic; text-align:center; padding:20px 0; font-size:13px; }
        .section-title { color:#c9a84c; margin-bottom:15px; font-size:18px; letter-spacing:1px; }
        .footer { text-align:center; padding:20px; color:#444; font-size:12px; margin-top:40px; border-top:1px solid #2e2e2e; grid-column:1/-1; }
        .footer span { color:#c9a84c; }
        .mes-factures-btn { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; padding:6px 14px; border-radius:8px; font-size:12px; text-decoration:none; font-weight:bold; margin-left:10px; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏪 STOX — CATALOGUE</h1>
    <div style="display:flex;align-items:center;gap:15px;">
        <span class="badge-role">👤 CLIENT</span>
        <span style="color:#888;font-size:13px;">Bonjour, <b style="color:#c9a84c"><?= $_SESSION["nom_complet"] ?></b></span>
        <a href="mes_factures.php" class="mes-factures-btn">🧾 Mes Factures</a>
        <a href="logout.php">🔴 DÉCONNEXION</a>
    </div>
</div>

<div class="container">
    <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <!-- CATALOGUE -->
    <div>
        <h2 class="section-title">📦 PRODUITS DISPONIBLES</h2>
        <table>
            <thead>
                <tr><th>#</th><th>Produit</th><th>Catégorie</th><th>Prix (DH)</th><th>Stock</th><th>Quantité</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $p): ?>
                <tr>
                    <td><?= $p["id"] ?></td>
                    <td><?= htmlspecialchars($p["nom"]) ?></td>
                    <td><span class="badge <?= $p['categorie']=='Informatique' ? 'badge-info' : 'badge-mobilier' ?>"><?= $p["categorie"] ?></span></td>
                    <td><?= number_format($p["prix"], 0, ',', ' ') ?> DH</td>
                    <td class="<?= $p['stock'] <= 5 ? 'stock-bas' : 'stock-ok' ?>"><?= $p["stock"] ?> <?= $p['stock'] <= 5 ? '⚠️' : '✅' ?></td>
                    <td>
                        <?php if ($p["stock"] > 0): ?>
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="id_produit" value="<?= $p["id"] ?>">
                            <input type="number" name="quantite" value="1" min="1" max="<?= $p['stock'] ?>" class="qte-input">
                            <button type="submit" name="ajouter_panier" class="btn-add">🛒 Ajouter</button>
                        </form>
                        <?php else: ?>
                        <span style="color:#e74c3c;font-size:12px;">Rupture</span>
                        <?php endif; ?>
                    </td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- PANIER -->
    <div class="panier-box">
        <h2>🛒 MON PANIER</h2>
        <?php if (empty($panier_details)): ?>
            <p class="panier-vide">Votre panier est vide</p>
        <?php else: ?>
            <?php foreach ($panier_details as $item): ?>
            <div class="panier-item">
                <div>
                    <div style="color:#f0f0f0;font-weight:bold;"><?= htmlspecialchars($item["produit"]["nom"]) ?></div>
                    <span>x<?= $item["quantite"] ?> × <?= number_format($item["produit"]["prix"], 0, ',', ' ') ?> DH</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="prix"><?= number_format($item["sous_total"], 0, ',', ' ') ?> DH</span>
                    <a href="?retirer=<?= $item["produit"]["id"] ?>" class="btn-retirer">✕</a>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="panier-total">
                <?php
                $tva = 20;
                $montant_tva = $total_panier * $tva / 100;
                $total_ttc = $total_panier + $montant_tva;
                ?>
                <div class="ligne"><span>Total HT</span><span><?= number_format($total_panier, 0, ',', ' ') ?> DH</span></div>
                <div class="ligne"><span>TVA (20%)</span><span><?= number_format($montant_tva, 0, ',', ' ') ?> DH</span></div>
                <div class="ligne ttc"><span>Total TTC</span><span><?= number_format($total_ttc, 0, ',', ' ') ?> DH</span></div>
            </div>

            <form method="POST">
                <button type="submit" name="valider_commande" class="btn-valider">✅ VALIDER LA COMMANDE</button>
            </form>
            <a href="?vider=1" class="btn-vider" onclick="return confirm('Vider le panier ?')">🗑️ Vider le panier</a>
        <?php endif; ?>
    </div>
</div>

<div class="footer">© 2025 <span>STOX</span> — Tous droits réservés</div>
</body>
</html>