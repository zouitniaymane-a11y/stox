<?php
session_start();
if (!isset($_SESSION["utilisateur"]) || !in_array($_SESSION["role"], ["employe","admin"])) {//verfication que c'est pas un client
    header("Location: login.php"); 
}
require "db.php";

$message = "";

// AJOUTER
if (isset($_POST["ajouter"])) {
    $nom      = $_POST["nom"];
    $categorie= $_POST["categorie"];
    $prix     = (float)$_POST["prix"];
    $stock    = (int)$_POST["stock"];

    $stmt = $conn->prepare("INSERT INTO produits (nom, categorie, prix, stock) VALUES (:nom,:categorie,:prix,:stock)");
    $stmt->execute([":nom"=>$nom, ":categorie"=>$categorie, ":prix"=>$prix, ":stock"=>$stock]);
    $message = "✅ Produit ajouté avec succès !";
}

// SUPPRIMER 
if (isset($_GET["supprimer"])) {
    $id = (int)$_GET["supprimer"];
    $conn->prepare("DELETE FROM produits WHERE id = :id")->execute([":id"=>$id]);
    $message = "🗑️ Produit supprimé !";
}

// MODIFIER (chargement) 
$edit = null;
if (isset($_GET["modifier"])) {
    $id = (int)$_GET["modifier"];
    $stmt = $conn->prepare("SELECT * FROM produits WHERE id = :id");
    $stmt->execute([":id"=>$id]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

//  MODIFIER (sauvegarde) 
if (isset($_POST["sauvegarder"])) {
    $id       = (int)$_POST["id"];
    $nom      = $_POST["nom"];
    $categorie= $_POST["categorie"];
    $prix     = (float)$_POST["prix"];
    $stock    = (int)$_POST["stock"];

    $stmt = $conn->prepare("UPDATE produits SET nom=:nom, categorie=:categorie, prix=:prix, stock=:stock WHERE id=:id");
    $stmt->execute([":nom"=>$nom,":categorie"=>$categorie,":prix"=>$prix,":stock"=>$stock,":id"=>$id]);
    $message = "✏️ Produit modifié avec succès !";
}

$produits = $conn->query("SELECT * FROM produits ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Employé - STOX</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#1a1a1a; color:#f0f0f0; }
        .navbar { background:#111; padding:16px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #c9a84c; }
        .navbar h1 { font-size:20px; color:#c9a84c; letter-spacing:2px; }
        .badge-role { background:#1a2a3a; color:#5dade2; border:1px solid #2e86c1; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:bold; }
        .navbar a { color:#e74c3c; text-decoration:none; font-weight:bold; margin-left:20px; font-size:13px; }
        .container { padding:30px; }
        .message { background:#1a2e1a; border:1px solid #27ae60; color:#58d68d; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:14px; }

        / Formulaire +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++/
        .form-box { background:#242424; border:1px solid #333; border-radius:10px; padding:25px; margin-bottom:30px; }
        .form-box h3 { color:#c9a84c; margin-bottom:18px; font-size:16px; letter-spacing:1px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        label { color:#aaa; font-size:12px; font-weight:600; letter-spacing:0.5px; display:block; margin-bottom:5px; }
        input[type="text"], input[type="number"], select {
            width:100%; padding:10px 14px; background:#1a1a1a; border:1px solid #444;
            border-radius:8px; font-size:14px; color:#f0f0f0; transition:border 0.3s;
        }
        input:focus, select:focus { border-color:#c9a84c; outline:none; }
        .btn { padding:10px 22px; border:none; border-radius:8px; font-size:13px; font-weight:bold; cursor:pointer; letter-spacing:1px; transition:opacity 0.3s; }
        .btn-or  { background:linear-gradient(135deg,#c9a84c,#a07830); color:#1a1a1a; margin-top:15px; }
        .btn-or:hover { opacity:0.85; }

        /* Tableau */
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
        .btn-mod { background:#1a3a4a; color:#5dade2; border:1px solid #2e86c1; padding:5px 14px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:bold; }
        .btn-sup { background:#3a1a1a; color:#e74c3c; border:1px solid #c0392b; padding:5px 14px; border-radius:6px; font-size:12px; text-decoration:none; font-weight:bold; }
        .btn-mod:hover { background:#2e86c1; color:#fff; }
        .btn-sup:hover { background:#e74c3c; color:#fff; }
        .footer { text-align:center; padding:20px; color:#444; font-size:12px; margin-top:40px; border-top:1px solid #2e2e2e; }
        .footer span { color:#c9a84c; }
        .section-title { color:#c9a84c; margin-bottom:15px; font-size:18px; letter-spacing:1px; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏪 STOX — GESTION PRODUITS</h1>
    <div style="display:flex;align-items:center;gap:15px;">
        <span class="badge-role">🛠️ <?= strtoupper($_SESSION["role"]) ?></span>
        <span style="color:#888;font-size:13px;">Bonjour, <b style="color:#c9a84c"><?= $_SESSION["nom_complet"] ?></b></span>
        <?php if ($_SESSION["role"] === "admin"): ?>
            <a href="dashboard_admin.php" style="color:#c9a84c;">⚙️ ADMIN</a>
        <?php endif; ?>
        <a href="logout.php">🔴 DÉCONNEXION</a>
    </div>
</div>

<div class="container">
    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

    <!-- FORMULAIRE AJOUT / MODIFICATION -->
    <div class="form-box">
        <h3><?= $edit ? "✏️ MODIFIER LE PRODUIT" : "➕ AJOUTER UN PRODUIT" ?></h3>
        <form method="POST">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div>
                    <label>NOM DU PRODUIT</label>
                    <input type="text" name="nom" value="<?= $edit ? htmlspecialchars($edit['nom']) : '' ?>" placeholder="Ex : Ordinateur portable" required>
                </div>
                <div>
                    <label>CATÉGORIE</label>
                    <select name="categorie">
                        <option value="Informatique" <?= ($edit && $edit['categorie']=='Informatique') ? 'selected' : '' ?>>💻 Informatique</option>
                        <option value="Mobilier"     <?= ($edit && $edit['categorie']=='Mobilier')     ? 'selected' : '' ?>>🪑 Mobilier</option>
                        <option value="Autre"        <?= ($edit && $edit['categorie']=='Autre')        ? 'selected' : '' ?>>📦 Autre</option>
                    </select>
                </div>
                <div>
                    <label>PRIX (DH)</label>
                    <input type="number" name="prix" value="<?= $edit ? $edit['prix'] : '' ?>" placeholder="0" min="0" step="0.01" required>
                </div>
                <div>
                    <label>STOCK</label>
                    <input type="number" name="stock" value="<?= $edit ? $edit['stock'] : '' ?>" placeholder="0" min="0" required>
                </div>
            </div>
            <?php if ($edit): ?>
                <button class="btn btn-or" type="submit" name="sauvegarder">💾 SAUVEGARDER</button>
                <a href="dashboard_employe.php" class="btn" style="background:#333;color:#aaa;margin-left:10px;">ANNULER</a>
            <?php else: ?>
                <button class="btn btn-or" type="submit" name="ajouter">➕ AJOUTER</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABLEAU PRODUITS -->
    <h2 class="section-title">📦 LISTE DES PRODUITS</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Produit</th><th>Catégorie</th><th>Prix (DH)</th><th>Stock</th><th>Actions</th></tr>
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
                    <a href="?modifier=<?= $p['id'] ?>" class="btn-mod">✏️ Modifier</a>
                    <a href="?supprimer=<?= $p['id'] ?>" class="btn-sup"
                       onclick="return confirm('Supprimer ce produit ?')">🗑️ Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="footer">© 2025 <span>STOX</span> — Tous droits réservés</div>
</body>
</html>
